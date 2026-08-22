<?php

namespace App\Filament\Widgets;

use App\Models\Isp;
use App\Models\IspMetric;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\HtmlString; // <--- TAMBAHKAN INI
use Illuminate\Contracts\Support\Htmlable; // <--- TAMBAHKAN INI

class IspLatencyChart extends ChartWidget
{


    // Mengubah judul agar persis seperti gambar referensi
    protected static ?string $heading = 'NETWORK TRAFFIC OVERVIEW (24H)';
    protected static ?string $pollingInterval = '5s';
    // Membatasi tinggi grafik agar tidak terlalu memakan tempat
    //protected static ?string $maxHeight = '280px';

    // Membuat chart mengambil porsi grid yang lebih lebar (untuk layout nantinya)
    protected static ?int $sort = 3; // Bareng dengan Menu
    protected int | string | array $columnSpan = [
        'default' => 1, // Di HP: Ambil 1 kolom penuh
        'lg' => 8,      // Di PC/Laptop: Ambil 8 kolom
    ];

    // Mengaplikasikan kotak neon (kita tambahkan CSS-nya nanti)

    public function getHeading(): string | Htmlable
    {
        return new HtmlString('
            <span class="target-ungu-hacker">NETWORK TRAFFIC OVERVIEW (24H)</span>
            
            <style>
                /* SIHIR KANVAS LANGSUNG DARI DALAM JANTUNG CHART */
                section.fi-section:has(.target-ungu-hacker) {
                    background-color: #151e32 !important;
                    border: 2px solid #a855f7 !important;
                    box-shadow: 0 0 25px rgba(168, 85, 247, 0.3), inset 0 0 15px rgba(168, 85, 247, 0.1) !important;
                    border-radius: 12px !important;
                    overflow: hidden !important;
                }
                
                /* Tarik judul ke kiri sejajar 99.85% */
                section.fi-section:has(.target-ungu-hacker) .fi-section-header {
                    padding-left: 1.5rem !important;
                    padding-bottom: 0.5rem !important;
                    border-bottom: none !important;
                }
                
                /* Bunuh garis melintang bawaan */
                section.fi-section:has(.target-ungu-hacker) .fi-section-content-ctn {
                    border-top: none !important;
                }
                
                /* Rapikan jarak grafik */
                section.fi-section:has(.target-ungu-hacker) .fi-section-content {
                    padding-top: 0.5rem !important;
                    padding-left: 1.5rem !important;
                    padding-right: 1.5rem !important;
                }
            </style>
        ');
    }

    public function getDescription(): ?string
    {
        $isps = Isp::all();
        $totalDownload = 0;
        
        foreach ($isps as $isp) {
            $trafficData = Redis::hget('isp:traffic', $isp->name);
            
            if ($trafficData) {
                $traffic = json_decode($trafficData, true);
                $totalDownload += $traffic['download_mbps'] ?? 0;
            }
        }

        return "Total Kecepatan Gabungan: " . round($totalDownload, 2) . " Mbps";
    }

    protected function getData(): array
    {
        $isps = Isp::all();
        $datasets = [];
        $labels = [];

        // Palet warna NEON: Cyan, Ungu, Merah Muda, Biru Terang
        $colors = ['#06b6d4', '#a855f7', '#ec4899', '#3b82f6']; 
        // Background transparan untuk area di bawah garis
        $bgColors = ['rgba(6,182,212,0.1)', 'rgba(168,85,247,0.1)', 'rgba(236,72,153,0.1)', 'rgba(59,130,246,0.1)'];

        foreach ($isps as $index => $isp) {
            $metrics = IspMetric::where('isp_id', $isp->id)
                ->latest('created_at')
                ->take(10)
                ->get()
                ->reverse(); 

            if ($index === 0) {
                $labels = $metrics->pluck('created_at')->map(fn($date) => $date->format('H:i'))->toArray();
            }

            $datasets[] = [
                'label' => $isp->name,
                'data' => $metrics->pluck('download_mbps')->toArray(),
                'borderColor' => $colors[$index % count($colors)], 
                'backgroundColor' => $bgColors[$index % count($bgColors)],
                'fill' => true, // Mengaktifkan efek area di bawah garis
                'tension' => 0.5, // Kurva sangat mulus (Bezier)
                'borderWidth' => 3, // Garis lebih tebal
                'pointRadius' => 0, // Sembunyikan titik agar bersih seperti referensi
                'pointHoverRadius' => 6, // Titik baru muncul kalau di-hover mouse
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; 
    }

    // Mengubah tampilan sumbu (X/Y) agar warnanya gelap menyatu dengan tema
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'color' => '#94a3b8',
                        'usePointStyle' => true,
                        'boxWidth' => 8,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'grid' => [
                        'color' => '#1e293b', // Warna garis panduan chart sangat gelap
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'color' => '#94a3b8',
                        'callback' => '(value) => value + " Mbps"', // Tambahkan teks Mbps di sumbu Y
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false, // Hilangkan garis panduan vertikal
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'color' => '#64748b',
                    ],
                ],
            ],
        ];
    }
}