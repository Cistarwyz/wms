<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Redis;
use App\Models\UptimeHistory;

class Dashboard extends \Filament\Pages\Dashboard
{

    // 1. Mengubah tulisan yang ada di TAB BROWSER & MENU SIDEBAR
    protected static ?string $title = 'NOC Monitor'; 

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
            Action::make('cek_uptime')
                ->label('Riwayat Uptime')
                ->icon('heroicon-o-clock')
                ->color('success')
                ->modalHeading('Laporan Uptime Mesin Penambang')
                ->modalSubmitAction(false) // Hilangkan tombol submit karena ini cuma lihat data
                ->modalCancelActionLabel('Tutup')
                ->modalContent(function () {
                    // 1. Ambil data LIVE bulan ini dari Redis
                    $bulanIni = date('Y-m');
                    $online = (int) Redis::get("uptime:{$bulanIni}:online") ?: 0;
                    $offline = (int) Redis::get("uptime:{$bulanIni}:offline") ?: 0;
                    $total = $online + $offline;
                    
                    $persentaseBulanIni = $total > 0 
                        ? round(($online / $total) * 100, 2) 
                        : 0;

                    // 2. Ambil data bulan-bulan sebelumnya dari MySQL
                    $riwayatLama = UptimeHistory::orderBy('id', 'desc')->get();

                    // Tampilkan ke layar (Bisa dibuat view blade agar lebih cantik)
                    return view('filament.pages.uptime-modal', [
                        'persentaseBulanIni' => $persentaseBulanIni,
                        'online' => $online,
                        'offline' => $offline,
                        'riwayatLama' => $riwayatLama,
                    ]);
                }),
        ];
    }
}