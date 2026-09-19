<?php

namespace App\Filament\Resources\MinerResource\Pages;

use App\Filament\Resources\MinerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMiner extends EditRecord
{
    protected static string $resource = MinerResource::class;
  protected function getHeaderActions(): array
    {
        return [
            // Bikin tombol Save manual yang langsung memicu proses save form
            Actions\Action::make('save')
                ->label('Simpan Perubahan')
                ->action('save') 
                ->color('primary'),

            // Bikin tombol Cancel manual yang mengarahkan balik ke halaman tabel
            Actions\Action::make('cancel')
                ->label('Batal')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray'),

            // Tombol Delete bawaan tetap aman di sini
            Actions\DeleteAction::make(),
        ];
    }
}

