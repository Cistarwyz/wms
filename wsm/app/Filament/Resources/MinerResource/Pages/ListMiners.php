<?php

namespace App\Filament\Resources\MinerResource\Pages;

use App\Filament\Resources\MinerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Miner;

class ListMiners extends ListRecords
{
    protected static string $resource = MinerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function updatedTableSearch(): void
    {
        // 1. Wajib panggil parent agar fungsi tabel tetap normal
        parent::updatedTableSearch();

        // 2. Ambil ketikan user dan jadikan huruf besar otomatis
        $search = strtoupper(trim($this->tableSearch));

        // Abaikan jika kosong
        if (empty($search)) {
            return;
        }

        // 3. Prioritas Pertama: Coba cari yang cocok 100% persis (Misal user emang rajin ngetik "536A")
        $miner = Miner::where('name', $search)->first();

        // 4. Prioritas Kedua: Kalau nggak ada yang persis, cari yang berawalan angka tersebut (Misal ngetik "536" aja)
        if (!$miner) {
            $matchingMiners = Miner::where('name', 'LIKE', $search . '%')->get();
            
            // KUNCINYA DI SINI: Hanya auto-popup KALAU cuma ada 1 mesin yang cocok
            if ($matchingMiners->count() === 1) {
                $miner = $matchingMiners->first();
            }
        }

        // 5. Eksekusi pop-up jika mesin ditemukan secara valid!
        if ($miner) {
            $this->mountTableAction('lihat_rak', $miner->id);
        }
    }
}