<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Isp extends Model
{
    protected $fillable = ['name', 'gateway_ip', 'port','is_online', 'last_checked_at'];

    // Relasi: Satu ISP punya banyak catatan metrik
    public function metrics()
    {
        return $this->hasMany(IspMetric::class);
    }
}