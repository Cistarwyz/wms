<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Pastikan model User dipanggil

class DeviceController extends Controller
{
    public function registerToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // Karena kita bypass login Sanctum, kita langsung tembak ke User ID 1 (Admin/NOC)
        $user = User::find(1); 
        
        if ($user) {
            $user->update([
                'fcm_token' => $request->fcm_token
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Device token berhasil didaftarkan.'
        ]);
    }
}