<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialPurchase;
use App\Models\RawMaterial;
use Illuminate\Support\Facades\DB;

class MaterialPurchaseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'raw_material_id' => 'required|exists:raw_materials,id',
            'qty_received' => 'required|numeric|min:0.1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Catat Transaksi Pembelian (Tambahkan purchase_date & received_at)
            MaterialPurchase::create([
                'po_number' => 'PO-BB-' . date('Ymd') . '-' . rand(100, 999),
                'supplier_id' => $request->supplier_id,
                'raw_material_id' => $request->raw_material_id,
                'qty_received' => $request->qty_received,
                'unit_price' => $request->unit_price,
                'total_price' => $request->qty_received * $request->unit_price,
                'purchase_date' => now(), // <<-- MENAMBAHKAN FIELD INI
                'received_at' => now(),
            ]);

            // 2. Tambahkan Stok Bahan Baku & Cek Status MSS
            $material = RawMaterial::findOrFail($request->raw_material_id);
            $material->increment('stock_qty', $request->qty_received);

            // Perbarui status MSS jika stok sudah di atas batas aman
            if ($material->stock_qty >= $material->mss_limit) {
                $material->update(['status' => 'STOK_AMAN']);
            }
        });

        return redirect()->back()->with('success', 'Transaksi masuk berhasil dicatat & stok bahan baku bertambah!');
    }
}
