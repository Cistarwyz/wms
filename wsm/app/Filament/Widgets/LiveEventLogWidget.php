<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Redis;

class LiveEventLogWidget extends Widget
{
    // Arahkan ke file tampilan (Blade)
    protected static string $view = 'filament.widgets.live-event-log-widget';
    
    // Auto-refresh setiap 5 detik agar log bergerak sendiri
    protected static ?string $pollingInterval = '5s';
    
    // Urutan widget (pastikan berada di sebelah grafik)
    protected static ?int $sort = 3; 

    // Fungsi untuk mengambil 20 log terakhir dari Redis
    public function getLogs(): array
    {
        return Redis::lrange('noc:live_logs', 0, 19) ?: [];
    }
}