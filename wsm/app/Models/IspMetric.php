<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IspMetric extends Model
{
    protected $fillable = [
        'isp_id',
        'ping_ms',
        'is_online',
        'download_mbps', // <--- Wajib didaftarkan
        'upload_mbps',   // <--- Wajib didaftarkan
    ];
    
    public function isp()
    {
        return $this->belongsTo(Isp::class);
    }
}