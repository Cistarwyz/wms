<?php

namespace App\Filament\Resources\MinerResource\Pages;

use App\Filament\Resources\MinerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMiner extends EditRecord
{
    protected static string $resource = MinerResource::class;

     // 1. Tambahkan tombol Cancel dan Save di deretan Header atas
    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make(),
        ];
    }

    // 2. Kosongkan aksi form di bawah agar tombolnya tidak dobel
    protected function getFormActions(): array
    {
        return [];
    }
}

