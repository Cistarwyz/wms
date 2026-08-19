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
    Schema::create('isps', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Contoh: "ISP Biznet (Utama)"
        $table->string('gateway_ip'); // Contoh IP Modem: "192.168.10.1" atau IP Publik target "8.8.8.8"
        $table->boolean('is_online')->default(true); // Status saat ini untuk pager
        $table->timestamp('last_checked_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isps');
    }
};
