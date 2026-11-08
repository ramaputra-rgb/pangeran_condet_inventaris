<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogCetakLaporan;
use App\Models\TransaksiBeliBahan;
use App\Models\LogProduksi;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Menampilkan Halaman / Frame Menu Laporan
     */
    public function index()
    {
        // Ambil riwayat log cetak laporan untuk audit trail
        $logCetakList = LogCetakLaporan::latest()->get();

        return view('reports.index', compact('logCetakList'));
    }

    /**
     * Filter & Proses Cetak Laporan (Sesuai Activity Diagram Hal. 11)
     */
    public function previewOrPrint(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|string', // e.g., 'stok', 'pengadaan', 'produksi', 'penjualan'
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date',
        ]);

        $jenis = $request->jenis_laporan;
        $mulai = $request->periode_mulai;
        $selesai = $request->periode_selesai;

        $dataLaporan = [];

        // Mengambil data berdasarkan sub-laporan yang dipilih
        if ($jenis === 'stok') {
            $dataLaporan = ProdukJadi::all();
        } elseif ($jenis === 'pengadaan') {
            $query = TransaksiBeliBahan::with('bahanProduksi');
            if ($mulai && $selesai) {
                $query->whereBetween('created_at', [$mulai . ' 00:00:00', $selesai . ' 23:59:59']);
            }
            $dataLaporan = $query->get();
        } elseif ($jenis === 'produksi') {
            $query = LogProduksi::with(['poProduk', 'detailProduksi.produkJadi']);
            if ($mulai && $selesai) {
                $query->whereBetween('tgl_produksi', [$mulai, $selesai]);
            }
            $dataLaporan = $query->get();
        } elseif ($jenis === 'penjualan') {
            $query = PoProduk::with('detailAlokasi.produkJadi');
            if ($mulai && $selesai) {
                $query->whereBetween('tgl_po', [$mulai, $selesai]);
            }
            $dataLaporan = $query->get();
        }

        DB::transaction(function () use ($jenis, $mulai, $selesai) {
            // Menyimpan data ke Database (Log Cetak Laporan - Activity Diagram Hal. 11)
            LogCetakLaporan::create([
                'jenis_laporan' => $jenis,
                'kriteria_filter' => 'Periode: ' . ($mulai ?? 'Semua') . ' s/d ' . ($selesai ?? 'Semua'),
                'tgl_cetak' => now(),
                'status_cetak' => 'BERHASIL',
            ]);
        });

        // Mengembalikan tampilan pratinjau cetak laporan
        return view('reports.preview', compact('jenis', 'dataLaporan', 'mulai', 'selesai'))
                 ->with('success', 'Laporan Berhasil Dicetak & Log Tercatat!');
    }
}