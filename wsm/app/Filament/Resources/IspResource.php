<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IspResource\Pages;
use App\Filament\Resources\IspResource\RelationManagers;
use App\Models\Isp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IspResource extends Resource
{
    protected static ?string $model = Isp::class;
    protected static ?string $navigationLabel = 'ISP';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->label('nomor port')
                ->required()
                ->placeholder('ether1')
                ->maxLength(255),
                
            Forms\Components\TextInput::make('Port')
                ->label('Nomor Port')
                ->required()
                ->placeholder('Contoh: Port 1')
                ->maxLength(255),

            Forms\Components\TextInput::make('gateway_ip')
                ->label('IP Gateway / IP Target Ping')
                ->required()
                ->ipv4()
                ->placeholder('Contoh: 192.168.20.1 atau 8.8.8.8'),
        ]);
}

    public static function table(Table $table): Table
    {
    return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Provider ISP')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('Port')
                    ->label('Nomor Port')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gateway_ip')
                    ->label('IP Gateway'),
                    
                Tables\Columns\IconColumn::make('is_online')
                    ->label('Status Saat Ini')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                    
                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('Pengecekan Terakhir')
                    ->dateTime('H:i:s')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
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
            'index' => Pages\ListIsps::route('/'),
            'create' => Pages\CreateIsp::route('/create'),
            'edit' => Pages\EditIsp::route('/{record}/edit'),
        ];
    }
}
