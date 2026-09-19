<?php

namespace App\Filament\Resources\ShelfResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\MinerResource;

class MinersRelationManager extends RelationManager
{
    protected static string $relationship = 'miners';
    
    // Judul tabel
    protected static ?string $title = 'Daftar Mesin di Rak Ini';

    // Fitur: Otomatis buka modal detail jika hasil pencarian cuma 1
    public function updatedTableSearch(): void
    {
        $value = $this->getTableSearch();

        if (!empty($value)) {
            $query = $this->getFilteredTableQuery();
            
            if ($query->count() === 1) {
                $record = $query->first();
                $this->mountTableAction('lihat_detail', $record->getKey());
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name') // Kolom acuan untuk pencarian saat fitur Associate
            ->recordUrl(
                fn ($record): string => MinerResource::getUrl('edit', ['record' => $record])
            )
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nomor Mesin')
                    ->searchable(),

                // Karena kita sudah berada di dalam halaman Rak, kolom 'Posisi Rak' 
                // sebenarnya opsional, jadi kita beri toggleable agar bisa disembunyikan.
                Tables\Columns\TextColumn::make('shelf_number')
                    ->label('Posisi Rak')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('slot_number')
                    ->label('Posisi Urut')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('shelf_level')
                    ->label('Level Rak')
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Pemilik')
                    ->searchable() 
                    ->sortable()
                    ->badge() 
                    ->color('info') 
                    ->default('Belum ada pemilik'),
                     
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mac_address')
                    ->label('MAC Address')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Anda bisa menambahkan filter lain di sini jika perlu
            ])
            ->headerActions([
                // TOMBOL BARU: Untuk menarik/memasukkan mesin yang sudah ada ke dalam rak ini
                Tables\Actions\AssociateAction::make()
                    ->label('Masukkan Mesin ke Rak')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat_detail') 
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn ($record) => 'Detail Mesin: ' . $record->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn ($record) => view('filament.components.detail-mesin-table', ['mesin' => $record])),
                
                // Mengarahkan tombol Edit langsung ke halaman Edit Mesin penuh
                Tables\Actions\EditAction::make()
                    ->url(fn ($record): string => MinerResource::getUrl('edit', ['record' => $record])),
                
                // TOMBOL BARU: Mengeluarkan mesin dari rak ini (Ubah shelf_id jadi null)
                Tables\Actions\DissociateAction::make()
                    ->label('Keluarkan dari Rak'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Mengeluarkan banyak mesin sekaligus dari rak ini
                    Tables\Actions\DissociateBulkAction::make()
                        ->label('Keluarkan yang Terpilih'),
                ]),
            ]);
    }
}