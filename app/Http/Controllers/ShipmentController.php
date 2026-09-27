<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratJalan;
use App\Models\LogPengiriman;
use App\Models\PoProduk;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        // Ambil Data Surat Jalan & Log Pengiriman
        $suratJalanList = SuratJalan::with('poProduk')->latest()->get();
        $shipments      = LogPengiriman::with('poProduk')->latest()->get();
        $poActive       = PoProduk::whereIn('status_po', ['PENDING', 'PROSES', 'DIKIRIM'])->get();

        // Indikator Top-Bar Ringkasan
        $totalSj         = $suratJalanList->count();
        $pengirimanProses = $shipments->where('status_pengiriman', 'DIPROSES')->count();
        $pengirimanSelesai= $shipments->where('status_pengiriman', 'SELESAI')->count();

        return view('logistics.shipments', compact(
            'suratJalanList',
            'shipments',
            'poActive',
            'totalSj',
            'pengirimanProses',
            'pengirimanSelesai'
        ));
    }

    public function storeSuratJalan(Request $request)
    {
        $request->validate([
            'tgl_terbit'    => 'required|date',
            'nama_penerima' => 'required|string|max:255',
            'nama_kurir'    => 'required|string|max:255',
            'ket'           => 'nullable|string',
            'no_po_produk'  => 'nullable',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Simpan Surat Jalan
            $sj = SuratJalan::create([
                'tgl_terbit'    => $request->tgl_terbit,
                'nama_penerima' => $request->nama_penerima,
                'nama_kurir'    => $request->nama_kurir,
                'ket'           => $request->ket,
            ]);

            // 2. Jika ditautkan ke PO, update PO & buat Log Pengiriman
            if ($request->no_po_produk) {
                PoProduk::where('no_po_produk', $request->no_po_produk)
                    ->update([
                        'no_ref'    => $sj->no_ref,
                        'status_po' => 'DIKIRIM',
                    ]);

                LogPengiriman::create([
                    'Tanggal'           => $request->tgl_terbit,
                    'no_po_produk'      => $request->no_po_produk,
                    'status_pengiriman' => 'DIPROSES',
                ]);
            }
        });

        return redirect()->route('shipments.index')
                         ->with('success', 'Surat Jalan berhasil diterbitkan dan log pengiriman telah diaktifkan!');
    }

    public function updateShipmentStatus(Request $request, $id)
    {
        $request->validate([
            'status_pengiriman' => 'required|in:DIPROSES,DIKIRIM,SELESAI',
            'bukti_serah_terima'=> 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $log = LogPengiriman::findOrFail($id);
            $log->update([
                'status_pengiriman'  => $request->status_pengiriman,
                'bukti_serah_terima' => $request->bukti_serah_terima ?? $log->bukti_serah_terima,
            ]);

            // Jika status pengiriman SELESAI, otomatis selesaikan PO
            if ($request->status_pengiriman === 'SELESAI') {
                PoProduk::where('no_po_produk', $log->no_po_produk)
                    ->update(['status_po' => 'SELESAI']);
            }
        });

        return redirect()->route('shipments.index')
                         ->with('success', 'Status log pengiriman berhasil diperbarui!');
    }
}