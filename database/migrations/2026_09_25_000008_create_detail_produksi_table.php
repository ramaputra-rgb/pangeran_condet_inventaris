<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_produksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_produk_jadi')->constrained('produk_jadi', 'id_produk_jadi')->onDelete('cascade');
            $table->foreignId('no_batch')->constrained('log_produksi', 'no_batch')->onDelete('cascade');
            $table->integer('jml_produk_jadi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_produksi');
    }
};