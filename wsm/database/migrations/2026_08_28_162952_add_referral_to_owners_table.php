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
        Schema::table('owners', function (Blueprint $table) {
            // Kita pakai pengecekan lagi agar aman
            if (!Schema::hasColumn('owners', 'referral')) {
                // Tipe data string, boleh kosong (nullable)
                $table->string('referral')->nullable()->after('name'); 
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            //
        });
    }
};
