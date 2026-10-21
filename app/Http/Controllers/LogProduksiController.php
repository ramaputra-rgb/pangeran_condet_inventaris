<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogProduksi;
use App\Models\DetailProduksi;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use Illuminate\Support\Facades\DB;

class LogProduksiController extends Controller
{
    public function index(Request $request)
    {
        $selectedPoNo = $request->get('no_po');

        $logProduksi = LogProduksi::with(['poProduk', 'detailProduksi.produkJadi'])
                        ->latest()
                        ->get();

        $poList = PoProduk::with(['detailAlokasi.produkJadi', 'logProduksi.detailProduksi'])
                    ->where(function ($query) use ($selectedPoNo) {
                        $query->whereNotIn('status_po', ['SELESAI', 'DIKIRIM']);
                        if ($selectedPoNo) {
                            $query->orWhere('no_po_produk', $selectedPoNo);
                        }
                    })
                    ->latest()
                    ->get();

        $products = ProdukJadi::all();

        // Mapping pesanan barang rengginang per PO agar otomatis & konsisten
        $poDataMap = [];
        foreach ($poList as $po) {
            $hasilGorengPerProduk = [];
            foreach ($po->logProduksi as $log) {
                foreach ($log->detailProduksi as $det) {
                    $idProd = $det->id_produk_jadi;
                    $hasilGorengPerProduk[$idProd] = ($hasilGorengPerProduk[$idProd] ?? 0) + $det->jml_produk_jadi;
                }
            }

            $items = [];
            foreach ($po->detailAlokasi as $alokasi) {
                $idProd = $alokasi->id_produk_jadi;
                $targetQty = (int) $alokasi->jml_alokasi;
                $sudahGoreng = (int) ($hasilGorengPerProduk[$idProd] ?? 0);
                $sisaQty = max(0, $targetQty - $sudahGoreng);
                $stokGudang = (int) ($alokasi->produkJadi->stok ?? 0);

                $items[] = [
                    'id_produk_jadi' => $idProd,
                    'nama_produk'    => $alokasi->produkJadi->nama_produk ?? ('Produk #' . $idProd),
                    'target_qty'     => $targetQty,
                    'sudah_goreng'   => $sudahGoreng,
                    'sisa_qty'       => $sisaQty,
                    'stok_gudang'    => $stokGudang,
                    'qty_default'    => $sisaQty > 0 ? $sisaQty : $targetQty,
                ];
            }

            $poDataMap[$po->no_po_produk] = [
                'no_po_produk'    => $po->no_po_produk,
                'nama_pelanggan'  => $po->nama_pelanggan,
                'nama_penerima'   => $po->nama_penerima ?? $po->nama_pelanggan,
                'tipe_jual'       => $po->tipe_jual,
                'status_po'       => $po->status_po,
                'jumlah_po'       => (int) $po->jumlah_po,
                'items'           => $items,
            ];
        }

        return view('production.log_produksi', compact(
            'logProduksi',
            'poList',
            'products',
            'selectedPoNo',
            'poDataMap'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_po_produk'    => 'required|exists:po_produk,no_po_produk',
            'tgl_produksi'    => 'required|date',
            'waktu_produksi'  => 'nullable|string',
            'rentang_tgl_exp' => 'nullable|date',
            'produk_ids'      => 'required|array|min:1',
            'produk_qty'      => 'required|array|min:1',
            'jml_grade_A'     => 'required|numeric|min:0',
            'jml_grade_B'     => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $gradeA = (int) $request->jml_grade_A;
            $gradeB = (int) ($request->jml_grade_B ?? 0);

            // 1. Simpan Header Log Produksi
            $log = LogProduksi::create([
                'no_po_produk'    => $request->no_po_produk,
                'tgl_produksi'    => $request->tgl_produksi,
                'waktu_produksi'  => $request->waktu_produksi ?? 'Shift Pagi',
                'rentang_tgl_exp' => $request->rentang_tgl_exp ?? date('Y-m-d', strtotime('+3 months')),
                'jml_grade_A'     => $gradeA,
                'jml_grade_B'     => $gradeB,
                'status_approval' => 'APPROVED',
                'ket'             => $request->ket ?? 'Batch penggorengan dapur',
            ]);

            // 2. Simpan Multi-Item Varian yang Digoreng ke DetailProduksi
            foreach ($request->produk_ids as $index => $idProduk) {
                $qty = (int) ($request->produk_qty[$index] ?? 0);

                if ($idProduk && $qty > 0) {
                    DetailProduksi::create([
                        'no_batch'        => $log->no_batch,
                        'id_produk_jadi'  => $idProduk,
                        'jml_produk_jadi' => $qty,
                    ]);

                    // UPDATE STOK GRADE A: Tambahkan stok ke produk varian utama yang digoreng
                    $produk = ProdukJadi::find($idProduk);
                    if ($produk) {
                        $produk->increment('stok', $qty);
                    }
                }
            }

            // 3. UPDATE STOK GRADE B (REMAHAN): 
            // Tambahkan hasil remahan ke produk penampung Remahan (misal nama produk mengandung 'Remahan')
            if ($gradeB > 0) {
                $produkRemahan = ProdukJadi::where('nama_produk', 'LIKE', '%Remahan%')->first();
                if ($produkRemahan) {
                    $produkRemahan->increment('stok', $gradeB);
                }
            }

            // 4. Update Status PO ke PROSES jika PENDING
            $po = PoProduk::find($request->no_po_produk);
            if ($po && $po->status_po === 'PENDING') {
                $po->update(['status_po' => 'PROSES']);
            }
        });

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Batch penggorengan berhasil dicatat & stok Grade A dan Grade B (Remahan) berhasil diperbarui!');
    }

    public function approveBatch($id)
    {
        $log = LogProduksi::findOrFail($id);
        $log->update(['status_approval' => 'APPROVED']);

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Batch BATCH-00' . $id . ' berhasil di-approve!');
    }
}