<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Miner;
use App\Models\Isp;
use App\Models\IspMetric;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\Incident;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;

class RunNetworkMonitor extends Command
{
    protected $signature = 'monitor:network';
    protected $description = 'Mengecek status 4 ISP, Mikrotik Gateway, dan 300 Mesin Miner (Daemon Mode)';

    public function handle()
    {
        // Infinite loop agar command tidak pernah mati (Daemon)
        while (true) {
            $this->info("Memulai pengecekan jaringan...");

            // ---------------------------------------------------------
            // 1. CEK 4 JALUR ISP DULU
            // ---------------------------------------------------------
            $isps = Isp::all();
            foreach ($isps as $isp) {
                $pingTime = $this->pingSingleIp($isp->gateway_ip);
                $isOnline = $pingTime !== null;

                $isp->update([
                    'is_online' => $isOnline,
                    'last_checked_at' => now(),
                ]);

                $interfaceName = $isp->name; 
                $trafficData = Redis::hget('isp:traffic', $interfaceName);
                $downloadMbps = null;
                $uploadMbps = null;
                if ($trafficData) {
                    $traffic = json_decode($trafficData, true);
                    $downloadMbps = $traffic['download_mbps'];
                    $uploadMbps = $traffic['upload_mbps'];
                }
                
                // SATUKAN PENYIMPANANNYA DI SINI:
                IspMetric::create([
                    'isp_id' => $isp->id,
                    'ping_ms' => $pingTime,
                    'is_online' => $isOnline,
                    'download_mbps' => $downloadMbps,
                    'upload_mbps' => $uploadMbps,
                ]);

                if (!$isOnline) {
                    Log::warning("ISP ALERT: {$isp->name} DOWN!");
                    $waktu = now()->format('H:i:s');
                    \Illuminate\Support\Facades\Redis::lpush('noc:live_logs', "<span class='text-red-500'>[{$waktu}] 🔴 ISP {$isp->name} TERPUTUS!</span>");
                }
            }

            // ---------------------------------------------------------
            // 2. CEK MIKROTIK GATEWAY UTAMA
            // ---------------------------------------------------------
            $mikrotikIp = '192.168.88.254'; 
            $this->info("Mengecek status Mikrotik Gateway ({$mikrotikIp})...");
            
            $mikrotikPing = $this->pingSingleIp($mikrotikIp);
            $isMikrotikOnline = $mikrotikPing !== null;

            Redis::set('system:mikrotik:status', $isMikrotikOnline ? 'online' : 'offline');

            if (!$isMikrotikOnline) {
                $this->error("🚨 ALERT CRITICAL: MIKROTIK GATEWAY DOWN!");
                Log::emergency("NOC SYSTEM: Mikrotik Router pada IP {$mikrotikIp} tidak merespons!");
                
                Miner::whereNotNull('ip_address')->update(['is_online' => false]);
                
                $this->warn("Pengecekan miner dilewati karena jalur utama ke switch terputus.");
                $this->info("Pengecekan jaringan selesai!");
                
                // Lanjut ke iterasi while berikutnya setelah jeda, bukan return (yang akan mematikan proses)
                $this->info("Menunggu 10 detik sebelum mengulang...");
                sleep(10);
                continue; 
            } else {
                $this->info(" -> Mikrotik Gateway status: ONLINE 🟢");
            }

            // ---------------------------------------------------------
            // 3. CEK MESIN PENAMBANG
            // ---------------------------------------------------------
            $setting = AppSetting::firstOrCreate(['id' => 1], [
                'use_chunking' => true,
                'chunk_size' => 50,
                'chunk_sleep' => 5,
            ]);

            $miners = Miner::whereNotNull('ip_address')->get();
            $isLocal = app()->environment('local') || PHP_OS_FAMILY === 'Windows';

            if ($isLocal || !$setting->use_chunking) {
                $this->info("Mode Aktif: SINGLE JOB (Mengecek semua mesin sekaligus)");
                $chunks = [$miners]; 
                $sleepDuration = 0;
            } else {
                $this->info("Mode Aktif: PARTIAL PING ({$setting->chunk_size} mesin/kelompok)");
                $chunks = $miners->chunk($setting->chunk_size); 
                $sleepDuration = $setting->chunk_sleep;
            }

            $offlineMiners = [];

            foreach ($chunks as $index => $chunk) {
                $ipsToPing = $chunk->pluck('ip_address')->toArray();
                
                $this->info("Mengecek rombongan " . ($index + 1) . " (" . count($ipsToPing) . " mesin)...");

                $results = $this->fpingBatch($ipsToPing);

                foreach ($chunk as $miner) {
                    $isAlive = in_array($miner->ip_address, $results['alive']);
                    $bulanIni = date('Y-m'); 

                    if ($isAlive) {
                        Redis::incr("uptime:{$bulanIni}:online");
                        $this->info(" -> Mesin {$miner->name} ONLINE");
                    } else {
                        Redis::incr("uptime:{$bulanIni}:offline");
                        $this->error(" -> Mesin {$miner->name} OFFLINE");
                        $offlineMiners[] = [
                            'id' => $miner->id,
                            'name' => $miner->name,
                            'ip' => $miner->ip_address,
                            'slot' => $miner->slot_number
                        ];
                    }

                    $miner->update([
                        'is_online' => $isAlive,
                        'last_seen_at' => $isAlive ? now() : $miner->last_seen_at,
                    ]);

                    if (!$isAlive) {
                        Log::error("MINER ALERT: Mesin {$miner->name} (Rak {$miner->slot_number}) TERPUTUS!");
                        $waktu = now()->format('H:i:s');
                        \Illuminate\Support\Facades\Redis::lpush('noc:live_logs', "<span class='text-red-500'>[{$waktu}] 🔴 Mesin {$miner->name} (IP: {$miner->ip_address}) OFFLINE</span>");
                    } else {
                        $waktu = now()->format('H:i:s');
                        \Illuminate\Support\Facades\Redis::lpush('noc:live_logs', "<span class='text-green-500'>[{$waktu}] 🟢 Mesin {$miner->name} ONLINE</span>");
                    }
                }

                if ($sleepDuration > 0 && $index < count($chunks) - 1) {
                    $this->info("Menunggu {$sleepDuration} detik sebelum rombongan berikutnya...");
                    sleep($sleepDuration); 
                }
            } 
            
            \Illuminate\Support\Facades\Redis::ltrim('noc:live_logs', 0, 19);

            // ---------------------------------------------------------
            // 4. LOGIKA PEMBUATAN TIKET INSIDEN (PAGERDUTY STYLE)
            // ---------------------------------------------------------
            $totalOffline = count($offlineMiners);

            if ($totalOffline > 0) {
                $activeIncident = Incident::where('status', 'open')->first();

                if (!$activeIncident) {
                    $newIncident = Incident::create([
                        'ticket_number' => 'NOC-' . time(),
                        'title' => "CRITICAL: {$totalOffline} Mesin Offline",
                        'status' => 'open',
                        'affected_miners' => $offlineMiners,
                    ]);
                    
                    $this->error("🚨 TIKET BARU DIBUAT: {$newIncident->ticket_number}");
                    
                    try {
                        $firebase = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
                        $messaging = $firebase->createMessaging();
                        $admin = User::find(1);

                        if ($admin && $admin->fcm_token) {
                            $message = CloudMessage::fromArray([
                                'token' => $admin->fcm_token,
                                'data' => [
                                    'trigger'   => 'full_screen_intent', 
                                    'ticket_id' => $newIncident->ticket_number
                                ]
                            ]);

                            $messaging->send($message);
                            $this->info("⚡ Sinyal darurat berhasil ditembakkan ke HP Admin!");
                        } else {
                            $this->warn("⚠️ FCM Batal dikirim: fcm_token milik admin masih kosong.");
                        }

                    } catch (\Exception $e) {
                        $this->error("Gagal menembak alarm FCM: " . $e->getMessage());
                    }
                } else {
                    $activeIncident->update([
                        'title' => "CRITICAL: {$totalOffline} Mesin Offline",
                        'affected_miners' => $offlineMiners,
                    ]);
                }
            } else {
                Incident::whereIn('status', ['open', 'acknowledged'])
                        ->update(['status' => 'resolved']);
            }
            
            $this->info("Pengecekan jaringan selesai!");
            
            // Jeda 10 detik sebelum mengulang ke atas
            $this->info("Menunggu 10 detik sebelum siklus berikutnya...");
            sleep(10);
        } // Penutup while(true)
    }

