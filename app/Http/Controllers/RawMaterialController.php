<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanProduksi;
use App\Models\TransaksiBeliBahan;
use Illuminate\Support\Facades\DB;

class RawMaterialController extends Controller
{
    public function index()
    {
        $rawMaterials = BahanProduksi::all();
        $purchases    = TransaksiBeliBahan::with('bahanProduksi')->latest()->get();

        $pengeluaranBulanIni = TransaksiBeliBahan::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->sum('total_harga');

        $totalBahanDiterima = TransaksiBeliBahan::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->sum('total_qty');

        $stokKritisCount = BahanProduksi::where('qty_stok', '<=', 50)->count();
        $stokKritisItems = BahanProduksi::where('qty_stok', '<=', 50)->pluck('nama_bahan')->implode(' & ');

        return view('inventory.raw_materials', compact(
            'rawMaterials',
            'purchases',
            'pengeluaranBulanIni',
            'totalBahanDiterima',
            'stokKritisCount',
            'stokKritisItems'
        ));
    }

    public function storePurchase(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'id_bahan'      => 'required',
            'total_qty'     => 'required|numeric|min:0.01',
            'total_harga'   => 'required|numeric|min:0',
            'total_ongkir'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            TransaksiBeliBahan::create([
                'nama_supplier' => $request->nama_supplier,
                'id_bahan'      => $request->id_bahan,
                'total_qty'     => $request->total_qty,
                'total_harga'   => $request->total_harga,
                'total_ongkir'  => $request->input('total_ongkir', 0),
            ]);

            $bahan = BahanProduksi::findOrFail($request->id_bahan);
            $bahan->increment('qty_stok', $request->total_qty);
        });

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Transaksi pembelian berhasil disimpan dan stok bahan produksi bertambah!');
    }

    public function storeMaterial(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'tipe'       => 'required|in:kering,basah',
            'ukuran'     => 'required|string|max:50',
            'qty_stok'   => 'required|numeric|min:0',
        ]);

        BahanProduksi::create([
            'nama_bahan' => $request->nama_bahan,
            'tipe'       => $request->tipe,
            'ukuran'     => $request->ukuran,
            'qty_stok'   => $request->qty_stok,
        ]);

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Bahan produksi baru berhasil ditambahkan!');
    }
}