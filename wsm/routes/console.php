<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('monitor:network')
    ->everyTenSeconds()
    ->withoutOverlapping(2); // Angka 2 berarti gembok akan otomatis hancur dalam 2 menit jika terjadi error/macet