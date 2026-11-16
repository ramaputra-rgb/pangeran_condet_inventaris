<?php

use App\Models\BahanProduksi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('bahan_produksi')->orderBy('id_bahan')->get();
        $seen = [];

        foreach ($rows as $row) {
            $normalized = BahanProduksi::normalizeName($row->nama_bahan);
            if ($normalized === '') {
                $normalized = 'bahan-'.$row->id_bahan;
            }

            if (isset($seen[$normalized])) {
                $normalized .= ' ('.$row->id_bahan.')';
            }
            $seen[$normalized] = true;

            if ($row->nama_bahan !== $normalized) {
                DB::table('bahan_produksi')
                    ->where('id_bahan', $row->id_bahan)
                    ->update(['nama_bahan' => $normalized]);
            }
        }

        Schema::table('bahan_produksi', function (Blueprint $table) {
            $table->unique('nama_bahan', 'bahan_produksi_nama_bahan_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bahan_produksi', function (Blueprint $table) {
            $table->dropUnique('bahan_produksi_nama_bahan_unique');
        });
    }
};
