<?php

namespace App\Filament\Resources\OwnerResource\Pages;

use App\Filament\Resources\OwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOwner extends EditRecord
{
    protected static string $resource = OwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Gunakan Action manual agar tombol ini memicu proses save form
            Actions\Action::make('save')
                ->label('Save changes')
                ->action('save') // <--- Kunci utamanya ada di sini
                ->color('primary'),

            // Tombol cancel diarahkan kembali ke tabel
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray'),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return []; // Tetap kosongkan form bawah agar tombol tidak dobel
    }
}