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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Contoh: NOC-1692345678
            $table->string('title'); // Contoh: "15 Mesin Offline Terdeteksi"
            $table->enum('status', ['open', 'acknowledged', 'resolved'])->default('open');
            $table->json('affected_miners')->nullable(); // Menyimpan daftar mesin yang mati
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
