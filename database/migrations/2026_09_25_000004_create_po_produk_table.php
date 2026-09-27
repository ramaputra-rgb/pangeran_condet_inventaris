<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_produk', function (Blueprint $table) {
            $table->id('no_po_produk');
            $table->string('nama_pelanggan');
            $table->string('tipe_jual'); // Grosir/Eceran/Toko
            $table->integer('jumlah_po');
            $table->date('tgl_jatuh_tempo_po');
            $table->string('status_po')->default('PENDING'); // PENDING, PROSES, DIKIRIM, SELESAI
            $table->text('alamat_pelanggan');
            $table->foreignId('no_ref')->nullable()->constrained('surat_jalan', 'no_ref')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_produk');
    }
};