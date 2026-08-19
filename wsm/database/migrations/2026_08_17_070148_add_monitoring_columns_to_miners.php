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
    Schema::table('miners', function (Blueprint $table) {
        $table->boolean('is_online')->default(false)->after('ip_address');
        $table->timestamp('last_seen_at')->nullable()->after('is_online');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('miners', function (Blueprint $table) {
            //
        });
    }
};
