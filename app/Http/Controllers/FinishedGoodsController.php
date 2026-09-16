<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionBatch;

class FinishedGoodsController extends Controller
{
    public function index()
    {
        // 1. Ambil seluruh Katalog Produk beserta data Inventarisnya
        $products = Product::with('inventory')->get();

        // 2. Ambil Antrean Batch dari Dapur yang status handover-nya masih PENDING
        $pendingBatches = ProductionBatch::with(['product', 'workOrder'])
            ->where('handover_status', 'PENDING')
            ->latest()
            ->get();

        // 3. Kirim data ke tampilan Blade
        return view('inventory.finished_goods', compact('products', 'pendingBatches'));
    }
}