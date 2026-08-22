<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Miner;

class LiveEventLogWidget extends Widget
{
    protected static string $view = 'filament.widgets.live-event-log-widget';
    protected static ?int $sort = 4; 
    protected int | string | array $columnSpan = 'full';

    // Matikan polling otomatis agar paginasi tidak ke-reset tiba-tiba
    // protected static ?string $pollingInterval = '5s';

    public int $page = 1;
    public int $perPage = 20; // Menampilkan 20 baris per halaman

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

    // Menggunakan getViewData bawaan Filament agar langsung terinjeksi ke Blade
    protected function getViewData(): array
    {
        $data = Miner::where('is_online', false)->orderBy('updated_at', 'desc')->get();
        $offset = ($this->page - 1) * $this->perPage;
        
        return [
            'items' => $data->slice($offset, $this->perPage),
            'hasMore' => ($offset + $this->perPage) < $data->count(),
        ];
    }
}