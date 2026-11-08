<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanProduksi;
use App\Models\TransaksiBeliBahan;
use Illuminate\Support\Facades\DB;

class RawMaterialsController extends Controller
{
    public function index(Request $request)
    {
        $materials = BahanProduksi::all();

        // 1. Hitung Statistik Ringkasan (Top-Bar)
        $totalBahanCount = $materials->count();
        $stokKritisCount = $materials->where('qty_stok', '<=', 5)->count();
        $totalPengadaanBulanIni = TransaksiBeliBahan::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->sum('total_harga');

        // 2. Query Riwayat Transaksi Beli Bahan
        $queryTx = TransaksiBeliBahan::with('bahanProduksi')->latest();

        if ($request->filled('periode')) {
            if ($request->periode === 'minggu_ini') {
                $queryTx->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($request->periode === 'bulan_ini') {
                $queryTx->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        }

        $rawTransactions = $queryTx->get();
        
        // Kelompokkan berdasarkan supplier & tanggal dibuat (created_at)
        $groupedTransactions = $rawTransactions->groupBy(function($item) {
            return $item->nama_supplier . '_' . \Carbon\Carbon::parse($item->created_at)->format('Y-m-d_H:i');
        });

        return view('inventory.raw_materials', compact(
            'materials', 
            'groupedTransactions',
            'totalBahanCount',
            'stokKritisCount',
            'totalPengadaanBulanIni'
        ));
    }

    /**
     * 1. INPUT MASTER BAHAN PRODUKSI BARU (Disesuaikan dengan Activity Diagram Hal. 4)
     * Pengecekan Duplikasi berdasarkan ['nama_bahan', 'ukuran']
     */
    public function storeMaterial(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'tipe'       => 'required|string',
            'ukuran'     => 'required|string',
            'qty_stok'   => 'required|numeric|min:0',
        ]);

        // Cek duplikasi berdasarkan Nama & Satuan Ukuran (Activity Diagram Hal. 4)
        $existing = BahanProduksi::where('nama_bahan', trim($request->nama_bahan))
            ->where('ukuran', trim($request->ukuran))
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! Bahan baku dengan nama dan satuan ukuran tersebut sudah ada di sistem.');
        }

        BahanProduksi::create([
            'nama_bahan' => trim($request->nama_bahan),
            'tipe'       => $request->tipe,
            'ukuran'     => trim($request->ukuran),
            'qty_stok'   => $request->qty_stok,
        ]);

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Data Berhasil Disimpan! Master bahan baku baru telah terdaftar.');
    }

    /**
     * EDIT MASTER BAHAN PRODUKSI
     */
    public function updateMaterial(Request $request, $id)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'tipe'       => 'required|string',
            'ukuran'     => 'required|string',
            'qty_stok'   => 'required|numeric|min:0',
        ]);

        $bahan = BahanProduksi::findOrFail($id);

        // Cek jika nama & ukuran diubah ke item yang sudah ada milik ID lain
        $existing = BahanProduksi::where('nama_bahan', trim($request->nama_bahan))
            ->where('ukuran', trim($request->ukuran))
            ->where('id_bahan', '!=', $id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! Kombinasi nama dan ukuran bahan baku telah digunakan item lain.');
        }

        $bahan->update([
            'nama_bahan' => trim($request->nama_bahan),
            'tipe'       => $request->tipe,
            'ukuran'     => trim($request->ukuran),
            'qty_stok'   => $request->qty_stok,
        ]);

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Master bahan baku berhasil diperbarui!');
    }

    /**
     * HAPUS MASTER BAHAN PRODUKSI
     */
    public function destroyMaterial($id)
    {
        $bahan = BahanProduksi::findOrFail($id);
        $bahan->delete();

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Master bahan baku berhasil dihapus!');
    }

    /**
     * 2. CATAT NOTA PEMBELIAN BAHAN (Disesuaikan dengan Activity Diagram Hal. 2)
     * Verifikasi Pengecekan Nota Duplikat & Penambahan Stok Otomatis
     */
    public function storePurchase(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'total_ongkir'  => 'nullable|numeric|min:0',
            'bahan_ids'     => 'required|array|min:1',
            'qty'           => 'required|array|min:1',
            'harga_total'   => 'required|array|min:1',
        ]);

        // Cek duplikasi nota berdasarkan supplier & waktu transaksi dalam kurun 1 menit terakhir (Activity Diagram Hal. 2)
        $recentDuplicate = TransaksiBeliBahan::where('nama_supplier', trim($request->nama_supplier))
            ->where('created_at', '>=', now()->subMinute())
            ->first();

        if ($recentDuplicate) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! Nota pembelian dari supplier ini baru saja diinput.');
        }

        DB::transaction(function () use ($request) {
            $ongkirPerItem = ($request->total_ongkir ?? 0) / count($request->bahan_ids);

            foreach ($request->bahan_ids as $idx => $idBahan) {
                $q = $request->qty[$idx] ?? 0;
                $h = $request->harga_total[$idx] ?? 0;

                // Simpan Nota Pembelian
                TransaksiBeliBahan::create([
                    'nama_supplier' => trim($request->nama_supplier),
                    'total_qty'     => $q,
                    'total_harga'   => $h + $ongkirPerItem,
                    'total_ongkir'  => $ongkirPerItem,
                    'id_bahan'      => $idBahan,
                ]);

                // Menambah Stok Bahan Produksi (Activity Diagram Hal. 2)
                BahanProduksi::where('id_bahan', $idBahan)->increment('qty_stok', $q);
            }
        });

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Data Berhasil Disimpan! Nota pembelian dicatat dan stok bahan baku bertambah.');
    }

    /**
     * 3. HAPUS / PENYESUAIAN NOTA PEMBELIAN (Disesuaikan dengan Activity Diagram Hal. 3 [Axi A])
     * Mengurangi/Revert Stok Bahan Baku saat Transaksi Dibatalkan
     */
    public function destroyPurchaseGroup(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->ids as $id) {
                $tx = TransaksiBeliBahan::find($id);
                if ($tx) {
                    // Revert/Mengurangi Stok sesuai transaksi yang dihapus
                    if ($tx->id_bahan && $tx->total_qty > 0) {
                        $bahan = BahanProduksi::find($tx->id_bahan);
                        if ($bahan) {
                            // Mencegah stok menjadi minus jika sudah dipakai
                            $newStok = max(0, $bahan->qty_stok - $tx->total_qty);
                            $bahan->update(['qty_stok' => $newStok]);
                        }
                    }
                    $tx->delete();
                }
            }
        });

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Hapus Data Berhasil! Nota pembelian dihapus dan penyesuaian stok bahan telah dilakukan.');
    }
}