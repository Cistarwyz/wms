<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MinerResource\Pages;
use App\Models\Miner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use App\Models\Shelf;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;

class MinerResource extends Resource
{
    protected static ?string $model = Miner::class;
    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'Manajemen Mesin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mac_address')
                    ->label('Pilih Mesin')
                    ->options(function (?\App\Models\Miner $record) {
                        $options = [];
                        $redisData = \Illuminate\Support\Facades\Redis::hgetall('miner:status');
                        
                        if ($record) {
                            $registeredMacs = \App\Models\Miner::where('id', '!=', $record->id)
                                                    ->pluck('mac_address')
                                                    ->toArray();
                        } else {
                            $registeredMacs = \App\Models\Miner::pluck('mac_address')->toArray();
                        }

                        foreach ($redisData as $ip => $json) {
                            $data = json_decode($json, true);
                            $mac = $data['mac'] ?? null;
                            $name = $data['name'] ?? 'Unknown';
                            
                            if ($mac && !in_array($mac, $registeredMacs)) {
                                $options[$mac] = "{$name} — MAC: {$mac} (IP: {$ip})";
                            }
                        }
                        
                        if ($record && !isset($options[$record->mac_address])) {
                            $options[$record->mac_address] = "{$record->name} — MAC: {$record->mac_address} (Offline/Terputus)";
                        }
                        
                        return $options;
                    })
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->live() 
                    ->suffixAction(
                        Action::make('refresh_redis')
                            ->icon('heroicon-m-arrow-path')
                            ->tooltip('Ambil data terbaru dari jaringan')
                            ->action(fn () => null) 
                    )
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        if (!$state) {
                            $set('ip_address', null);
                            return;
                        }

                        $redisData = \Illuminate\Support\Facades\Redis::hgetall('miner:status');
                        foreach ($redisData as $ip => $json) {
                            $data = json_decode($json, true);
                            
                            if (isset($data['mac']) && $data['mac'] === $state) {
                                $set('ip_address', $ip); 
                                
                                if (!$get('name') && isset($data['name'])) {
                                    if (str_contains(strtoupper($data['name']), 'SPX')) {
                                        $set('name', $data['name']); 
                                    }
                                }
                                break;
                            }
                        }
                    }),

                Forms\Components\TextInput::make('name')
                    ->label('Nama / Identitas Mesin')
                    ->placeholder('Contoh: SPX-A2')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('ip_address')
                    ->label('Alamat IP')
                    ->ipv4()
                    ->nullable() // FIX: Wajib ditambahkan agar kalau kosong tidak error validasi IP
                    ->unique(ignoreRecord: true),
            
               Forms\Components\Fieldset::make('Informasi Kepemilikan & Lokasi Fisik')
                    ->schema([
                        Forms\Components\Select::make('owner_id')
                            ->label('Nama User / Pemilik')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable() // FIX: Mencegah error diam-diam jika kosong
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap User')
                                    ->required()
                                    ->unique('owners', 'name')
                            ])
                            ->columnSpanFull(),
                        // 1. TAMBAHKAN DROPDOWN WORKSHOP
                        Forms\Components\Select::make('workshop_id')
                            ->label('Lokasi Workshop')
                            ->options(\App\Models\Workshop::pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('shelf_id', null)), // Reset rak jika workshop diganti
                            
                        // 2. DROPDOWN PILIH RAK (SHELF)
        Forms\Components\Select::make('shelf_id')
            ->label('Pilih Rak (Shelf)')
            ->options(function (\Filament\Forms\Get $get) {
                $workshopId = $get('workshop_id');
                if (!$workshopId) {
                    return [];
                }
                return \App\Models\Shelf::where('workshop_id', $workshopId)->pluck('name', 'id');
            })
            ->searchable()
            ->live()
            ->required()
            // JIKA RAK DIPILIH, OTOMATIS ISI 'shelf_number'
            ->afterStateUpdated(function ($state, \Filament\Forms\Set $set) {
                $shelf = \App\Models\Shelf::find($state);
                if ($shelf) {
                    $set('shelf_number', $shelf->name);
                }
            }),

                // 3. SHELF NUMBER TETAP ADA (Dibuat ReadOnly agar otomatis terisi dari pilihan rak)
                Forms\Components\TextInput::make('shelf_number')
                    ->label('Kode Rak (Shelf Number)')
                    ->readOnly() // User tidak bisa ketik manual, harus pilih lewat dropdown di atas
                    ->required(),

                Forms\Components\Select::make('shelf_level')
                    ->label('Tingkat')
                    ->options([
                        1 => 'Tingkat 1',
                        2 => 'Tingkat 2',
                        3 => 'Tingkat 3',
                        4 => 'Tingkat 4',
                        5 => 'Tingkat 5',
                        6 => 'Tingkat 6',
                    ])
                    ->live()
                    ->required(),

                Forms\Components\TextInput::make('slot_number')
                    ->label('Urutan/Posisi')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(18)
                    ->placeholder('1 - 18')
                    ->live(debounce: 500)
                    ->required(),
                 ])->columns(3), 

                Forms\Components\Placeholder::make('rack_visualizer')
                    ->hiddenLabel()
                    ->content(function (\Filament\Forms\Get $get) {
                        return view('filament.components.rack-visualizer', [
                            'shelf_number' => $get('shelf_number'),
                            'shelf_level'  => (int) $get('shelf_level'),
                            'slot_number'  => (int) $get('slot_number'),
                        ]);
                    })
                    ->columnSpanFull(),
         ]);
    }

    // ... (Fungsi table() biarkan seperti sebelumnya)
   public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nomor Mesin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('shelf_number')
                    ->label('Posisi Rak')
                    ->searchable()
                    ->sortable(),

                // Ubah label slot_number menjadi Posisi Urut
                Tables\Columns\TextColumn::make('slot_number')
                    ->label('Posisi Urut')
                    ->searchable()
                    ->sortable(),


                // (Opsional) Jika ingin menampilkan Tingkat juga
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
            // Tambahkan filter ini
            SelectFilter::make('shelf_number')
                ->label('Posisi Rak')
                ->options(function () {
                    return Miner::whereNotNull('shelf_number')
                        ->distinct()
                        ->pluck('shelf_number', 'shelf_number')
                        ->toArray();
                }),
            ])
            ->actions([
               
                Tables\Actions\Action::make('lihat_rak')
                    ->label('Lihat Rak')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    // Judul Pop-up disesuaikan dengan nama mesin yang diklik
                    ->modalHeading(fn (\App\Models\Miner $record) => 'Lokasi Mesin: ' . $record->name) 
                    // Memanggil file Blade Visualizer dan melempar data dari database
                    ->modalContent(fn (\App\Models\Miner $record) => view('filament.components.rack-visualizer', [
                        'shelf_number' => $record->shelf_number,
                        'shelf_level'  => $record->shelf_level,
                        'slot_number'  => $record->slot_number,
                    ]))
                    // Hilangkan tombol "Submit" karena ini hanya untuk melihat
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                // Tombol bawaan yang sudah ada
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMiners::route('/'),
            'create' => Pages\CreateMiner::route('/create'),
            'edit' => Pages\EditMiner::route('/{record}/edit'),
        ];
    }
}
