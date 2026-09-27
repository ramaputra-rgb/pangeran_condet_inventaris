<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_jadi', function (Blueprint $table) {
            $table->id('id_produk_jadi');
            $table->string('nama_produk');
            $table->string('varian'); // Tenggiri Gurih, Pedas, Remahan, dll.
            $table->string('netto');
            $table->decimal('harga_jual', 15, 2);
            $table->integer('stok')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_jadi');
    }
};