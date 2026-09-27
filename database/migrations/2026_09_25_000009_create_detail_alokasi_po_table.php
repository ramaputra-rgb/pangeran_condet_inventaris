<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_alokasi_po', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_produk_jadi')->constrained('produk_jadi', 'id_produk_jadi')->onDelete('cascade');
            $table->foreignId('no_po_produk')->constrained('po_produk', 'no_po_produk')->onDelete('cascade');
            $table->integer('jml_alokasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_alokasi_po');
    }
};