<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\MaterialPurchase;

class RawMaterialController extends Controller
{
    public function index()
    {
        $rawMaterials = RawMaterial::all();
        $suppliers = Supplier::all();
        // Mengambil riwayat pembelian terbaru dari database
        $purchases = MaterialPurchase::with(['supplier', 'rawMaterial'])->latest()->get();

        return view('inventory.raw_materials', compact('rawMaterials', 'suppliers', 'purchases'));
    }
}
