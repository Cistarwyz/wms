<?php

namespace App\Filament\Widgets;


use App\Models\Miner;
use App\Models\Isp;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Redis; // <--- Memastikan Fasad Redis ter-import dengan benar

class IncidentReportWidget extends Widget
{
    // Mengarahkan ke file tampilan visual (Blade)
    protected static string $view = 'filament.widgets.incident-report-widget';
    
    // Memaksa widget ini membentang penuh (Full Width) di dashboard agar sangat mencolok
    protected int | string | array $columnSpan = 'full';

    // Mendengarkan event refresh (berguna nanti jika kita integrasikan dengan WebSockets)
    protected $listeners = ['refresh' => '$refresh'];

    /**
     * Fitur Baru: Mengambil status Mikrotik langsung dari Redis
     */
    public function getMikrotikStatus(): string
    {
        // Jika data di Redis belum terbentuk, default-nya dianggap 'online' agar tidak memicu false alarm
        return Redis::get('system:mikrotik:status') ?? 'online';
    }

    public function getOfflineMiners()
    {
        // Mengambil mesin yang statusnya offline, atau sudah tidak nge-ping selama lebih dari 3 menit
        return Miner::whereNotNull('ip_address')
            ->where(function ($query) {
                $query->where('is_online', false)
                      ->orWhere('last_seen_at', '<', now()->subMinutes(3));
            })->get();
    }

    public function getOfflineIsps()
    {
        // Mengambil ISP yang jalur gateway-nya sedang terputus
        return Isp::where('is_online', false)->get();
    }
}