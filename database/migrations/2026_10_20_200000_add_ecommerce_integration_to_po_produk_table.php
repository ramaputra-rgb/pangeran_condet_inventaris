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
        Schema::table('po_produk', function (Blueprint $table) {
            if (!Schema::hasColumn('po_produk', 'nama_penerima')) {
                $table->string('nama_penerima')->nullable()->after('nama_pelanggan');
            }
            if (!Schema::hasColumn('po_produk', 'nomor_pesanan_ecommerce')) {
                $table->string('nomor_pesanan_ecommerce', 100)->nullable()->index()->after('no_ref');
            }
            if (!Schema::hasColumn('po_produk', 'sumber_transaksi')) {
                $table->string('sumber_transaksi', 50)->default('OFFLINE')->after('nomor_pesanan_ecommerce');
            }
            if (!Schema::hasColumn('po_produk', 'catatan')) {
                $table->text('catatan')->nullable()->after('sumber_transaksi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('po_produk', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('po_produk', 'nama_penerima')) {
                $columnsToDrop[] = 'nama_penerima';
            }
            if (Schema::hasColumn('po_produk', 'nomor_pesanan_ecommerce')) {
                $columnsToDrop[] = 'nomor_pesanan_ecommerce';
            }
            if (Schema::hasColumn('po_produk', 'sumber_transaksi')) {
                $columnsToDrop[] = 'sumber_transaksi';
            }
            if (Schema::hasColumn('po_produk', 'catatan')) {
                $columnsToDrop[] = 'catatan';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
