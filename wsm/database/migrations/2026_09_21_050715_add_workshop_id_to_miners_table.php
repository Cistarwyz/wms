<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miners', function (Blueprint $table) {
            // Menambahkan kolom workshop_id setelah kolom owner_id (opsional penempatannya)
            $table->foreignId('workshop_id')->nullable()->constrained('workshops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('miners', function (Blueprint $table) {
            $table->dropForeign(['workshop_id']);
            $table->dropColumn('workshop_id');
        });
    }
};