    private function pingSingleIp($ip)
    {
        if (env('PING_MODE') === 'dummy') return rand(1, 15);
        
        if (PHP_OS_FAMILY === 'Windows') {
            exec("ping -n 1 -w 1000 " . ($ip), $output, $status);
            if ($status === 0) {
                return 1; 
            }
        } else {
            exec("ping -c 1 -W 1 " . escapeshellarg($ip), $output, $status);
            if ($status === 0) {
                preg_match('/time=([0-9\.]+) ms/', implode(" ", $output), $matches);
                return isset($matches[1]) ? (int) $matches[1] : 1; 
            }
        }
        
        return null;
    }

    private function fpingBatch(array $ips)
    {
        $alive = [];

        if (env('PING_MODE') === 'dummy') return ['alive' => $ips];
        
        if (PHP_OS_FAMILY === 'Windows') {
            foreach ($ips as $ip) {
                exec("ping -n 1 -w 500 " . ($ip), $output, $status);
                if ($status === 0) {
                    $alive[] = $ip;
                }
            }
        } else {
            $escapedIps = array_map('escapeshellarg', $ips);
            $ipString = implode(' ', $escapedIps);
            
            exec("fping -a -t 500 $ipString 2>/dev/null", $aliveOutputs, $status);
            $alive = $aliveOutputs;
        }

        return [
            'alive' => $alive, 
        ];
    }
}