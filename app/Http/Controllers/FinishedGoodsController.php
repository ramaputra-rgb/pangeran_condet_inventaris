<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\StockInventory;
use Illuminate\Support\Facades\DB;

class FinishedGoodsController extends Controller
{
    public function index()
    {
        $products = Product::with('inventory')->get();

        $pendingBatches = ProductionBatch::with(['product', 'workOrder'])
            ->where('handover_status', 'PENDING')
            ->latest()
            ->get();

        return view('inventory.finished_goods', compact('products', 'pendingBatches'));
    }

    // Fungsi Baru: Mengunci Stok Bebas Jual (ATP) menjadi Stok Terkunci PO
    public function allocatePo(Request $request, $productId)
    {
        $request->validate([
            'allocated_qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $productId) {
            $inventory = StockInventory::where('product_id', $productId)->firstOrFail();

            // Validasi agar tidak mengalokasikan stok melebihi Bebas Jual (ATP)
            if ($request->allocated_qty > $inventory->atp_stock) {
                return redirect()->back()->with('error', 'Jumlah alokasi melebihi Stok Bebas Jual (ATP) yang tersedia!');
            }

            // Kurangi stok Bebas Jual (ATP) dan Tambah ke Stok Terkunci PO
            $inventory->decrement('atp_stock', $request->allocated_qty);
            $inventory->increment('reserved_stock', $request->allocated_qty);
        });

        return redirect()->back()->with('success', 'Berhasil mengalokasikan stok untuk pesanan PO!');
    }
}