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
                Forms\Components\Section::make('Informasi Pribadi User')
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
                    ])->columns(2) // Membagi form jadi 2 kolom agar rapi
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
                            // 1. Sesuaikan Helper Text agar format CSV-nya jelas
                            ->helperText('Format kolom: owner_name, owner_nik, owner_email, owner_phone, owner_address, miner_name, miner_mac, shelf_number, shelf_level, slot_number'),
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
                            $header = fgetcsv($handle, 1000, ","); // Lewati header

                            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                $ownerName    = $row[0] ?? null;
                                if (!$ownerName || $ownerName === 'owner_name') continue; 

                                $ownerNik     = (!empty($row[1]) && $row[1] !== '-') ? $row[1] : null;
                                // Catatan: Di kode Anda sebelumnya, referral dan nik sama-sama mengambil $row[1]. 
                                // Jika di CSV ada kolom terpisah, sesuaikan angkanya (misal $row[2]).
                                $ownerReferral = (!empty($row[1]) && $row[1] !== '-') ? $row[1] : null; 
                                $ownerEmail   = (!empty($row[2]) && $row[2] !== '-') ? $row[2] : null;
                                $ownerPhone   = (!empty($row[3]) && $row[3] !== '-') ? $row[3] : null;
                                $ownerAddress = (!empty($row[4]) && $row[4] !== '-') ? $row[4] : null;
                                
                                $minerName    = $row[5] ?? null;
                                $minerMac     = (!empty($row[6]) && $row[6] !== 'NaN') ? $row[6] : '-';
                                
                                // Generate MAC otomatis jika kosong di CSV
                                if (empty($minerMac) || $minerMac === '-') {
                                    $minerMac = '54:60:09:' . strtoupper(substr(md5($minerName . $ownerName), 0, 8));
                                }

                                // 2. TANGKAP DATA RAK, LEVEL, DAN URUTAN SECARA MANUAL (Tanpa rumus)
                                $shelfNumber = isset($row[7]) ? strtoupper(trim((string) $row[7])) : null;
                                $shelfLevel  = isset($row[8]) ? (int) $row[8] : null;
                                $slotNumber  = isset($row[9]) ? (int) $row[9] : null;

                                // 1. Cari atau Buat Owner
                                $owner = Owner::updateOrCreate(
                                    ['name' => $ownerName],
                                    [
                                        'nik' => $ownerNik,
                                        'referral' => $ownerReferral, // Saya tambahkan ini agar referral ikut tersimpan
                                        'email' => $ownerEmail,
                                        'phone' => $ownerPhone,
                                        'address' => $ownerAddress,
                                    ]
                                );

                                // 2. Buat atau Update Mesin
                                if ($minerName) {
                                    \App\Models\Miner::updateOrCreate(
                                        ['mac_address' => $minerMac],
                                        [
                                            'owner_id' => $owner->id,
                                            'name' => $minerName,
                                            // 3. SIMPAN KE-3 DATA POSISI KE DATABASE
                                            'shelf_number' => $shelfNumber,
                                            'shelf_level' => $shelfLevel,
                                            'slot_number' => $slotNumber,
                                            'is_online' => false,
                                        ]
                                    );
                                    $rowcount++;
                                }
                            }
                            fclose($handle);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("Berhasil! Sebanyak {$rowcount} mesin berhasil diimport.")
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
}