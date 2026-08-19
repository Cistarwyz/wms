<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('app_settings', function (Blueprint $table) {
        $table->id();
        $table->boolean('use_chunking')->default(true); // Aktifkan sistem partial
        $table->integer('chunk_size')->default(50);     // Jumlah per rombongan
        $table->integer('chunk_sleep')->default(5);     // Jeda per rombongan (detik)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
