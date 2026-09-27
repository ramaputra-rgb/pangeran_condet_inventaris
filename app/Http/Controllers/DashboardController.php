<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanProduksi;
use App\Models\LogProduksi;
use App\Models\ProdukJadi;
use App\Models\PoProduk;
use App\Models\LogPengiriman;
use App\Models\TransaksiBeliBahan;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Metrics Pengadaan & Bahan
        $stokKritisCount = BahanProduksi::where('qty_stok', '<=', 10)->count();
        $totalPengeluaran = TransaksiBeliBahan::sum('total_harga');

        // 2. Metrics Produksi & QC
        $totalBatchProduksi = LogProduksi::count();
        $totalGradeA        = LogProduksi::sum('jml_grade_A');
        $totalGradeB        = LogProduksi::sum('jml_grade_B');

        // 3. Metrics Produk Jadi (FG)
        $totalStokFg = ProdukJadi::sum('stok');
        $totalNilaiAsetFg = ProdukJadi::all()->sum(function ($p) {
            return $p->stok * $p->harga_jual;
        });

        // 4. Metrics Penjualan & Logistik
        $poActiveCount    = PoProduk::whereIn('status_po', ['PENDING', 'PROSES', 'DIKIRIM'])->count();
        $pengirimanProses = LogPengiriman::where('status_pengiriman', 'DIPROSES')->count();

        // 5. Data Aktivitas Terbaru untuk Tabel Ringkasan
        $recentLogProduksi = LogProduksi::with('poProduk')->latest()->take(5)->get();
        $recentPoList      = PoProduk::with('detailAlokasi.produkJadi')->latest()->take(5)->get();

        return view('dashboard', compact(
            'stokKritisCount',
            'totalPengeluaran',
            'totalBatchProduksi',
            'totalGradeA',
            'totalGradeB',
            'totalStokFg',
            'totalNilaiAsetFg',
            'poActiveCount',
            'pengirimanProses',
            'recentLogProduksi',
            'recentPoList'
        ));
    }
}