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
        Schema::create('miners', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('mac_address')->unique();
        $table->string('ip_address')->nullable();
        $table->string('shelf_number')->nullable();     
        $table->integer('shelf_level')->nullable();
        $table->integer('slot_number')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miners');
    }
};
