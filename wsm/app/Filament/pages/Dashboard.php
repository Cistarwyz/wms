<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Redis;
use App\Models\UptimeHistory;

class Dashboard extends \Filament\Pages\Dashboard
{
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