<?php

namespace App\Filament\Resources\IspResource\Pages;

use App\Filament\Resources\IspResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIsp extends EditRecord
{
    protected static string $resource = IspResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
