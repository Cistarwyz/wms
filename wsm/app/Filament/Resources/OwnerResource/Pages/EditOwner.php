<?php

namespace App\Filament\Resources\OwnerResource\Pages;

use App\Filament\Resources\OwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOwner extends EditRecord
{
    protected static string $resource = OwnerResource::class;

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