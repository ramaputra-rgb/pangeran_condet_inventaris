<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan_produksi', function (Blueprint $table) {
            $table->id('id_bahan');
            $table->string('nama_bahan');
            $table->enum('tipe', ['kering', 'basah']);
            $table->string('ukuran'); // gram/kg/ml
            $table->decimal('qty_stok', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_produksi');
    }
};