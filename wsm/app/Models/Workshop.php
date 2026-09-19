<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    // Tambahkan baris ini agar field bisa disimpan
    protected $fillable = [
        'name',
        'description',
    ];

    // Relasi ke Shelf
    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }
}