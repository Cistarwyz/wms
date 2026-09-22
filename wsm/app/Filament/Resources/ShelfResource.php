<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShelfResource\Pages;
use App\Filament\Resources\ShelfResource\RelationManagers;
use App\Models\Shelf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;



use Filament\Forms\Components\View;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ShelfResource\RelationManagers\MinersRelationManager;
use Filament\Forms\Components\Placeholder;


class ShelfResource extends Resource
{
    protected static ?string $model = Shelf::class;
    protected static ?string $navigationLabel = 'Daftar Rak';
    protected static ?string $navigationIcon = 'clarity-rack-server-line';
   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Select::make('workshop_id')
                ->relationship('workshop', 'name')
                ->label('Lokasi Workshop')
                ->default(fn () => auth()->user()->workshop_id)
                ->disabled(fn () => auth()->user()->role === 'pos')
                ->dehydrated()
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('name')
                ->label('Nama Shelf')
                ->required(),
            // Memanggil Visualizer Rak (Sesuai Gambar 1)
            Placeholder::make('visualizer')
                ->hiddenLabel()
                ->content(function (?Shelf $record) {
                    if (! $record) {
                        return ''; 
                    }
                    
                    return view('filament.components.rack-visualizer', [
                        // Kirim data yang dibutuhkan blade
                        'shelf_number' => $record->name,  
                        'shelf_level'  => null, // Kosongkan untuk trigger mode "Rak Penuh"
                        'slot_number'  => null, // Kosongkan
                        'miners'       => $record->miners // Kirim data seluruh mesin di rak ini
                    ]);
                })
                ->columnSpanFull(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            // 1. Tambahkan baris ini untuk menghitung total mesin secara otomatis & efisien
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('miners'))
            ->columns([
                TextColumn::make('workshop.name')
                    ->label('Workshop')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Shelf')
                    ->searchable()
                    ->sortable(),

                // 2. Tampilkan jumlah mesin yang terisi (miners_count didapat dari modifyQueryUsing di atas)
                TextColumn::make('miners_count')
                    ->label('Kapasitas / Unit')
                    ->formatStateUsing(fn ($state) => $state . ' / 108 Unit')
                    ->sortable(),

                // 3. Tambahkan kolom Sisa Slot hasil pengurangan 108 dengan mesin yang ada
                TextColumn::make('sisa_slot')
                    ->label('Sisa Slot')
                    ->getStateUsing(fn ($record) => 108 - $record->miners_count)
                    ->badge()
                    // Beri warna hijau jika masih ada slot kosong, dan merah jika sudah penuh (0)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
            ])
        ->filters([
            //
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

    public static function getRelations(): array
    {
    return [
        RelationManagers\MinersRelationManager::class,
     ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShelves::route('/'),
            'create' => Pages\CreateShelf::route('/create'),
            'edit' => Pages\EditShelf::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->role === 'pos') {
            // Menggunakan 'id' karena ini adalah tabel workshops itu sendiri
            $query->where('id', auth()->user()->workshop_id);
        }

        return $query;
    }
}
