<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukJadi;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    public function index()
    {
        // Ambil produk beserta relasi formula BoM dan detail kinerjanya
        $products = ProdukJadi::with(['formulaBom.detailFormula.bahanBaku'])
                        ->where('status_produk', 'Aktif')
                        ->get();

        $rawMaterials = BahanBaku::all();

        // Top Card Calculations
        $totalFormula   = DB::table('formula_bom')->count();
        $totalBahanBaku = $rawMaterials->count();

        return view('production.bom_recipes', compact(
            'products',
            'rawMaterials',
            'totalFormula',
            'totalBahanBaku'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_produk'       => 'required',
            'nama_formula'    => 'required|string|max:255',
            'porsi_batch_pcs' => 'required|numeric|min:1',
            'keterangan'      => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Simpan atau Update Header Formula BoM
            DB::table('formula_bom')->updateOrInsert(
                ['id_produk' => $request->id_produk],
                [
                    'nama_formula'    => $request->nama_formula,
                    'porsi_batch_pcs' => $request->porsi_batch_pcs,
                    'keterangan'      => $request->keterangan,
                    'updated_at'      => now(),
                    'created_at'      => now(),
                ]
            );
        });

        return redirect()->route('bom-recipes.index')
                         ->with('success', 'Formula Resep BoM berhasil disimpan!');
    }
}