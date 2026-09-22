<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\ImportLog; // <--- Import model yang benar

class NavigationMenuWidget extends Widget
{
    protected static string $view = 'filament.widgets.navigation-menu-widget';
    
    protected static ?int $sort = 3; 
    protected int | string | array $columnSpan = [
        'default' => 'full', 
        'lg' => 'full',      
    ];

    protected function getViewData(): array
    {
        return [
            // Gunakan ImportLog sesuai dengan model yang di-import di atas
            'logs' => ImportLog::where('type', 'import') 
                        ->latest()
                        ->take(10)
                        ->get(),
        ];
    }
}