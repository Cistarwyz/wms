<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    protected $guarded = [];

    // Satu Rak memiliki banyak Mesin (Miner)
    public function miners()
    {
        return $this->hasMany(Miner::class);
    }
}