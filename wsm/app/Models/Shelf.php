<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    protected $guarded = [];

    // Satu Rak memiliki banyak Mesin (Miner)
    public function miners()
    {
        return $this->hasMany(Miner::class, 'shelf_id');
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    // Relasi ke mesin (pastikan nama method sesuai dengan model mesin Anda)
    public function machines() 
    {
        return $this->hasMany(Machine::class); 
    }
}