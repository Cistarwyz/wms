<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = ['name', 'nik', 'email', 'phone', 'address'];

    // Relasi: Satu Owner punya Banyak Mesin
    public function miners()
    {
        return $this->hasMany(Miner::class);
    }
}