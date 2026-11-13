<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratJalan;
use App\Models\LogPengiriman;
use App\Models\PoProduk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $suratJalanList = SuratJalan::with('poProduk')->latest()->get();
        $shipments      = LogPengiriman::with('poProduk')->latest()->get();
        $poActive       = PoProduk::whereIn('status_po', ['PENDING', 'PROSES', 'STOK_DIPOTONG', 'DIKIRIM'])->get();

        $today = Carbon::today()->toDateString();
        $pengirimanHarian = $shipments->filter(function ($item) use ($today) {
            $tgl = $item->Tanggal
                ? Carbon::parse($item->Tanggal)->toDateString()
                : Carbon::parse($item->created_at)->toDateString();
            return $tgl === $today;
        })->count();

        $pengirimanSelesai = $shipments->where('status_pengiriman', 'SELESAI')->count();

        return view('logistics.shipments', compact(
            'suratJalanList',
            'shipments',
            'poActive',
            'pengirimanHarian',
            'pengirimanSelesai'
        ));
    }

    /**
     * TERBIT SURAT JALAN & CETAK
     */
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
                'nama_penerima' => trim($request->nama_penerima),
                'nama_kurir'    => trim($kurir),
                'ket'           => $request->ket ?? 'Diterbitkan otomatis dari PO Pelanggan',
            ]);

            if ($request->no_po_produk) {
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
                         ->with('success', 'Surat Jalan Berhasil Dicetak! Hardcopy diterbitkan dan status PO otomatis menjadi DIKIRIM.');
    }

    /**
     * KONFIRMASI PENGIRIMAN SELESAI & UPLOAD BUKTI SERAH TERIMA
     */
    public function updateShipmentStatus(Request $request, $id)
    {
        $request->validate([
            'status_pengiriman'  => 'required|in:DIPROSES,DIKIRIM,SELESAI',
            'bukti_serah_terima' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            'bukti_teks'         => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $log = LogPengiriman::findOrFail($id);
            $oldStatus = $log->status_pengiriman;
            $buktiPath = $log->bukti_serah_terima;

            if ($request->hasFile('bukti_serah_terima')) {
                $file = $request->file('bukti_serah_terima');
                $filename = 'bukti_terima_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/bukti_terima'), $filename);
                $buktiPath = 'uploads/bukti_terima/' . $filename;
            } elseif ($request->filled('bukti_teks')) {
                $buktiPath = $request->bukti_teks;
            }

            $log->update([
                'status_pengiriman'  => $request->status_pengiriman,
                'bukti_serah_terima' => $buktiPath,
            ]);

            if ($request->status_pengiriman === 'SELESAI' && $oldStatus !== 'SELESAI') {
                PoProduk::where('no_po_produk', $log->no_po_produk)
                    ->update(['status_po' => 'SELESAI']);
            }
        });

        return redirect()->route('shipments.index')
                         ->with('success', 'Konfirmasi Pengiriman Berhasil! Bukti serah terima tercatat dan status PO menjadi SELESAI.');
    }
}