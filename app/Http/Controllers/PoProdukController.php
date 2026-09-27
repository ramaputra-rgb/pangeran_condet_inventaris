<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use App\Models\SuratJalan;
use App\Models\DetailAlokasiPo;
use Illuminate\Support\Facades\DB;

class PoProdukController extends Controller
{
    public function index()
    {
        // Ambil Data PO Produk beserta Relasinya
        $poList = PoProduk::with(['suratJalan', 'detailAlokasi.produkJadi'])
                    ->latest()
                    ->get();

        $products   = ProdukJadi::all();
        $suratJalan = SuratJalan::all();

        // Indikator Top-Bar Ringkasan
        $totalPo        = $poList->count();
        $poPendingCount = $poList->where('status_po', 'PENDING')->count();
        $poProsesCount  = $poList->where('status_po', 'PROSES')->count();
        $poSelesaiCount = $poList->where('status_po', 'SELESAI')->count();

        return view('sales.po_produk', compact(
            'poList',
            'products',
            'suratJalan',
            'totalPo',
            'poPendingCount',
            'poProsesCount',
            'poSelesaiCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan'     => 'required|string|max:255',
            'tipe_jual'          => 'required|string',
            'jumlah_po'          => 'required|integer|min:1',
            'tgl_jatuh_tempo_po' => 'required|date',
            'alamat_pelanggan'   => 'required|string',
            'id_produk_jadi'     => 'required',
            'no_ref'             => 'nullable',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Simpan Header PO Produk
            $po = PoProduk::create([
                'nama_pelanggan'     => $request->nama_pelanggan,
                'tipe_jual'          => $request->tipe_jual,
                'jumlah_po'          => $request->jumlah_po,
                'tgl_jatuh_tempo_po' => $request->tgl_jatuh_tempo_po,
                'status_po'          => 'PENDING',
                'alamat_pelanggan'   => $request->alamat_pelanggan,
                'no_ref'             => $request->no_ref,
            ]);

            // 2. Simpan Alokasi Stok PO
            DetailAlokasiPo::create([
                'id_produk_jadi' => $request->id_produk_jadi,
                'no_po_produk'   => $po->no_po_produk,
                'jml_alokasi'    => $request->jumlah_po,
            ]);
        });

        return redirect()->route('po-produk.index')
                         ->with('success', 'Pesanan PO Pelanggan berhasil dicatat dan alokasi produk telah dikunci!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_po' => 'required|in:PENDING,PROSES,DIKIRIM,SELESAI',
        ]);

        $po = PoProduk::findOrFail($id);
        $po->update(['status_po' => $request->status_po]);

        return redirect()->route('po-produk.index')
                         ->with('success', 'Status PO Pelanggan berhasil diperbarui!');
    }
}