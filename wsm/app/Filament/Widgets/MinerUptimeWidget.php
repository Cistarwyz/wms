<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Redis;
use App\Models\Miner;
use App\Models\Isp;
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

    protected function getStats(): array
    {
        $bulanIni = date('Y-m');
        
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

        return [
            // Kotak 1: Uptime Jaringan
           Stat::make('NETWORK AVAILABILITY', "{$availability}%")
                ->description('Status: ONLINE')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->extraAttributes([
                    'class' => 'neon-box-green', // Class custom untuk CSS nanti
                ]),

            // Kotak 2: Total Mesin & Rincian
            Stat::make('TOTAL DEVICES', number_format($totalMesin))
                ->description("{$mesinOnline} Online | {$mesinDown} Down")
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('info')
                ->extraAttributes([
                    'class' => 'neon-box-blue',
                ]),
                
            // Kotak 3: Active Alarms
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
        ];
    }
}