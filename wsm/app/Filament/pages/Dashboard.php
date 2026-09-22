<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Redis;
use App\Models\UptimeHistory;

class Dashboard extends \Filament\Pages\Dashboard
{

    // 1. Mengubah tulisan yang ada di TAB BROWSER & MENU SIDEBAR
    protected static ?string $title = 'Home'; 

    // 2. Mengubah tulisan GEDE yang ada di bagian atas halaman
    protected ?string $heading = 'Wilis Monitoring System';

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,  // Di HP (layar kecil), paksa jadi 1 kolom aja (atas-bawah)
            'lg' => 12,      // Di Layar besar (Laptop/PC), baru bentangkan 12 kolom
        ];
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}