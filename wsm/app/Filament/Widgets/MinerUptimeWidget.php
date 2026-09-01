<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Redis;
use App\Models\Miner;
use App\Models\Isp;
use App\Models\Owner;
use App\Models\IspMetric;
use Illuminate\Support\HtmlString;
// Tambahkan model Alarm/Incident jika ada, contoh: use App\Models\Alarm;

class MinerUptimeWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full'; // Membentang 12 kolom, isi dalamnya sudah kita set 4 kotak
    protected function getColumns(): int
    {
        return 4; 
    }

    private function calculateTrend($modelClass): float
    {
        $currentMonth = $modelClass::where('created_at', '>=', now()->startOfMonth())->count();
        
        $lastMonth = $modelClass::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();

        if ($lastMonth === 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return (($currentMonth - $lastMonth) / $lastMonth) * 100;
    }

    protected function getStats(): array
    {


        $totalMesin = Miner::count();
        $totalOwner = Owner::count();

        $persenMesin = round($this->calculateTrend(Miner::class), 1);
        $statusMesin = $persenMesin >= 0 ? '' : '';
        $iconMesin   = $persenMesin >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $warnaMesin  = $persenMesin >= 0 ? 'success' : 'danger';

        $persenOwner = round($this->calculateTrend(Owner::class), 1);
        $statusOwner = $persenOwner >= 0 ? '' : '';
        $iconOwner   = $persenOwner >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $warnaOwner  = $persenOwner >= 0 ? 'success' : 'danger';
       
        /*
        $bulanIni = date('Y-m');
        $totalOwner = Owner::count();
        // 1. Data Uptime Mesin (Dari Redis)
        $online = (int) Redis::get("uptime:{$bulanIni}:online") ?: 0;
        $offline = (int) Redis::get("uptime:{$bulanIni}:offline") ?: 0;
        $totalPengecekan = $online + $offline;
        $persentaseUptime = $totalPengecekan > 0 ? round(($online / $totalPengecekan) * 100, 2) : 0;

        // 2. Data Total Mesin (Dari Database)
        $totalMesin = Miner::count();
        $mesinOnline = Miner::where('is_online', true)->count(); // Asumsi ada field is_online
        $mesinDown = $totalMesin - $mesinOnline;

       // Kita batasi hitungan hanya dari data 24 jam terakhir agar akurat
        $waktuMulai = now()->subHours(24);
        
        $totalIspChecks = IspMetric::where('created_at', '>=', $waktuMulai)->count();
        $onlineIspChecks = IspMetric::where('created_at', '>=', $waktuMulai)
                                    ->where('is_online', true)->count();
        
        $availability = $totalIspChecks > 0 
            ? round(($onlineIspChecks / $totalIspChecks) * 100, 2) 
            : 100;

        // --- B. KALKULASI ACTIVE ALARMS ---
        // Critical: Jumlah mesin mati + ISP mati saat ini
        $mesinDown = Miner::where('is_online', false)->count();
        $ispDown = Isp::where('is_online', false)->count();
        $criticalAlarms = $mesinDown + $ispDown;

        // Warning: ISP yang nyala tapi ping-nya di atas 100ms dalam 5 menit terakhir
        $warningAlarms = IspMetric::where('created_at', '>=', now()->subMinutes(5))
                            ->where('is_online', true)
                            ->where('ping_ms', '>', 100)
                            ->count();
                            
        $totalAlarms = $criticalAlarms + $warningAlarms;
        */
        // WIDGET AWAL //
        return [
            // Kotak 1: Uptime Jaringan
          /* Stat::make('NETWORK AVAILABILITY', "{$availability}%")
                ->description('Status: ONLINE')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->extraAttributes([
                    'class' => 'neon-box-green', // Class custom untuk CSS nanti
                ]),
                */

            // Kotak 2: Total Mesin & Rincian
         Stat::make(
            new HtmlString('
                <div style="display: flex; align-items: center; gap: 20px; padding: 10px 0;">
                    
                    <!-- KOTAK ICON (SEBELAH KIRI - UKURAN LEBIH BESAR) -->
                    <div style="display: flex; flex-shrink: 0; align-items: center; justify-content: center; width: 75px; height: 75px; background-color: #1e293b; border-radius: 16px; border: 1px solid #334155;">
                        <!-- SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 38px; height: 38px; color: #3b82f6;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z" />
                        </svg>
                    </div>
                    
                    <!-- KELOMPOK TEKS (SEBELAH KANAN) -->
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <span style="font-size: 14px; font-weight: 500; color: #dadada; margin-bottom: 4px;">TOTAL MESIN</span>
                        <span style="font-size: 32px; font-weight: bold; color: white; line-height: 1;">' . number_format($totalMesin) . '</span>
                    </div>

                </div>
                '),
                '' // Parameter kedua dikosongkan ('') karena angkanya sudah masuk di dalam HtmlString di atas
                )
                
                ->description(abs($persenMesin) . '% ' . $statusMesin)
                ->descriptionIcon($iconMesin)
                ->color($warnaMesin)
                ->extraAttributes([
                    'class' => 'neon-box-blue',
                ]),


            
            Stat::make(
            new HtmlString('
               <div style="display: flex; align-items: center; gap: 20px; padding: 10px 0;">
                <div style="display: flex; flex-shrink: 0; align-items: center; justify-content: center; width: 75px; height: 75px; background-color: #1e293b; border-radius: 16px; border: 1px solid #334155;">
                    <!-- Icon Users/Group (Hijau) -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 38px; height: 38px; color: #10b981;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div style="display: flex; flex-direction: column; justify-content: center;">
                    <span style="font-size: 14px; font-weight: 600; color: #9ca3af; margin-bottom: 6px; letter-spacing: 0.5px;">TOTAL OWNER</span>
                    <span style="font-size: 36px; font-weight: bold; color: white; line-height: 1;">' . number_format($totalOwner) . '</span>
                </div>
            </div>
                '),
                '' // Parameter kedua dikosongkan ('') karena angkanya sudah masuk di dalam HtmlString di atas
                )
                ->description(abs($persenOwner) . '% ' . $statusOwner)
                ->descriptionIcon($iconOwner)
                ->color($warnaOwner)
                ->extraAttributes([
                    'class' => 'neon-box-blue',
                ]),

            /* Kotak 3: Active Alarms
            Stat::make('ACTIVE ALARMS', $criticalAlarms)
                ->description("Critical: {$criticalAlarms} | Warning: {$warningAlarms}")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'neon-box-orange',
                ]),

            // Kotak 4: Uptime Mesin
            Stat::make('MACHINE UPTIME', "{$persentaseUptime}%")
                ->description("Dari total {$totalPengecekan} rekaman")
                ->descriptionIcon('heroicon-m-bolt')
                ->color('primary')
                ->chart([70, 80, 90, 95, $persentaseUptime])
                ->extraAttributes([
                    'class' => 'neon-box-cyan',
                ]), 
            */
        ];
    }
}