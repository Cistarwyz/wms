<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Redis;

class MinerUptimeWidget extends BaseWidget
{
    // Bikin widget ini otomatis refresh tiap 10 detik tanpa perlu reload halaman!
    protected static ?string $pollingInterval = '10s';
    
        protected function getStats(): array
    {
        $bulanIni = date('Y-m');
        
        // Ambil data langsung dari brankas Redis
        $online = (int) \Illuminate\Support\Facades\Redis::get("uptime:{$bulanIni}:online") ?: 0;
        $offline = (int) \Illuminate\Support\Facades\Redis::get("uptime:{$bulanIni}:offline") ?: 0;
        
        $total = $online + $offline;

        // Hitung persentase (bulatkan 2 angka di belakang koma)
        $persentase = $total > 0 
            ? round(($online / $total) * 100, 2) 
            : 0;

        // Bikin logika warna otomatis
        $warna = $persentase >= 95 ? 'success' : ($persentase >= 80 ? 'warning' : 'danger');
        $icon = $persentase >= 95 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        // ==========================================
        // AMBIL JUMLAH TOTAL MESIN DARI DATABASE
        // ==========================================
        $totalMesin = \App\Models\Miner::count();

        return [
            Stat::make('Total Pengecekan Sukses', number_format($online))
                ->description('Hasil Data Per 10 Detik')
                ->color('success'),
                
            Stat::make('Uptime Mesin (Bulan Ini)', "{$persentase}%")
                ->description("Dari total {$total} rekaman jaringan")
                ->descriptionIcon($icon)
                ->color($warna)
                ->chart([70, 80, 90, 95, $persentase]), 
                
            // ==========================================
            // KOTAK KETIGA DIUBAH JADI JUMLAH MESIN
            // ==========================================
            Stat::make('Total Mesin Terdaftar', number_format($totalMesin))
                ->description('Jumlah unit di dalam sistem')
                ->descriptionIcon('heroicon-m-server-stack') // Ikon server yang cocok untuk NOC
                ->color('info'), // Warna biru agar terlihat beda
        ];
    }
}