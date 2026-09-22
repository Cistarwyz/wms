<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\IspLatencyChart;

class AnalyticDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Analytic';
    protected static ?string $title = 'System Analytics';
    
    // Ini mengarah ke file resources/views/filament/pages/analytic-dashboard.blade.php
    protected static string $view = 'filament.pages.analytic-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [

        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}