<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Models\TransaksiBeliBahan;
use Illuminate\Support\Facades\DB;

class MaterialPurchaseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_supplier'         => 'required|integer',
            'id_bahan_baku'       => 'required|integer',
            'kuantitas'           => 'required|numeric|min:0.01',
            'harga_perbahan_baku' => 'required|numeric|min:0',
            'biaya_kirim'         => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Simpan Transaksi Pembelian Bahan Baku
            TransaksiBeliBahan::create([
                'id_supplier'         => $request->id_supplier,
                'id_bahan_baku'       => $request->id_bahan_baku,
                'kuantitas'           => $request->kuantitas,
                'harga_perbahan_baku' => $request->harga_perbahan_baku,
                'biaya_kirim'         => $request->biaya_kirim ?? 0,
                'tanggal_transaksi'   => now(),
            ]);

            // 2. Otomatis Tambah Stok Satuan di Tabel Bahan Baku
            $bahan = BahanBaku::findOrFail($request->id_bahan_baku);
            $bahan->increment('stok_satuan', $request->kuantitas);
        });

        return redirect()->back()->with('success', 'Transaksi pembelian berhasil disimpan dan stok bahan baku diperbarui!');
    }
}