<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\Widget;

class LiveEventLogWidget extends Widget
{
    // Pastikan nama view ini sesuai dengan nama file Blade Anda di bawah
    protected static string $view = 'filament.widgets.live-event-log-widget';

    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 2;

    public int $page = 1;
    public int $perPage = 5;

    public function nextPage()
    {
        $this->page++;
    }

    public function prevPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    // Ini adalah fungsi resmi Filament untuk mengirim variabel ke Blade
    protected function getViewData(): array
    {
        $totalLogs = AuditLog::count();
        
        return [
            'logs' => AuditLog::latest()
                ->skip(($this->page - 1) * $this->perPage)
                ->take($this->perPage)
                ->get(),
            'hasMore' => ($this->page * $this->perPage) < $totalLogs,
        ];
    }
}