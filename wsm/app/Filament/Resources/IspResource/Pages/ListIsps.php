<?php

namespace App\Filament\Resources\IspResource\Pages;

use App\Filament\Resources\IspResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIsps extends ListRecords
{
    protected static string $resource = IspResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
