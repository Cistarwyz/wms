<?php

namespace App\Filament\Resources\OwnerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\MinerResource;

class MinersRelationManager extends RelationManager
{
    protected static string $relationship = 'miners';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->recordUrl(
                fn ($record): string => MinerResource::getUrl('edit', ['record' => $record]))
        ->recordTitleAttribute('name')
        ->heading(new HtmlString('<span class="text-2xl font-bold">Daftar Mesin Milik Miner</span>'))
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Mesin')
                ->searchable(),
                
            Tables\Columns\TextColumn::make('mac_address')
                ->label('MAC Address'),
                
            Tables\Columns\TextColumn::make('slot_number')
                ->label('Posisi Rak (ID Global)')
                ->badge()
                ->color('warning')
                ->sortable(),
        ])
        ->filters([
            //
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()
                ->label('Tambah Mesin Baru'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }   
}
