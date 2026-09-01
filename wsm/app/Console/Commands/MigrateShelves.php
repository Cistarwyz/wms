<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Miner;
use App\Models\Shelf;

class MigrateShelves extends Command
{
    // Nama perintah yang akan kita jalankan nanti di terminal
    protected $signature = 'app:migrate-shelves';

    // Deskripsi perintah
    protected $description = 'Memindahkan data rak dari text biasa ke tabel relasi shelves';

    public function handle()
    {
        $this->info('Memulai proses migrasi rak...');

        // 1. Ambil semua nama rak yang unik dari tabel miners (misal: "A", "B", "C")
        $listRakText = Miner::whereNotNull('shelf_number')
            ->distinct()
            ->pluck('shelf_number');

        $totalRak = 0;
        $totalMesinDiupdate = 0;

        // 2. Looping setiap nama rak
        foreach ($listRakText as $namaRak) {
            // Buat rak baru di tabel shelves (kalau belum ada)
            $shelf = Shelf::firstOrCreate([
                'name' => $namaRak
            ]);
            $totalRak++;

            // 3. Jahit (update) semua mesin yang punya shelf_number ini ke ID rak yang baru
            $mesinTerupdate = Miner::where('shelf_number', $namaRak)
                ->update(['shelf_id' => $shelf->id]);
                
            $totalMesinDiupdate += $mesinTerupdate;

            $this->line("Rak {$namaRak} berhasil dibuat. ({$mesinTerupdate} mesin dimasukkan)");
        }

        $this->info("SELESAI! Berhasil membuat {$totalRak} Rak dan memindahkan {$totalMesinDiupdate} Mesin.");
    }
}