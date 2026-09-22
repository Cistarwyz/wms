<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shelf;

class Miner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mac_address',
        'ip_address',
        'owner_name',    
        'slot_number',
        'shelf_number',     
        'shelf_level', // (Contoh input form: 1 untuk wilis)
        'workshop_id', // <--- TAMBAHKAN BARIS INI
        'owner_id',
        'shelf_id',    // Wajib ada di sini
        'is_online',
        'last_seen_at'
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Miner $miner) {
            
            if (!empty($miner->shelf_level) && !empty($miner->shelf_number)) {
                
                // Cari data rak berdasarkan ID workshop (dari shelf_level) dan nama rak
                // Asumsi: kolom nomor/nama rak di tabel shelves bernama 'name'
                $matchedShelf = Shelf::where('workshop_id', $miner->shelf_level)
                                     ->where('name', $miner->shelf_number)
                                     ->first();

                if ($matchedShelf) {
                    $miner->shelf_id = $matchedShelf->id;
                } else {
                    $miner->shelf_id = null;
                }
            }
        });
    }
}