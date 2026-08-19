<?php

namespace App\Filament\Widgets;

use App\Models\Isp;
use App\Models\IspMetric;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Redis;

class IspLatencyChart extends ChartWidget
{
    protected static ?string $heading = 'ISP Speed Test';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '5s';

    // =======================================================
    public function getDescription(): ?string
    {
        $isps = Isp::all();
        $totalDownload = 0;
        
        foreach ($isps as $isp) {
            // Ambil kecepatan dari brankas Redis
            $trafficData = Redis::hget('isp:traffic', $isp->name);
            
            if ($trafficData) {
                $traffic = json_decode($trafficData, true);
                $totalDownload += $traffic['download_mbps'] ?? 0;
            }
        }

        $totalDownload = round($totalDownload, 2);

        return "Total Kecepatan Gabungan: {$totalDownload} Mbps";
    }

    protected function getData(): array
    {
        $isps = Isp::all();
        $datasets = [];
        $labels = [];

        // Menyiapkan warna yang berbeda untuk 4 ISP
        $colors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b']; 

        foreach ($isps as $index => $isp) {
            // Ambil 10 data ping terakhir dari ISP ini
            $metrics = IspMetric::where('isp_id', $isp->id)
                ->latest('created_at')
                ->take(10)
                ->get()
                ->reverse(); // Balik urutan agar yang tertua di kiri, terbaru di kanan

            // Jika ini ISP pertama, kita gunakan waktu (jam:menit) sebagai label sumbu X di bawah grafik
            if ($index === 0) {
                $labels = $metrics->pluck('created_at')->map(fn($date) => $date->format('H:i:s'))->toArray();
            }

            $datasets[] = [
                'label' => $isp->name,
                'data' => $metrics->pluck('download_mbps')->toArray(),
                'borderColor' => $colors[$index % count($colors)], // Beri warna garis
                'fill' => false,
                'tension' => 0.4, // Membuat garis melengkung (smooth)
                'spanGaps' => true,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Jenis grafik
    }
    

    

}