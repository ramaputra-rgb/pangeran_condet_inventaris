<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrderSpk;
use App\Models\ProdukJadi;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    public function index()
    {
        // Ambil Data SPK beserta Relasi Produk & Detail Formula BoM
        $workOrders = WorkOrderSpk::with(['produk.formulaBom.detailFormula.bahanBaku'])
                        ->latest()
                        ->get();

        $products     = ProdukJadi::where('status_produk', 'Aktif')->get();
        $rawMaterials = BahanBaku::all();

        // Ringkasan Top-Bar Modul Work Order
        $spkProsesCount   = WorkOrderSpk::where('status_spk', 'PROSES')->count();
        $spkSelesaiCount  = WorkOrderSpk::where('status_spk', 'SELESAI')->count();
        $totalTargetBatch = WorkOrderSpk::where('status_spk', 'PROSES')->sum('target_produksi_pcs');

        return view('production.work_orders', compact(
            'workOrders',
            'products',
            'rawMaterials',
            'spkProsesCount',
            'spkSelesaiCount',
            'totalTargetBatch'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_produk'           => 'required',
            'target_produksi_pcs' => 'required|numeric|min:1',
            'tanggal_spk'         => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            // Generasi Nomor SPK Otomatis
            $noSpk = 'SPK-' . date('Ymd') . '-' . rand(100, 999);

            // 1. Simpan Header SPK Dapur
            $spk = WorkOrderSpk::create([
                'no_spk'              => $noSpk,
                'id_produk'           => $request->id_produk,
                'target_produksi_pcs' => $request->target_produksi_pcs,
                'tanggal_spk'         => $request->tanggal_spk,
                'status_spk'          => 'PROSES',
            ]);

            // 2. Pemotongan Stok Bahan Baku Otomatis Berdasarkan Formula BoM
            $produk = ProdukJadi::with('formulaBom.detailFormula')->find($request->id_produk);
            
            if ($produk && $produk->formulaBom) {
                $formula      = $produk->formulaBom;
                $porsiBatch   = $formula->porsi_batch_pcs > 0 ? $formula->porsi_batch_pcs : 100;
                $pengaliBatch = $request->target_produksi_pcs / $porsiBatch;

                foreach ($formula->detailFormula as $detail) {
                    if ($detail->jenis_komponen === 'BAHAN_BAKU' && $detail->id_bahan_baku) {
                        $jumlahDibutuhkan = $detail->jumlah_dibutuhkan * $pengaliBatch;
                        
                        // Perbaikan: Potong stok menggunakan primary key 'id_bahan'
                        BahanBaku::where('id_bahan', $detail->id_bahan_baku)
                            ->decrement('stok_satuan', $jumlahDibutuhkan);
                    }
                }
            }
        });

        return redirect()->route('work-orders.index')
                         ->with('success', 'SPK Dapur berhasil diterbitkan dan stok bahan baku otomatis terpotong sesuai acuan BoM!');
    }
}