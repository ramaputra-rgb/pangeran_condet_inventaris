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
        $poActive       = PoProduk::whereIn('status_po', ['PENDING', 'PROSES', 'DIKIRIM'])->get();

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
            'tgl_terbit'    => 'required|date',
            'nama_penerima' => 'required|string|max:255',
            'nama_kurir'    => 'required|string|max:255',
            'ket'           => 'nullable|string',
            'no_po_produk'  => 'nullable',
        ]);

        DB::transaction(function () use ($request) {
            $sj = SuratJalan::create([
                'tgl_terbit'    => $request->tgl_terbit,
                'nama_penerima' => $request->nama_penerima,
                'nama_kurir'    => $request->nama_kurir,
                'ket'           => $request->ket,
            ]);

            if ($request->no_po_produk) {
                // OTOMATISASI 2: Saat Surat Jalan Terbit, Status PO berubah ke "DIKIRIM"
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
                         ->with('success', 'Surat Jalan diterbitkan! Status PO otomatis berubah ke DIKIRIM.');
    }

    public function updateShipmentStatus(Request $request, $id)
    {
        $request->validate([
            'status_pengiriman' => 'required|in:DIPROSES,DIKIRIM,SELESAI',
            'bukti_serah_terima'=> 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $log = LogPengiriman::findOrFail($id);
            $oldStatus = $log->status_pengiriman;

            $log->update([
                'status_pengiriman'  => $request->status_pengiriman,
                'bukti_serah_terima' => $request->bukti_serah_terima ?? $log->bukti_serah_terima,
            ]);

            // OTOMATISASI 3: Jika Status Pengiriman berubah ke SELESAI
            if ($request->status_pengiriman === 'SELESAI' && $oldStatus !== 'SELESAI') {
                // 1. Ubah Status PO ke SELESAI
                PoProduk::where('no_po_produk', $log->no_po_produk)
                    ->update(['status_po' => 'SELESAI']);

                // 2. Ambil data alokasi PO ini untuk memotong stok fisik gudang permanen
                $alokasiList = DetailAlokasiPo::where('no_po_produk', $log->no_po_produk)->get();

                foreach ($alokasiList as $alokasi) {
                    // Potong stok fisik produk jadi
                    ProdukJadi::where('id_produk_jadi', $alokasi->id_produk_jadi)
                        ->decrement('stok', $alokasi->jml_alokasi);
                }

                // 3. Hapus kuncian alokasi stok PO karena pesanan telah sukses terkirim
                DetailAlokasiPo::where('no_po_produk', $log->no_po_produk)->delete();
            }
        });

        return redirect()->route('shipments.index')
                         ->with('success', 'Status pengiriman diperbarui! Jika SELESAI, stok fisik otomatis dipotong & alokasi dilepas.');
    }
}