<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShelfResource\Pages;
use App\Filament\Resources\ShelfResource\RelationManagers;
use App\Models\Shelf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\View;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ShelfResource\RelationManagers\MinersRelationManager;

class ShelfResource extends Resource
{
    protected static ?string $model = Shelf::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
      return $form
        ->schema([
            // Panggil file khusus Rak
            View::make('filament.components.rack-visualizer-shelf') 
                ->columnSpanFull(),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
            'md' => 3, // 3 kotak sejajar di layar sedang
            'xl' => 5, // 5 kotak sejajar di layar besar (seperti gambarmu)
        ])
        ->columns([
            // 2. MEMANGGIL DESAIN KOTAK KUSTOM
            ViewColumn::make('id')
                ->view('filament.components.rack-card')
        ])
        // 3. MENGATUR KLIK KOTAK LANGSUNG MASUK KE HALAMAN EDIT
        ->recordUrl(
            fn (Model $record): string => Pages\EditShelf::getUrl([$record->id])
        )
        ->filters([
            //
        ])
        ->actions([
            // Bisa dikosongkan karena klik kotaknya sudah langsung ke Edit
        ])
        ->bulkActions([
            //
        ]);
    }

    public static function getRelations(): array
    {
    return [
        MinersRelationManager::class,
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
}
