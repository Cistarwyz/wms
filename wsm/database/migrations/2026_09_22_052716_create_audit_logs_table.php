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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Siapa yang ubah
            $table->string('action'); // created, updated, deleted
            $table->string('model_type'); // Model apa yang diubah (e.g., Miner)
            $table->unsignedBigInteger('model_id'); // ID datanya
            $table->json('old_values')->nullable(); // Data sebelum diubah
            $table->json('new_values')->nullable(); // Data setelah diubah
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
