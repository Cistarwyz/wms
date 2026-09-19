<?php

namespace App\Filament\Resources\WorkshopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ShelfResource; // <-- WAJIB TAMBAHKAN IMPORT INI DI ATAS

class ShelvesRelationManager extends RelationManager
{
    protected static string $relationship = 'shelves';
    protected static ?string $title = 'Daftar Rak (Shelf)';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Rak')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name') 
            // 1. Agar seluruh baris tabel bisa diklik dan melempar ke halaman Shelf
            ->recordUrl(
                fn ($record): string => ShelfResource::getUrl('edit', ['record' => $record])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Rak')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Rak Baru'),
                Tables\Actions\AssociateAction::make()
                    ->label('Assign Rak')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                // 2. Ubah EditAction agar mengarah ke URL halaman Edit Shelf
                Tables\Actions\EditAction::make()
                    ->url(fn ($record): string => ShelfResource::getUrl('edit', ['record' => $record])),
                
                Tables\Actions\DissociateAction::make()
                    ->label('Keluarkan dari Workshop'),
                    
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make()
                        ->label('Keluarkan yang Terpilih'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}