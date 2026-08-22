<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Miner;
use App\Models\Isp;
use App\Models\IspMetric;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis; // <--- Tambahkan fasad Redis di atas
use App\Models\Incident; // <--- Tambahkan ini
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;

class RunNetworkMonitor extends Command
{
    protected $signature = 'monitor:network';
    protected $description = 'Mengecek status 4 ISP, Mikrotik Gateway, dan 300 Mesin Miner';

    public function handle()
    {
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
        // 2. CEK MIKROTIK GATEWAY UTAMA (Fitur Baru)
        // ---------------------------------------------------------
        $mikrotikIp = '192.168.56.2'; // IP Gateway Mikrotik Anda sesuai peta jaringan
        $this->info("Mengecek status Mikrotik Gateway ({$mikrotikIp})...");
        
        $mikrotikPing = $this->pingSingleIp($mikrotikIp);
        $isMikrotikOnline = $mikrotikPing !== null;

        // Simpan status ke Redis agar dibaca instan oleh Incident Widget di Filament
        Redis::set('system:mikrotik:status', $isMikrotikOnline ? 'online' : 'offline');

        if (!$isMikrotikOnline) {
            $this->error("🚨 ALERT CRITICAL: MIKROTIK GATEWAY DOWN!");
            Log::emergency("NOC SYSTEM: Mikrotik Router pada IP {$mikrotikIp} tidak merespons!");
            
            // OPTIONAL: Jika Mikrotik mati, kita paksa semua status miner saat ini di database 
            // menjadi offline tanpa harus mem-ping satu-satu (menghemat waktu RTO fping)
            Miner::whereNotNull('ip_address')->update(['is_online' => false]);
            
            $this->warn("Pengecekan miner dilewati karena jalur utama ke switch terputus.");
            $this->info("Pengecekan jaringan selesai!");
            return; 
        } else {
            $this->info(" -> Mikrotik Gateway status: ONLINE 🟢");
        }

        // ---------------------------------------------------------
        // 3. CEK MESIN PENAMBANG (Hanya berjalan jika Mikrotik Online)
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
                
                $bulanIni = date('Y-m'); // Hasilnya: "2026-08"

                if ($isAlive) {
                    Redis::incr("uptime:{$bulanIni}:online");
                } else {
                    Redis::incr("uptime:{$bulanIni}:offline");
                }

                if ($isAlive) {
                    $this->info(" -> Mesin {$miner->name} ONLINE");
                } else {
                    $this->error(" -> Mesin {$miner->name} OFFLINE");
                    // MASUKKAN KE KERANJANG TIKET!
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
                    // Opsional: Kalau mau log juga saat mesin hidup kembali
                     $waktu = now()->format('H:i:s');
                     \Illuminate\Support\Facades\Redis::lpush('noc:live_logs', "<span class='text-green-500'>[{$waktu}] 🟢 Mesin {$miner->name} ONLINE</span>");
                }
            }

            if ($sleepDuration > 0 && $index < count($chunks) - 1) {
                $this->info("Menunggu {$sleepDuration} detik sebelum rombongan berikutnya...");
                sleep($sleepDuration); 
            }
            if ($sleepDuration > 0 && $index < count($chunks) - 1) {
                $this->info("Menunggu {$sleepDuration} detik sebelum rombongan berikutnya...");
                sleep($sleepDuration); 
            }
        } // <-- Penutup foreach chunks
        
        \Illuminate\Support\Facades\Redis::ltrim('noc:live_logs', 0, 19);
        // ---------------------------------------------------------
        // LOGIKA PEMBUATAN TIKET INSIDEN (PAGERDUTY STYLE)
        // ---------------------------------------------------------
        $totalOffline = count($offlineMiners);

        if ($totalOffline > 0) {
            // Cek apakah sudah ada tiket yang sedang 'open'
            $activeIncident = Incident::where('status', 'open')->first();

            if (!$activeIncident) {
                // JIKA BELUM ADA, BUAT TIKET BARU!
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

                    // 1. Ambil admin utama (Asumsi ID 1 adalah akun Anda di NOC)
                    $admin = User::find(1);

                    // 2. Pastikan admin ada dan tokennya tidak kosong
                    // Pastikan admin ada dan tokennya tidak kosong
                if ($admin && $admin->fcm_token) {
                    // Gunakan fromArray agar kompatibel dengan Firebase SDK terbaru dan bebas error IDE
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        // BLOK NOTIFICATION DIHAPUS TOTAL DI SINI
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
                
                // TODO (Tahap 3): Di sinilah kita akan menembak API FCM ke HP Anda
                // agar layar HP menyala dan berbunyi alarm keras!
                
            } else {
                // Jika sudah ada tiket, cukup perbarui datanya (takutnya mesin mati bertambah/berkurang)
                $activeIncident->update([
                    'title' => "CRITICAL: {$totalOffline} Mesin Offline",
                    'affected_miners' => $offlineMiners,
                ]);
            }
        } else {
            // Jika semua mesin HIDUP, maka tutup semua tiket yang masih open/acknowledged
            Incident::whereIn('status', ['open', 'acknowledged'])
                    ->update(['status' => 'resolved']);
        }
        $this->info("Pengecekan jaringan selesai!");

    }

    private function pingSingleIp($ip)
    {
        // === SAKELAR MODE SIMULASI ===
        if (env('PING_MODE') === 'dummy') return rand(1, 15);
        // === SAKELAR MODE SIMULASI ===
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
            $ipString = implode('escapeshellarg_array', ($ips));
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
              