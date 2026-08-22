<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentResource\Pages;
use App\Filament\Resources\IncidentResource\RelationManagers;
use App\Models\Incident;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;


class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('ID Insiden')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Keterangan')
                    ->color('danger'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'acknowledged',
                        'success' => 'resolved',
                    ]),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // TOMBOL UNTUK MELIHAT DAFTAR MESIN YANG MATI
                 // TOMBOL UNTUK MELIHAT DAFTAR MESIN YANG MATI
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail Mesin')
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('affected_miners_list')
                            ->label('Daftar Mesin Terdampak')
                            ->content(function (Incident $record) {
                                $miners = $record->affected_miners;
                                
                                if (empty($miners) || !is_array($miners)) {
                                    return new \Illuminate\Support\HtmlString('<em>Tidak ada detail mesin yang terdampak.</em>');
                                }

                                $html = '<ul style="list-style-type: disc; padding-left: 20px;">';
                                foreach($miners as $minerData) {
                                    $nama = $minerData['name'] ?? 'Unknown';
                                    $ip = $minerData['ip'] ?? '-';
                                    
                                    // Panggil data fisik terbaru langsung dari database!
                                    // Pastikan model Miner sudah di-use di atas file ini (use App\Models\Miner;)
                                   $minerDb = \App\Models\Miner::find($minerData['id'] ?? 0);

                                    $rak = $minerDb->rack_number ?? '-'; 
                                    $lvl = $minerDb->rack_level ?? '-';
                                    $urutan = $minerDb->rack_position ?? '-';

                                    $html .= "<li style='margin-bottom: 5px;'>
                                                <strong>{$nama}</strong> (IP: {$ip})<br>
                                                <span class='text-sm text-gray-500'>Lokasi: Rak {$rak} | Level {$lvl} | Urutan ke-{$urutan}</span>
                                            </li>";
                                }
                                $html .= '</ul>';
                                
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]), 

                // TOMBOL AMBIL ALIH (ACKNOWLEDGE)
                Tables\Actions\Action::make('acknowledge')
                    ->label('Tangani Sekarang')
                    ->icon('heroicon-o-hand-raised')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Incident $record) => $record->status === 'open')
                    ->action(fn (Incident $record) => $record->update(['status' => 'acknowledged'])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageIncidents::route('/'),
        ];
    }
}
