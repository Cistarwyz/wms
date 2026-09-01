<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Miner;
use Illuminate\Support\Facades\DB;

class DataRak extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack'; // Icon server/rak
    protected static ?string $navigationLabel = 'Data Rak';
    protected static ?string $title = 'Daftar Rak Mesin';
    protected static ?string $navigationGroup = 'Manajemen Mesin'; // Disesuaikan dengan grup kamu
    
    protected static string $view = 'filament.pages.data-rak';

    // Fungsi untuk mengambil data rak unik dan menghitung jumlah mesinnya
    protected function getViewData(): array
    {
        $listRak = Miner::selectRaw('shelf_number, count(*) as total_mesin')
            ->whereNotNull('shelf_number')
            ->groupBy('shelf_number')
            ->orderBy('shelf_number')
            ->get();

        return [
            'listRak' => $listRak,
        ];
    }
}