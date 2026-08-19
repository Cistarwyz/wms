<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Miner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mac_address',
        'ip_address',
        'owner_name',    
        'slot_number',
        'owner_id',
        'is_online',
        'last_seen_at'
        ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}