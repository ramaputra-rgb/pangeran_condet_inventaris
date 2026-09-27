<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukJadi;
use App\Models\BatchProduksi;
use Illuminate\Support\Str;

class ProductionBatchController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'product_id'          => 'required|exists:produk_jadi,id_produk',
            'actual_net_good_qty' => 'required|integer|min:1',
            'id_spk'              => 'nullable|exists:work_order_spk,id_spk',
        ]);

        // 2. Buat Batch Produksi Baru
        BatchProduksi::create([
            'id_spk'              => $request->input('id_spk'), // Bernilai NULL jika tidak dikirim dari form
            'id_produk'           => $request->product_id,
            'kode_batch'          => 'BATCH-' . strtoupper(Str::random(6)),
            'jumlah_goreng_pcs'   => $request->actual_net_good_qty, // Disamakan dengan Qty Pass QC
            'jumlah_bagus_qc_pcs' => $request->actual_net_good_qty,
            'status_serah_terima' => 'PENDING',
            'tanggal_produksi'    => now(),
            'tanggal_expired'     => now()->addMonths(6),
        ]);

        return redirect()->route('finished-goods.index')
                         ->with('success', 'Antrean SPK Dapur berhasil dibuat! Menunggu verifikasi masuk gudang.');
    }

    public function accept($id)
    {
        // 3. Terima Batch ke Fisik Gudang & Tambah Stok
        $batch = BatchProduksi::findOrFail($id);

        if ($batch->status_serah_terima === 'COMPLETED') {
            return redirect()->back()->with('error', 'Batch ini sudah diterima di gudang sebelumnya.');
        }

        $batch->update([
            'status_serah_terima'  => 'COMPLETED',
            'diterima_gudang_pada' => now(),
        ]);

        // Otomatis Tambah Stok Fisik & Stok ATP Produk Jadi
        $product = ProdukJadi::findOrFail($batch->id_produk);
        $product->increment('stok_fisik', $batch->jumlah_bagus_qc_pcs);
        $product->increment('stok_bebas_jual_atp', $batch->jumlah_bagus_qc_pcs);

        return redirect()->route('finished-goods.index')
                         ->with('success', "Batch {$batch->kode_batch} berhasil diterima! Stok fisik {$product->nama_produk} bertambah {$batch->jumlah_bagus_qc_pcs} Pcs.");
    }
}