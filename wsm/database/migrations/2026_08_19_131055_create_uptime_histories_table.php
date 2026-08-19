<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('uptime_histories', function (Blueprint $table) {
        $table->id();
        $table->string('periode'); // Contoh: "Agustus 2026"
        $table->decimal('uptime_percent', 5, 2); // Simpan persentase, contoh: 99.85
        $table->bigInteger('total_online')->default(0);
        $table->bigInteger('total_offline')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uptime_histories');
    }
};
