<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukJadi;
use App\Models\DetailProduksi;
use App\Models\DetailAlokasiPo;
use Illuminate\Support\Facades\DB;

class FinishedGoodsController extends Controller
{
    public function index()
    {
        // Ambil Data Produk Jadi beserta Relasinya
        $products = ProdukJadi::with(['detailProduksi.logProduksi', 'detailAlokasiPo.poProduk'])->get();

        // Indikator Top-Bar Ringkasan
        $totalStokFisik   = $products->sum('stok');
        $totalVarian      = $products->count();
        $totalNilaiAset   = $products->sum(function ($p) {
            return $p->stok * $p->harga_jual;
        });

        // Hitung total alokasi PO yang terikat
        $totalAlokasiPo   = DetailAlokasiPo::sum('jml_alokasi');

        return view('inventory.finished_goods', compact(
            'products',
            'totalStokFisik',
            'totalVarian',
            'totalNilaiAset',
            'totalAlokasiPo'
        ));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'varian'      => 'required|string|max:255',
            'netto'       => 'required|string|max:50',
            'harga_jual'  => 'required|numeric|min:0',
            'stok'        => 'required|integer|min:0',
        ]);

        ProdukJadi::create([
            'nama_produk' => $request->nama_produk,
            'varian'      => $request->varian,
            'netto'       => $request->netto,
            'harga_jual'  => $request->harga_jual,
            'stok'        => $request->stok,
        ]);

        return redirect()->route('finished-goods.index')
                         ->with('success', 'Master Produk Jadi baru berhasil ditambahkan!');
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stok_tambahan' => 'required|integer',
        ]);

        $product = ProdukJadi::findOrFail($id);
        $product->increment('stok', $request->stok_tambahan);

        return redirect()->route('finished-goods.index')
                         ->with('success', 'Penyesuaian stok produk jadi berhasil diperbarui!');
    }
}