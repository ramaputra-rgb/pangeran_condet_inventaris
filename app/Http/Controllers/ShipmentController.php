<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratJalan;
use App\Models\LogPengiriman;
use App\Models\PoProduk;
use App\Models\DetailAlokasiPo;
use App\Models\ProdukJadi;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $suratJalanList = SuratJalan::with('poProduk')->latest()->get();
        $shipments      = LogPengiriman::with('poProduk')->latest()->get();
        $poActive       = PoProduk::whereIn('status_po', ['PENDING', 'PROSES', 'STOK_DIPOTONG', 'DIKIRIM'])->get();

        $totalSj          = $suratJalanList->count();
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
            'tgl_terbit'    => 'nullable|date',
            'nama_penerima' => 'required|string|max:255',
            'nama_kurir'    => 'nullable|string|max:255',
            'ket'           => 'nullable|string',
            'no_po_produk'  => 'nullable',
        ]);

        DB::transaction(function () use ($request) {
            $tglTerbit = $request->tgl_terbit ?? now()->format('Y-m-d');
            $kurir     = $request->nama_kurir ?? 'Kurir Internal Condet';

            $sj = SuratJalan::create([
                'tgl_terbit'    => $tglTerbit,
                'nama_penerima' => $request->nama_penerima,
                'nama_kurir'    => $kurir,
                'ket'           => $request->ket ?? 'Diterbitkan otomatis dari PO Pelanggan',
            ]);

            if ($request->no_po_produk) {
                // Saat Surat Jalan Terbit, perbarui reference & ubah status ke DIKIRIM
                PoProduk::where('no_po_produk', $request->no_po_produk)
                    ->update([
                        'no_ref'    => $sj->no_ref,
                        'status_po' => 'DIKIRIM',
                    ]);

                LogPengiriman::create([
                    'Tanggal'           => $tglTerbit,
                    'no_po_produk'      => $request->no_po_produk,
                    'status_pengiriman' => 'DIPROSES',
                ]);
            }
        });

        return redirect()->back()
                         ->with('success', 'Surat Jalan berhasil diterbitkan! Status PO otomatis berubah ke DIKIRIM.');
    }

    public function updateShipmentStatus(Request $request, $id)
    {
        $request->validate([
            'status_pengiriman'  => 'required|in:DIPROSES,DIKIRIM,SELESAI',
            'bukti_serah_terima' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $log = LogPengiriman::findOrFail($id);
            $oldStatus = $log->status_pengiriman;

            $log->update([
                'status_pengiriman'  => $request->status_pengiriman,
                'bukti_serah_terima' => $request->bukti_serah_terima ?? $log->bukti_serah_terima,
            ]);

            // Saat pengiriman selesai, cukup update status_po ke SELESAI
            if ($request->status_pengiriman === 'SELESAI' && $oldStatus !== 'SELESAI') {
                PoProduk::where('no_po_produk', $log->no_po_produk)
                    ->update(['status_po' => 'SELESAI']);
                    
                // CATATAN: DetailAlokasiPo::delete() & decrement stok DIHAPUS 
                // agar tidak terjadi double decrement & detail PO tidak ter-reset!
            }
        });

        return redirect()->route('shipments.index')
                        ->with('success', 'Status pengiriman berhasil diperbarui menjadi SELESAI!');
    }
}