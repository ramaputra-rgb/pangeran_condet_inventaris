<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_produksi', function (Blueprint $table) {
            $table->id('no_batch');
            $table->date('tgl_produksi');
            $table->date('rentang_tgl_exp');
            $table->time('waktu_produksi');
            $table->integer('jml_grade_A')->default(0);
            $table->integer('jml_grade_B')->default(0);
            $table->foreignId('no_po_produk')->constrained('po_produk', 'no_po_produk')->onDelete('cascade');
            $table->text('ket')->nullable(); // Ditambahkan untuk catatan kendala dapur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_produksi');
    }
};