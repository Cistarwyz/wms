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
    Schema::create('isp_metrics', function (Blueprint $table) {
        $table->id();
        $table->foreignId('isp_id')->constrained('isps')->cascadeOnDelete();
        $table->integer('ping_ms')->nullable(); 
        $table->float('download_mbps')->nullable(); 
        $table->float('upload_mbps')->nullable();
        $table->boolean('is_online')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_metrics');
    }
};
