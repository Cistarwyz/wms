<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\IspLatencyChart;
use App\Filament\Widgets\MinerStatusChart;
use App\Filament\Widgets\MinerUptimeWidget;
// Tambahkan use widget lain di sini nanti (misal BarChart untuk Traffic Mikrotik)

class AnalyticDashboard extends Page
{
    // Mengganti icon dan nama menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Analytic';
    protected static ?string $title = 'System Analytics';
    protected static ?int $navigationSort = 2; // Taruh di bawah menu Dashboard utama

    // Lokasi file tampilan kosong bawaan
    protected static string $view = 'filament.pages.analytic-dashboard';

    // 1. DAFTARKAN SEMUA GRAFIK DI SINI
    protected function getHeaderWidgets(): array
    {
        return [
            // Pindahkan chart ISP dari dashboard utama ke sini
            MinerUptimeWidget::class,
            IspLatencyChart::class, 
            
            
            // Nanti Anda bisa buat dan masukkan widget lain seperti:
            // MikrotikTrafficChart::class,
            // MinerUptimeStats::class,
        ];
    }

    // 2. ATUR GRID LAYOUT (Agar padat seperti referensi)
    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'md' => 2, // Di layar sedang, bagi jadi 2 kolom
            'xl' => 3, // Di layar besar, bagi jadi 3 kolom agar rapat
        ];
    }
}