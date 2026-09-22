<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerResource\Pages;
use App\Filament\Resources\OwnerResource\RelationManagers;
use App\Models\Owner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OwnerResource extends Resource
{
    protected static ?string $model = Owner::class;

    // Mengubah icon menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    // Mengubah nama menu di sidebar
    protected static ?string $navigationLabel = 'Data Miner';
    protected static ?string $modelLabel = 'Miner / Pemilik';
    protected static ?string $pluralModelLabel = 'Daftar Miner';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi Miner')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('referral')
                            ->label('Referral')
                            ->default('-'),
                            
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK (Nomor Induk Kependudukan)')
                            ->numeric()
                            ->maxLength(16),
                            
                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon / WhatsApp')
                            ->tel()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
         // DIPINDAH KE SINI (Disesuaikan posisinya, misal sejajar dengan telepon)
                        Forms\Components\Select::make('workshop_id')
                            ->relationship('workshop', 'name')
                            ->default(fn () => auth()->user()->workshop_id) 
                            ->disabled(fn () => auth()->user()->role === 'pos') 
                            ->dehydrated() 
                            ->required()
                            ->label('Workshop'),
                        ])->columns(2), // Membagi form jadi 2 kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Miner')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                 Tables\Columns\TextColumn::make('referral')
                    ->label('Referral')
                    ->searchable()
                    ->default('-'),
                    

                // Menampilkan total mesin yang dimiliki user ini
                Tables\Columns\TextColumn::make('miners_count')
                    ->counts('miners')
                    ->label('Total Mesin')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
           // ==========================================
            // TOMBOL IMPORT CSV DI HEADER TABEL
            // ==========================================
            ->headerActions([
                Tables\Actions\Action::make('importCsv')
                    ->label('Import Data CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->form([
                        Forms\Components\FileUpload::make('csv_file')
                            ->label('Pilih File CSV')
                            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', '.csv'])
                            ->required()
                            ->helperText('Urutan kolom CSV: workshop, owner_name, owner_nik, owner_email, owner_phone, owner_address, miner_name, miner_mac, shelf_number, shelf_level, slot_number'),
                    ])
                    ->action(function (array $data) {
                        $filePath = storage_path('app/public/' . $data['csv_file']);
                        if (!file_exists($filePath)) {
                            $filePath = storage_path('app/' . $data['csv_file']);
                        }

                        if (!file_exists($filePath)) {
                            \Filament\Notifications\Notification::make()->title('File CSV tidak ditemukan di server!')->danger()->send();
                            return;
                        }

                        $rowcount = 0;
                       if (($handle = fopen($filePath, 'r')) !== FALSE) {
                            $header = fgetcsv($handle, 1000, ","); 

                            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                
                        $workshopName  = trim($row[0] ?? '');
                        
                        $ownerName     = trim($row[1] ?? '');
                        if (empty($ownerName) || strtolower($ownerName) === 'owner_name') continue; 
                        
                        $ownerNik      = (!empty(trim($row[2])) && trim($row[2]) !== '-') ? trim($row[2]) : null;
                        $ownerReferral = (!empty(trim($row[2])) && trim($row[2]) !== '-') ? trim($row[2]) : null;
                        $ownerEmail    = (!empty(trim($row[3])) && trim($row[3]) !== '-') ? trim($row[3]) : null;
                        $ownerPhone    = (!empty(trim($row[4])) && trim($row[4]) !== '-') ? trim($row[4]) : null;
                        $ownerAddress  = (!empty(trim($row[5])) && trim($row[5]) !== '-') ? trim($row[5]) : null;
                        
                        $minerName     = trim($row[6] ?? '');
                        $minerMac      = (!empty(trim($row[7])) && trim($row[7]) !== 'NaN') ? trim($row[7]) : '-';
                        
                        $shelfNumber   = !empty(trim($row[8])) ? strtoupper(trim((string) $row[8])) : null;
                        $shelfLevel    = (!empty(trim($row[9])) && is_numeric(trim($row[9]))) ? (int) trim($row[9]) : null;
                        $slotNumber    = (!empty(trim($row[10])) && is_numeric(trim($row[10]))) ? (int) trim($row[10]) : null;
                        
                        // Generate MAC otomatis JIKA kosong
                        if (empty($minerMac) || $minerMac === '-') {
                            $minerMac = '54:60:09:' . strtoupper(substr(md5($minerName . $ownerName), 0, 8));
                        }
                        
                        // 1. CARI ATAU BUAT DATA WORKSHOP
                        $workshopId = null;
                        if (!empty($workshopName)) {
                            $workshop = \App\Models\Workshop::firstOrCreate(
                                ['name' => $workshopName]
                            );
                            $workshopId = $workshop->id;
                        }
                        
                        // 2. CARI ATAU BUAT DATA SHELF (Berdasarkan Workshop)
                        $shelfId = null;
                        if (!empty($shelfNumber) && $workshopId) {
                            $shelf = \App\Models\Shelf::firstOrCreate(
                                [
                                    'name' => $shelfNumber,
                                    'workshop_id' => $workshopId
                                ]
                            );
                            $shelfId = $shelf->id;
                        } 
                        
                       // 3. CARI ATAU UPDATE DATA OWNER
                        // Kembalikan ke format awal: Cari hanya berdasarkan nama
                        $owner = \App\Models\Owner::updateOrCreate(
                            [
                                'name' => $ownerName, 
                            ], 
                            [
                                'nik'         => $ownerNik,
                                'referral'    => $ownerReferral,
                                'email'       => $ownerEmail,
                                'phone'       => $ownerPhone,
                                'address'     => $ownerAddress,
                                'workshop_id' => $workshopId // Pindah ke sini agar bisa meng-update data lama
                            ]
                        );

                        // 4. CARI ATAU UPDATE DATA MINER
                        if (!empty($minerName)) {
                            // KUNCI PENCARIAN DIUBAH: Cari berdasarkan Nama Mesin dan Workshop
                            // Agar jika ada update slot/rak, data tertimpa (tidak terduplikat)
                            \App\Models\Miner::updateOrCreate(
                                [
                                    'name' => $minerName, 
                                ],
                                [
                                    'owner_id'     => $owner->id,
                                    'shelf_id'     => $shelfId,    
                                    'workshop_id' => $workshopId,
                                    'shelf_number' => $shelfNumber,
                                    'shelf_level'  => $shelfLevel,
                                    'slot_number'  => $slotNumber,
                                    'mac_address'  => $minerMac, // MAC Address masuk ke update, bukan pencarian
                                ]
                            );
                            $rowcount++;
                        }
                            }
                            fclose($handle);
                        }
                        // SIMPAN KE TABEL IMPORT LOG
                        \App\Models\ImportLog::create([
                            'type' => 'import',
                            'message' => "Berhasil mengimpor {$rowcount} data mesin melalui CSV.",
                            'row_count' => $rowcount,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title("Berhasil! {$rowcount} mesin diimport sesuai urutan CSV baru.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            // Mendaftarkan Relation Manager ke halaman User
            RelationManagers\MinersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwners::route('/'),
            'create' => Pages\CreateOwner::route('/create'),
            'edit' => Pages\EditOwner::route('/{record}/edit'),
        ];
    }

  public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Jika user yang login adalah POS
        if (auth()->user()->role === 'pos') {
            // Langsung filter berdasarkan workshop_id si Owner (Lebih cepat dan aman)
            $query->where('workshop_id', auth()->user()->workshop_id);
        }

        return $query;
    }
    
}