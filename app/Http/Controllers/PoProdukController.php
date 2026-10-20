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
        // Mengambil data PO beserta detail alokasi produk dan riwayat log penggorengan dapur
        $poList = PoProduk::with(['detailAlokasi.produkJadi', 'suratJalan', 'logProduksi.detailProduksi'])
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
            'tgl_po'             => 'required|date',
            'tgl_jatuh_tempo_po' => 'required|date',
            'alamat_pelanggan'   => 'required|string',
            'produk_ids'         => 'required|array|min:1',
            'produk_qty'         => 'required|array|min:1',
            'no_ref'             => 'nullable',
        ]);

        DB::transaction(function () use ($request) {
            $totalQtyPo = array_sum($request->produk_qty);

            $po = PoProduk::create([
                'nama_pelanggan'     => $request->nama_pelanggan,
                'nama_penerima'      => $request->nama_penerima,
                'tipe_jual'          => $request->tipe_jual,
                'jumlah_po'          => $totalQtyPo,
                'tgl_po'             => $request->tgl_po,
                'tgl_jatuh_tempo_po' => $request->tgl_jatuh_tempo_po,
                'status_po'          => 'PENDING',
                'alamat_pelanggan'   => $request->alamat_pelanggan,
                'no_ref'             => $request->no_ref ?: null,
            ]);

            foreach ($request->produk_ids as $index => $idProduk) {
                $qty = $request->produk_qty[$index] ?? 0;
                if ($idProduk && $qty > 0) {
                    DetailAlokasiPo::create([
                        'id_produk_jadi' => $idProduk,
                        'no_po_produk'   => $po->no_po_produk,
                        'jml_alokasi'    => $qty,
                    ]);
                }
            }
        });

        return redirect()->route('po-produk.index')
                         ->with('success', 'Pesanan PO Pelanggan (Multi-Item) berhasil dicatat!');
    }

    public function potongStokReady($id)
    {
        DB::transaction(function () use ($id) {
            $po = PoProduk::with('detailAlokasi.produkJadi')->findOrFail($id);
            
            // Hanya potong jika statusnya belum STOK_DIPOTONG atau SELESAI
            if (!in_array($po->status_po, ['STOK_DIPOTONG', 'SELESAI', 'DIKIRIM'])) {
                foreach ($po->detailAlokasi as $alokasi) {
                    if ($alokasi->produkJadi) {
                        $alokasi->produkJadi->decrement('stok', $alokasi->jml_alokasi);
                    }
                }
                
                // Set status khusus sebagai penanda stok sudah terpotong
                $po->update(['status_po' => 'STOK_DIPOTONG']);
            }
        });

        return redirect()->route('po-produk.index')
                        ->with('success', 'Stok ready berhasil dipotong! Silakan terbitkan Surat Jalan.');
    }

    public function buatProdukBatch(Request $request, $id)
    {
        return redirect()->route('log-produksi.index', ['no_po' => $id])
                         ->with('info', 'Silakan input detail batch penggorengan dapur untuk PO-00' . $id);
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