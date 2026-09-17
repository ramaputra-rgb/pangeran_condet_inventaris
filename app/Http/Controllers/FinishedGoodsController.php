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

        // Data Tab 1 & Section Bawah: Antrean PENDING
        $pendingBatches = ProductionBatch::with(['product', 'workOrder'])
            ->where('handover_status', 'PENDING')
            ->latest()
            ->get();

        // Data Tab 2: Riwayat SPK yang COMPLETED
        $completedBatches = ProductionBatch::with(['product', 'workOrder'])
            ->where('handover_status', 'COMPLETED')
            ->latest()
            ->take(10)
            ->get();

        // Data Tab 4: Batch QC & FIFO Watch (Expiring within 180 days)
        $expiringBatches = ProductionBatch::with('product')
            ->whereNotNull('expired_date')
            ->orderBy('expired_date', 'asc')
            ->take(10)
            ->get();

        return view('inventory.finished_goods', compact(
            'products', 
            'pendingBatches', 
            'completedBatches', 
            'expiringBatches'
        ));
    }

    public function allocatePo(Request $request, $productId)
    {
        $request->validate([
            'allocated_qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $productId) {
            $inventory = StockInventory::where('product_id', $productId)->firstOrFail();

            if ($request->allocated_qty > $inventory->atp_stock) {
                return redirect()->back()->with('error', 'Jumlah alokasi melebihi Stok Bebas Jual (ATP) yang tersedia!');
            }

            $inventory->decrement('atp_stock', $request->allocated_qty);
            $inventory->increment('reserved_stock', $request->allocated_qty);
        });

        return redirect()->back()->with('success', 'Berhasil mengalokasikan stok untuk pesanan PO!');
    }
}