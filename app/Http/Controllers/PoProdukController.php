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

    /**
     * INPUT DATA PO PELANGGAN (Disesuaikan dengan Activity Diagram Hal. 6 & 8)
     * Includes: Pengecekan Duplikasi & Otomatisasi Kartu Batch Produksi (Node B)
     */
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
            'buat_batch_langsung'=> 'nullable|boolean',
        ]);

        // 1. Cek Duplikasi PO Berdasarkan ['nama_pelanggan', 'tgl_po'] (Activity Diagram Hal. 6)
        $existingPo = PoProduk::where('nama_pelanggan', trim($request->nama_pelanggan))
            ->where('tgl_po', $request->tgl_po)
            ->first();

        if ($existingPo) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! PO untuk pelanggan ini pada tanggal tersebut sudah pernah dibuat.');
        }

        DB::transaction(function () use ($request) {
            $totalQtyPo = array_sum($request->produk_qty);

            // Simpan Data PO Pelanggan Baru
            $po = PoProduk::create([
                'nama_pelanggan'     => trim($request->nama_pelanggan),
                'nama_penerima'      => trim($request->nama_penerima),
                'tipe_jual'          => $request->tipe_jual,
                'jumlah_po'          => $totalQtyPo,
                'tgl_po'             => $request->tgl_po,
                'tgl_jatuh_tempo_po' => $request->tgl_jatuh_tempo_po,
                'status_po'          => 'PENDING',
                'alamat_pelanggan'   => $request->alamat_pelanggan,
                'no_ref'             => $request->no_ref ?: null,
            ]);

            // Simpan Detail Alokasi Item PO
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

            // 2. OTOMATISASI NODE B: Buat Kartu Batch Baru jika tombol "Buat Batch Produksi" ditekan (Activity Diagram Hal. 6 & 8)
            if ($request->filled('buat_batch_langsung') || $request->input('action') === 'buat_batch') {
                LogProduksi::create([
                    'no_po_produk' => $po->no_po_produk,
                    'tgl_produksi' => now(),
                    'jml_grade_A'  => 0,
                    'jml_grade_B'  => 0,
                    'ket'          => 'Batch otomatis dipicu dari PO Pelanggan #' . $po->no_po_produk,
                ]);

                // Update Status PO menjadi PROSES (Dalam Proses Produksi)
                $po->update(['status_po' => 'PROSES']);
            }
        });

        return redirect()->route('po-produk.index')
                         ->with('success', 'Data Berhasil Disimpan! PO Pelanggan dicatat dan Kartu Batch Produksi otomatis diterbitkan.');
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

    /**
     * TRIGGER BATCH PRODUKSI MANUAL (Node B)
     */
    public function buatProdukBatch(Request $request, $id)
    {
        $po = PoProduk::findOrFail($id);

        DB::transaction(function () use ($po) {
            // Cek apakah batch sudah ada untuk PO ini
            $existingLog = LogProduksi::where('no_po_produk', $po->no_po_produk)->first();
            
            if (!$existingLog) {
                LogProduksi::create([
                    'no_po_produk' => $po->no_po_produk,
                    'tgl_produksi' => now(),
                    'jml_grade_A'  => 0,
                    'jml_grade_B'  => 0,
                    'ket'          => 'Batch dipicu untuk PO #' . $po->no_po_produk,
                ]);
            }

            $po->update(['status_po' => 'PROSES']);
        });

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Kartu Batch Produksi berhasil dibuat! Status PO berganti menjadi PROSES.');
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