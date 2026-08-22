<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class NavigationMenuWidget extends Widget
{
    protected static string $view = 'filament.widgets.navigation-menu-widget';
    
   protected static ?int $sort = 3; // Bareng dengan Grafik
    protected int | string | array $columnSpan = [
        'default' => 1, // Di HP: Jatuh ke bawah, ambil 1 kolom penuh
        'lg' => 4,      // Di PC/Laptop: Duduk di samping grafik ambil 4 kolom
    ];
}