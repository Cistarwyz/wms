<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['use_chunking', 'chunk_size', 'chunk_sleep'];
}