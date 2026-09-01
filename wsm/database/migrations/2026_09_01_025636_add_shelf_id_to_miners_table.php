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
            // Menambahkan foreign key ke tabel shelves
            $table->foreignId('shelf_id')->nullable()->constrained('shelves')->nullOnDelete();
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
