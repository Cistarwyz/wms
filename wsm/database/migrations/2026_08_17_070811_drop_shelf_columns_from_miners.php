<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miners', function (Blueprint $table) {
            // Menghapus dua kolom yang sudah tidak dipakai
            $table->dropColumn(['shelf_number', 'shelf_level']);
        });
    }

    public function down(): void
    {
        Schema::table('miners', function (Blueprint $table) {
            // Ini untuk berjaga-jaga jika sewaktu-waktu kita ingin mengembalikannya (rollback)
            $table->string('shelf_number')->nullable();
            $table->string('shelf_level')->nullable();
        });
    }
};