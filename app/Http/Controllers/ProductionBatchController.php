<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionBatch;
use App\Models\WorkOrder;
use App\Models\StockInventory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductionBatchController extends Controller
{
    // 1. Buat Antrean SPK Produksi & Batch Baru dari Dapur
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'actual_net_good_qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // A. Buat Work Order (SPK) Otomatis
            $workOrder = WorkOrder::create([
                'spk_number'   => 'SPK-' . date('Ym') . '-' . rand(100, 999),
                'product_id'   => $request->product_id,
                'target_qty'   => $request->actual_net_good_qty,
                'kitchen_area' => 'Dapur Condet Utama',
                'status'       => 'SELESAI',
            ]);

            // B. Buat Production Batch Lengkap
            ProductionBatch::create([
                'work_order_id'       => $workOrder->id,
                'product_id'          => $request->product_id,
                'batch_code'          => 'B-SPK-' . date('Ymd') . '-' . rand(100, 999),
                'total_dapur_qty'     => $request->actual_net_good_qty,
                'net_good_qty'        => $request->actual_net_good_qty,
                'actual_net_good_qty' => $request->actual_net_good_qty,
                'production_date'     => now(),
                'expired_date'        => Carbon::now()->addMonths(6),
                'handover_status'     => 'PENDING',
            ]);
        });

        return redirect()->back()->with('success', 'Antrean SPK berhasil dibuat! Silakan klik "+ Terima ke Fisik Gudang" untuk memutasi stok.');
    }

    // 2. Terima ke Fisik Gudang (Mutasi Stok Real-Time)
    public function acceptHandover(Request $request, $id)
    {
        DB::transaction(function () use ($id) {
            $batch = ProductionBatch::findOrFail($id);

            if ($batch->handover_status === 'COMPLETED') {
                return;
            }

            $addedQty = $batch->actual_net_good_qty ?? ($batch->net_good_qty ?? $batch->total_dapur_qty);

            // Tambah Stok Fisik dan ATP di Gudang FG
            $inventory = StockInventory::firstOrCreate(
                ['product_id' => $batch->product_id],
                ['total_physical_stock' => 0, 'reserved_stock' => 0, 'atp_stock' => 0, 'status_gudang' => 'STOK_AMAN']
            );

            $inventory->increment('total_physical_stock', $addedQty);
            $inventory->increment('atp_stock', $addedQty);

            // Update Status Batch
            $batch->update([
                'handover_status' => 'COMPLETED',
                'received_at'     => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Stok hasil produksi berhasil masuk ke Fisik Gudang!');
    }
}