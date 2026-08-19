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
        $table->string('owner_name')->nullable()->after('name');
        $table->string('shelf_number')->nullable()->after('owner_name');
        $table->integer('shelf_level')->nullable()->after('shelf_number');
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
