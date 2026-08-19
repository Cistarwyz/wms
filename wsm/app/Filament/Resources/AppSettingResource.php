<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages;
use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    // Mengganti ikon dan label di menu sidebar
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Worker';
    protected static ?string $navigationGroup = 'Sistem'; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konfigurasi Ping Jaringan')
                    ->description('Atur bagaimana background worker memindai mesin saat di Production.')
                    ->schema([
                        Forms\Components\Toggle::make('use_chunking')
                            ->label('Aktifkan Mode Partial (Chunking)')
                            ->helperText('Jika nyala, pengecekan akan dipecah. Jika mati, semua IP di-ping sekaligus.')
                            ->default(true),

                        Forms\Components\TextInput::make('chunk_size')
                            ->label('Jumlah Mesin per Rombongan')
                            ->numeric()
                            ->default(50)
                            ->required(),

                        Forms\Components\TextInput::make('chunk_sleep')
                            ->label('Jeda Antar Rombongan (Detik)')
                            ->numeric()
                            ->default(5)
                            ->required(),
                    ])->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('use_chunking')
                    ->label('Mode Partial Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('chunk_size')
                    ->label('Mesin / Kloter')
                    ->numeric(),

                Tables\Columns\TextColumn::make('chunk_sleep')
                    ->label('Jeda')
                    ->formatStateUsing(fn ($state) => $state . ' Detik'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Dikosongkan agar tidak ada yang bisa menghapus data ini secara massal
            ]);
    }

    // --- FITUR TAMBAHAN (PENTING) ---
    // Karena ini adalah "Pengaturan", kita hanya butuh 1 baris data di database.
    // Fungsi ini akan menyembunyikan tombol "Create" atau "New" jika pengaturan sudah ada.
    public static function canCreate(): bool
    {
        return AppSetting::count() === 0;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppSettings::route('/'),
            'create' => Pages\CreateAppSetting::route('/create'),
            'edit' => Pages\EditAppSetting::route('/{record}/edit'),
        ];
    }
}