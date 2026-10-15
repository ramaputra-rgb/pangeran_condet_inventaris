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

        // 1. Hitung Statistik Ringkasan (Top-Bar) menggunakan created_at
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

    public function storeMaterial(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'tipe'       => 'required|string',
            'ukuran'     => 'required|string',
            'qty_stok'   => 'required|numeric|min:0',
        ]);

        BahanProduksi::create([
            'nama_bahan' => $request->nama_bahan,
            'tipe'       => $request->tipe,
            'ukuran'     => $request->ukuran,
            'qty_stok'   => $request->qty_stok,
        ]);

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Bahan baku baru berhasil ditambahkan!');
    }

    public function updateMaterial(Request $request, $id)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'tipe'       => 'required|string',
            'ukuran'     => 'required|string',
            'qty_stok'   => 'required|numeric|min:0',
        ]);

        $bahan = BahanProduksi::findOrFail($id);
        $bahan->update([
            'nama_bahan' => $request->nama_bahan,
            'tipe'       => $request->tipe,
            'ukuran'     => $request->ukuran,
            'qty_stok'   => $request->qty_stok,
        ]);

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Master bahan baku berhasil diperbarui!');
    }

    public function destroyMaterial($id)
    {
        $bahan = BahanProduksi::findOrFail($id);
        $bahan->delete();

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Master bahan baku berhasil dihapus!');
    }

    public function storePurchase(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'total_ongkir'  => 'nullable|numeric|min:0',
            'bahan_ids'     => 'required|array|min:1',
            'qty'           => 'required|array|min:1',
            'harga_total'   => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $ongkirPerItem = ($request->total_ongkir ?? 0) / count($request->bahan_ids);

            foreach ($request->bahan_ids as $idx => $idBahan) {
                $q = $request->qty[$idx] ?? 0;
                $h = $request->harga_total[$idx] ?? 0;

                TransaksiBeliBahan::create([
                    'nama_supplier' => $request->nama_supplier,
                    'total_qty'     => $q,
                    'total_harga'   => $h + $ongkirPerItem,
                    'total_ongkir'  => $ongkirPerItem,
                    'id_bahan'      => $idBahan,
                ]);

                BahanProduksi::where('id_bahan', $idBahan)->increment('qty_stok', $q);
            }
        });

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Transaksi pembelian bahan berhasil dicatat & stok ter-update!');
    }

    public function destroyPurchaseGroup(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->ids as $id) {
                $tx = TransaksiBeliBahan::find($id);
                if ($tx) {
                    if ($tx->id_bahan && $tx->total_qty > 0) {
                        BahanProduksi::where('id_bahan', $tx->id_bahan)->decrement('qty_stok', $tx->total_qty);
                    }
                    $tx->delete();
                }
            }
        });

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Transaksi nota pembelian berhasil dihapus & stok telah disesuaikan!');
    }
}