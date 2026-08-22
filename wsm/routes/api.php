<?php

use Illuminate\Support\Facades\Route; // <--- Tambahkan baris ini
use App\Http\Controllers\Api\DeviceController;

// Pastikan request API ini terlindungi oleh middleware autentikasi
// Hapus middleware('auth:sanctum') untuk sementara
Route::post('/register-device', [DeviceController::class, 'registerToken']);