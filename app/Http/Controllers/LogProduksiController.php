<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogProduksi;
use App\Models\DetailProduksi;
use App\Models\BahanProduksi;
use App\Models\ProdukJadi;
use App\Models\PoProduk;
use Illuminate\Support\Facades\DB;

class LogProduksiController extends Controller
{
    public function index()
    {
        $logs      = LogProduksi::with(['poProduk', 'bahanProduksi', 'detailProduksi.produkJadi'])->latest()->get();
        $poActive  = PoProduk::whereIn('status_po', ['PENDING', 'PROSES'])->get();
        $bahanList = BahanProduksi::all();
        $products  = ProdukJadi::all();

        return view('production.log_produksi', compact('logs', 'poActive', 'bahanList', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_po_produk'      => 'required',
            'tgl_produksi'      => 'required|date',
            'rentang_tgl_exp'   => 'required|date',
            'waktu_produksi'    => 'required',
            'jml_grade_A'       => 'required|integer|min:0',
            'jml_grade_B'       => 'required|integer|min:0',
            'id_produk_grade_A' => 'required',
            'id_produk_grade_B' => 'nullable',
            'bahan_ids'         => 'required|array',
            'bahan_qty'         => 'required|array',
            'ket'               => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Simpan Header Log Produksi
            $log = LogProduksi::create([
                'no_po_produk'    => $request->no_po_produk,
                'tgl_produksi'    => $request->tgl_produksi,
                'rentang_tgl_exp' => $request->rentang_tgl_exp,
                'waktu_produksi'  => $request->waktu_produksi,
                'jml_grade_A'     => $request->jml_grade_A,
                'jml_grade_B'     => $request->jml_grade_B,
                'ket'             => $request->ket,
            ]);

            // 2. Pemotongan Stok Bahan Baku Dapur
            foreach ($request->bahan_ids as $index => $idBahan) {
                $qtyPakai = $request->bahan_qty[$index] ?? 0;
                if ($idBahan && $qtyPakai > 0) {
                    $log->bahanProduksi()->attach($idBahan, ['qty_dipakai' => $qtyPakai]);
                    BahanProduksi::where('id_bahan', $idBahan)->decrement('qty_stok', $qtyPakai);
                }
            }

            // 3. Penambahan Hasil Hasil QC Grade A ke Stok Fisik FG
            if ($request->jml_grade_A > 0) {
                DetailProduksi::create([
                    'id_produk_jadi'  => $request->id_produk_grade_A,
                    'no_batch'        => $log->no_batch,
                    'jml_produk_jadi' => $request->jml_grade_A,
                ]);

                ProdukJadi::where('id_produk_jadi', $request->id_produk_grade_A)
                    ->increment('stok', $request->jml_grade_A);
            }

            // 4. Penambahan Hasil QC Grade B (Remahan)
            if ($request->jml_grade_B > 0 && $request->id_produk_grade_B) {
                DetailProduksi::create([
                    'id_produk_jadi'  => $request->id_produk_grade_B,
                    'no_batch'        => $log->no_batch,
                    'jml_produk_jadi' => $request->jml_grade_B,
                ]);

                ProdukJadi::where('id_produk_jadi', $request->id_produk_grade_B)
                    ->increment('stok', $request->jml_grade_B);
            }

            // OTOMATISASI 1: Ubah Status PO Acuan Menjadi "PROSES"
            PoProduk::where('no_po_produk', $request->no_po_produk)
                ->update(['status_po' => 'PROSES']);
        });

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Batch Log Produksi disimpan! Status PO otomatis diperbarui ke PROSES.');
    }
}