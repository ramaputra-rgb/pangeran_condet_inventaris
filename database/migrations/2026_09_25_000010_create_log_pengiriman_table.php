<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_pengiriman', function (Blueprint $table) {
            $table->id('id_log_pengiriman');
            $table->date('Tanggal');
            $table->foreignId('no_po_produk')->constrained('po_produk', 'no_po_produk')->onDelete('cascade');
            $table->string('bukti_serah_terima')->nullable();
            $table->string('status_pengiriman')->default('DIPROSES');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_pengiriman');
    }
};