<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use App\Models\SuratJalan;
use App\Models\DetailAlokasiPo;
use App\Models\LogProduksi;
use Illuminate\Support\Facades\DB;

class PoProdukController extends Controller
{
    public function index()
    {
        $poList = PoProduk::with(['detailAlokasi.produkJadi', 'suratJalan', 'logProduksi'])
                    ->withSum('logProduksi as total_digoreng', 'jml_grade_A')
                    ->latest()
                    ->get();

        $products   = ProdukJadi::all();
        $suratJalan = SuratJalan::all();

        $totalPo         = $poList->count();
        $poPendingCount  = $poList->where('status_po', 'PENDING')->count();
        $poProsesCount   = $poList->where('status_po', 'PROSES')->count();
        $poSelesaiCount  = $poList->where('status_po', 'SELESAI')->count();

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
            'nama_penerima'      => 'required|string|max:255',
            'tipe_jual'          => 'required|string',
            'jumlah_po'          => 'required|integer|min:1',
            'tgl_po'             => 'required|date',
            'tgl_jatuh_tempo_po' => 'required|date',
            'alamat_pelanggan'   => 'required|string',
            'id_produk_jadi'     => 'required',
            'no_ref'             => 'nullable',
        ]);

        DB::transaction(function () use ($request) {
            $po = PoProduk::create([
                'nama_pelanggan'     => $request->nama_pelanggan,
                'nama_penerima'      => $request->nama_penerima,
                'tipe_jual'          => $request->tipe_jual,
                'jumlah_po'          => $request->jumlah_po,
                'tgl_po'             => $request->tgl_po,
                'tgl_jatuh_tempo_po' => $request->tgl_jatuh_tempo_po,
                'status_po'          => 'PENDING',
                'alamat_pelanggan'   => $request->alamat_pelanggan,
                'no_ref'             => $request->no_ref ?: null,
            ]);

            DetailAlokasiPo::create([
                'id_produk_jadi' => $request->id_produk_jadi,
                'no_po_produk'   => $po->no_po_produk,
                'jml_alokasi'    => $request->jumlah_po,
            ]);
        });

        return redirect()->route('po-produk.index')
                         ->with('success', 'Pesanan PO Pelanggan berhasil dicatat!');
    }

    public function potongStokReady($id)
    {
        DB::transaction(function () use ($id) {
            $po = PoProduk::with('detailAlokasi.produkJadi')->findOrFail($id);
            $alokasi = $po->detailAlokasi->first();

            if ($alokasi && $alokasi->produkJadi) {
                $produk = $alokasi->produkJadi;
                if ($produk->stok >= $po->jumlah_po) {
                    $produk->decrement('stok', $po->jumlah_po);
                    $po->update(['status_po' => 'PROSES']);
                }
            }
        });

        return redirect()->route('po-produk.index')
                         ->with('success', 'Stok ready berhasil dipotong untuk PO ini!');
    }

    public function buatProdukBatch(Request $request, $id)
    {
        DB::transaction(function () use ($id, $request) {
            $po = PoProduk::findOrFail($id);
            $po->update(['status_po' => 'PROSES']);

            LogProduksi::create([
                'no_po_produk'    => $po->no_po_produk,
                'jml_target'      => $request->qty_produksi ?? $po->jumlah_po,
                'jml_grade_A'     => 0,
                'jml_grade_B'     => 0,
                'status_approval' => 'PENDING',
                'tgl_produksi'    => now(),
            ]);
        });

        return redirect()->route('po-produk.index')
                         ->with('success', 'Perintah batch produksi baru dikirim ke Dapur (Menunggu Approval Admin)!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_po' => 'required|in:PENDING,PROSES,DIKIRIM,SELESAI',
        ]);

        $po = PoProduk::findOrFail($id);
        $po->update(['status_po' => $request->status_po]);

        return redirect()->route('po-produk.index')
                         ->with('success', 'Status PO berhasil diperbarui!');
    }
}