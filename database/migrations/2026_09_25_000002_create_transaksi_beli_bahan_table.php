<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_beli_bahan', function (Blueprint $table) {
            $table->id('no_transaksi_beli');
            $table->string('nama_supplier');
            $table->decimal('total_qty', 12, 2);
            $table->decimal('total_harga', 15, 2);
            $table->decimal('total_ongkir', 15, 2)->nullable()->default(0);
            $table->foreignId('id_bahan')->constrained('bahan_produksi', 'id_bahan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_beli_bahan');
    }
};