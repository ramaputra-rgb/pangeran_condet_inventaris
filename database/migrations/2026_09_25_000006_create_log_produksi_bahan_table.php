<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_produksi_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('no_batch')->constrained('log_produksi', 'no_batch')->onDelete('cascade');
            $table->foreignId('id_bahan')->constrained('bahan_produksi', 'id_bahan')->onDelete('cascade');
            $table->decimal('qty_dipakai', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_produksi_bahan');
    }
};