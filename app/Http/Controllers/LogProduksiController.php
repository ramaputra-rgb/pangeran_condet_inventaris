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
                    ->where('nama_pelanggan', '!=', 'STOK INTERNAL GUDANG')
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
                if (strtoupper($log->status_approval) === 'APPROVED') {
                    foreach ($log->detailProduksi as $det) {
                        $idProd = $det->id_produk_jadi;
                        $hasilGorengPerProduk[$idProd] = ($hasilGorengPerProduk[$idProd] ?? 0) + $det->jml_produk_jadi;
                    }
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
                'no_po_produk'   => $po->no_po_produk,
                'nama_pelanggan' => $po->nama_pelanggan,
                'nama_penerima'  => $po->nama_penerima ?? $po->nama_pelanggan,
                'tipe_jual'      => $po->tipe_jual,
                'status_po'      => $po->status_po,
                'jumlah_po'      => (int) $po->jumlah_po,
                'items'          => $items,
            ];
        }

        // Mapping data batch untuk modal Selesai Produksi & Input QC
        $batchQcMap = [];
        foreach ($logProduksi as $log) {
            $detailItems = [];
            foreach ($log->detailProduksi as $det) {
                $detailItems[] = [
                    'detail_id'      => $det->id,
                    'id_produk_jadi' => $det->id_produk_jadi,
                    'nama_produk'    => $det->produkJadi->nama_produk ?? ('Produk #' . $det->id_produk_jadi),
                    'target_qty'     => (int) $det->jml_produk_jadi,
                ];
            }

            $isInternal = !$log->no_po_produk || ($log->poProduk && $log->poProduk->nama_pelanggan === 'STOK INTERNAL GUDANG');

            $batchQcMap[$log->no_batch] = [
                'no_batch'     => $log->no_batch,
                'kode_batch'   => 'BATCH-00' . $log->no_batch,
                'tgl_produksi' => \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y'),
                'sumber'       => $isInternal
                    ? 'Stok Internal Gudang (Tanpa Acuan PO)'
                    : 'PO-00' . $log->no_po_produk . ' — ' . ($log->poProduk->nama_pelanggan ?? '-'),
                'ket'          => $log->ket ?? '',
                'items'        => $detailItems,
            ];
        }

        return view('production.log_produksi', compact(
            'logProduksi',
            'poList',
            'products',
            'selectedPoNo',
            'poDataMap',
            'batchQcMap'
        ));
    }

    /**
     * TAHAP 1: INPUT REQUEST / JADWAL BATCH PRODUKSI DAPUR (BELUM MENAMBAH STOK & GRADE)
     * Mendukung 2 Mode: 'po' (Acuan PO Pelanggan) & 'stok_gudang' (Tanpa Acuan PO)
     */
    public function store(Request $request)
    {
        $mode = $request->input('mode_produksi', 'stok_gudang');

        $rules = [
            'mode_produksi'   => 'required|in:po,stok_gudang',
            'tgl_produksi'    => 'required|date',
            'waktu_produksi'  => 'nullable|string',
            'rentang_tgl_exp' => 'nullable|date',
            'produk_ids'      => 'required|array|min:1',
            'produk_qty'      => 'required|array|min:1',
            'ket'             => 'nullable|string',
        ];

        if ($mode === 'po') {
            $rules['no_po_produk'] = 'required|exists:po_produk,no_po_produk';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $mode) {
            $noPoProduk = null;

            if ($mode === 'po') {
                $noPoProduk = $request->no_po_produk;
            } else {
                // Cek apakah kolom no_po_produk pada tabel log_produksi nullable atau tidak
                $colInfo = DB::select("SHOW COLUMNS FROM log_produksi LIKE 'no_po_produk'");
                $isNullable = !empty($colInfo) && strtoupper($colInfo[0]->Null ?? '') === 'YES';

                if (!$isNullable) {
                    $internalPo = PoProduk::firstOrCreate(
                        ['nama_pelanggan' => 'STOK INTERNAL GUDANG'],
                        [
                            'nama_penerima'      => 'Gudang Produk Jadi Condet',
                            'tipe_jual'          => 'Stok Opname',
                            'jumlah_po'          => 0,
                            'tgl_po'             => now()->toDateString(),
                            'tgl_jatuh_tempo_po' => now()->addYear()->toDateString(),
                            'status_po'          => 'SELESAI',
                            'alamat_pelanggan'   => 'Dapur Produksi Condet',
                        ]
                    );
                    $noPoProduk = $internalPo->no_po_produk;
                }
            }

            $defaultKet = $mode === 'po'
                ? 'Batch penggorengan dapur untuk PO-00' . $noPoProduk
                : 'Batch produksi mandiri untuk stok gudang (Non-PO)';

            // 1. Simpan Header Batch dalam status PENDING (Sedang Proses Produksi Dapur)
            $log = LogProduksi::create([
                'no_po_produk'    => $noPoProduk,
                'tgl_produksi'    => $request->tgl_produksi,
                'waktu_produksi'  => $request->waktu_produksi ?? 'Shift Pagi',
                'rentang_tgl_exp' => $request->rentang_tgl_exp ?? date('Y-m-d', strtotime('+3 months')),
                'jml_grade_A'     => 0,
                'jml_grade_B'     => 0,
                'status_approval' => 'PENDING',
                'ket'             => $request->ket ?: $defaultKet,
            ]);

            // 2. Simpan Rencana / Target Item yang Digoreng (Belum menambah stok sampai QC selesai)
            foreach ($request->produk_ids as $index => $idProduk) {
                $qty = (int) ($request->produk_qty[$index] ?? 0);

                if ($idProduk && $qty > 0) {
                    DetailProduksi::create([
                        'no_batch'        => $log->no_batch,
                        'id_produk_jadi'  => $idProduk,
                        'jml_produk_jadi' => $qty,
                    ]);
                }
            }

            // 3. Jika berasal dari PO, pastikan status PO menjadi PROSES (Sedang Digoreng)
            if ($mode === 'po' && $noPoProduk) {
                $po = PoProduk::find($noPoProduk);
                if ($po && $po->status_po === 'PENDING') {
                    $po->update(['status_po' => 'PROSES']);
                }
            }
        });

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Batch Produksi dijadwalkan! Silakan klik "Selesai & Input QC" setelah proses penggorengan selesai.');
    }

    /**
     * TAHAP 2: UPDATE STATUS SELESAI PRODUKSI & INPUT HASIL QC (GRADE A & GRADE B)
     * Barulah pada tahap ini stok produk jadi bertambah sesuai hasil QC riil.
     */
    public function completeQc(Request $request, $id)
    {
        $request->validate([
            'grade_a_qty'   => 'required|array|min:1',
            'grade_a_qty.*' => 'required|integer|min:0',
            'jml_grade_B'   => 'nullable|integer|min:0',
            'ket'           => 'nullable|string',
        ]);

        $log = LogProduksi::with(['detailProduksi', 'poProduk'])->findOrFail($id);

        if (strtoupper($log->status_approval) === 'APPROVED') {
            return redirect()->back()->with('error', 'Batch ini sudah selesai dan terverifikasi QC.');
        }

        DB::transaction(function () use ($request, $log) {
            $totalGradeA = 0;
            $gradeB = (int) ($request->jml_grade_B ?? 0);

            foreach ($log->detailProduksi as $detail) {
                $qtyLolosQc = (int) ($request->grade_a_qty[$detail->id] ?? 0);

                // Perbarui jumlah aktual lolos QC pada detail batch
                $detail->update([
                    'jml_produk_jadi' => $qtyLolosQc,
                ]);

                // Tambahkan stok riil Grade A ke Gudang Produk Jadi
                if ($qtyLolosQc > 0) {
                    $produk = ProdukJadi::find($detail->id_produk_jadi);
                    if ($produk) {
                        $produk->increment('stok', $qtyLolosQc);
                    }
                }

                $totalGradeA += $qtyLolosQc;
            }

            // Alokasi stok Remahan (Grade B) jika ada
            if ($gradeB > 0) {
                $produkRemahan = ProdukJadi::where('nama_produk', 'LIKE', '%Remahan%')->first();
                if ($produkRemahan) {
                    $produkRemahan->increment('stok', $gradeB);
                } else {
                    ProdukJadi::create([
                        'nama_produk' => 'Rengginang Remahan (Grade B)',
                        'varian'      => 'Mix',
                        'netto'       => '500g',
                        'harga_jual'  => 15000,
                        'stok'        => $gradeB,
                    ]);
                }
            }

            $log->update([
                'jml_grade_A'     => $totalGradeA,
                'jml_grade_B'     => $gradeB,
                'status_approval' => 'APPROVED',
                'ket'             => $request->ket ?: $log->ket,
            ]);
        });

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Produksi Selesai & Hasil QC Tersimpan! Stok Grade A dan Grade B telah ditambahkan ke gudang.');
    }

    public function approveBatch($id)
    {
        $log = LogProduksi::findOrFail($id);
        $log->update(['status_approval' => 'APPROVED']);

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Batch BATCH-00' . $id . ' berhasil di-approve!');
    }

    /**
     * HAPUS BATCH YANG MASIH PENDING (JIKA SALAH INPUT)
     */
    public function destroy($id)
    {
        $log = LogProduksi::findOrFail($id);

        if (strtoupper($log->status_approval) === 'APPROVED') {
            return redirect()->back()->with('error', 'Batch yang sudah Selesai & Lolos QC tidak dapat dihapus karena stok sudah masuk ke gudang.');
        }

        $noPoProduk = $log->no_po_produk;

        DB::transaction(function () use ($log, $noPoProduk) {
            $log->detailProduksi()->delete();
            $log->delete();

            if ($noPoProduk) {
                $po = PoProduk::with('logProduksi')->find($noPoProduk);
                if ($po && $po->status_po === 'PROSES' && $po->logProduksi->isEmpty()) {
                    $po->update(['status_po' => 'PENDING']);
                }
            }
        });

        return redirect()->route('log-produksi.index')
                         ->with('success', 'Batch BATCH-00' . $id . ' berhasil dibatalkan/dihapus.');
    }
}