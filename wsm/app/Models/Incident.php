<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'title',
        'status',
        'affected_miners',
    ];

    // Pastikan JSON otomatis diubah jadi array di PHP
    protected $casts = [
        'affected_miners' => 'array',
    ];
}