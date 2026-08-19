<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Notifications\Notification;
use App\Models\Miner;

class DemoSimulationWidget extends Widget
{
    protected static string $view = 'filament.widgets.demo-simulation-widget';
    // Ganti nilai sort menjadi angka besar agar ditaruh di antrean paling akhir
    protected static ?int $sort = -1; 
    
    // Pastikan columnSpan full agar tidak menggeser widget lain di sebelahnya
    protected int | string | array $columnSpan = 'full';

    public function simulasikanMesinMati()
    {
        $miner = \App\Models\Miner::inRandomOrder()->first();
        if (!$miner) return;

        // Hitung Posisi
        $slot = $miner->slot_number;
        $rak = ceil($slot / 64);
        $sisaRak = $slot % 64 ?: 64; 
        $tingkat = ceil($sisaRak / 16);
        $urutan = $sisaRak % 16 ?: 16;

        $time = now()->format('H:i:s');
        \Illuminate\Support\Facades\Redis::lpush('noc:live_logs', "[{$time}] 🔴 ALERT! Mesin {$miner->name} OFFLINE (Rak {$rak} Lv{$tingkat})");
        $miner->update(['is_online' => false]);

        $this->dispatch('tampilkan-alert', [
            'tipe' => 'mesin',
            'judul' => 'CRITICAL ALERT: MESIN DOWN!',
            'namaMesin' => $miner->name,
            'macMesin' => $miner->mac_address,
            'rak' => $rak,
            'tingkat' => $tingkat,
            'urutan' => $urutan,
            'slotGlobal' => $slot // <--- TAMBAHAN BARU
        ]);
    }

    public function simulasikanIspMati()
    {
        $time = now()->format('H:i:s');
        \Illuminate\Support\Facades\Redis::lpush('noc:live_logs', "[{$time}] 🚨 FATAL! KONEKSI ISP TERPUTUS!");

        $this->dispatch('tampilkan-alert', [
            'tipe' => 'isp',
            'judul' => 'ISP CONNECTION LOST',
            'pesan' => 'Jalur utama ISP terputus. Segera Cek Perangkat'
        ]);
    }
}