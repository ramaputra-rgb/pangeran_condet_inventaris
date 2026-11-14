<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiBeliBahan;
use App\Models\LogProduksi;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use App\Models\LogPengiriman;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ReportController extends Controller
{
    /**
     * Lokasi penyimpanan file JSON riwayat cetak laporan
     */
    private function getLogFilePath(): string
    {
        return storage_path('app/log_cetak_laporan.json');
    }

    /**
     * Membaca seluruh riwayat cetak dari file JSON sebagai Collection of Objects
     */
    private function readPrintLogs(): Collection
    {
        $path = $this->getLogFilePath();

        if (!File::exists($path)) {
            return collect();
        }

        $decoded = json_decode(File::get($path), true);

        if (!is_array($decoded)) {
            return collect();
        }

        return collect($decoded)
            ->map(fn ($item) => (object) $item)
            ->sortByDesc(fn ($item) => ($item->tgl_cetak ?? '') . '_' . str_pad((string) ($item->id_log_cetak ?? 0), 8, '0', STR_PAD_LEFT))
            ->values();
    }

    /**
     * Menambahkan catatan cetak baru ke file JSON
     */
    private function appendPrintLog(string $jenis, ?string $mulai, ?string $selesai): object
    {
        $path = $this->getLogFilePath();
        $existing = [];

        if (File::exists($path)) {
            $decoded = json_decode(File::get($path), true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $maxId = collect($existing)->max('id_log_cetak') ?? 0;

        $kriteria = $jenis === 'stok'
            ? 'Posisi Stok Aktual'
            : 'Periode: ' . $mulai . ' s/d ' . $selesai;

        $newRecord = [
            'id_log_cetak'    => $maxId + 1,
            'jenis_laporan'   => $jenis,
            'kriteria_filter' => $kriteria,
            'tgl_cetak'       => now()->toDateTimeString(),
            'status_cetak'    => 'BERHASIL',
        ];

        $existing[] = $newRecord;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return (object) $newRecord;
    }

    /**
     * Menampilkan Halaman Utama Laporan & Audit Log Cetak
     */
    public function index()
    {
        $logCetakList = $this->readPrintLogs();

        return view('reports', [
            'logCetakList'  => $logCetakList,
            'previewActive' => false,
        ]);
    }

    /**
     * Filter, Catat Log Audit ke JSON, & Tampilkan Pratinjau Cetak
     */
    public function previewOrPrint(Request $request)
    {
        $request->validate([
            'jenis_laporan'   => 'required|in:stok,pengadaan,produksi,penjualan,pengiriman',
            'periode_mulai'   => 'required_unless:jenis_laporan,stok|nullable|date',
            'periode_selesai' => 'required_unless:jenis_laporan,stok|nullable|date|after_or_equal:periode_mulai',
        ], [
            'periode_mulai.required_unless'   => 'Tanggal mulai wajib diisi untuk laporan transaksi.',
            'periode_selesai.required_unless' => 'Tanggal selesai wajib diisi untuk laporan transaksi.',
            'periode_selesai.after_or_equal'  => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $jenis   = $request->jenis_laporan;
        $mulai   = $jenis === 'stok' ? null : $request->periode_mulai;
        $selesai = $jenis === 'stok' ? null : $request->periode_selesai;

        $judulMap = [
            'stok'       => 'Laporan Stok & Varian Produk Jadi',
            'pengadaan'  => 'Laporan Pengadaan & Pembelian Bahan Baku',
            'produksi'   => 'Laporan Log Produksi & QC Dapur',
            'penjualan'  => 'Laporan Purchase Order & Penjualan Pelanggan',
            'pengiriman' => 'Laporan Log Pengiriman & Surat Jalan',
        ];

        $judulLaporan = $judulMap[$jenis] ?? 'Laporan Operasional';
        $dataLaporan  = collect();

        if ($jenis === 'stok') {
            $dataLaporan = ProdukJadi::all();
        } elseif ($jenis === 'pengadaan') {
            $dataLaporan = TransaksiBeliBahan::with('bahanProduksi')
                ->whereBetween('created_at', [$mulai . ' 00:00:00', $selesai . ' 23:59:59'])
                ->latest()
                ->get();
        } elseif ($jenis === 'produksi') {
            $dataLaporan = LogProduksi::with(['poProduk', 'detailProduksi.produkJadi'])
                ->whereBetween('tgl_produksi', [$mulai, $selesai])
                ->latest('tgl_produksi')
                ->get();
        } elseif ($jenis === 'penjualan') {
            $dataLaporan = PoProduk::with('detailAlokasi.produkJadi')
                ->whereBetween('tgl_po', [$mulai, $selesai])
                ->latest('tgl_po')
                ->get();
        } elseif ($jenis === 'pengiriman') {
            $dataLaporan = LogPengiriman::with(['poProduk.suratJalan'])
                ->where(function ($q) use ($mulai, $selesai) {
                    $q->whereBetween('Tanggal', [$mulai, $selesai])
                      ->orWhere(function ($q2) use ($mulai, $selesai) {
                          $q2->whereNull('Tanggal')
                             ->whereBetween('created_at', [$mulai . ' 00:00:00', $selesai . ' 23:59:59']);
                      });
                })
                ->orderByDesc('Tanggal')
                ->latest()
                ->get();
        }

        // Simpan log cetak ke file JSON
        $logBaru = $this->appendPrintLog($jenis, $mulai, $selesai);

        // Baca ulang daftar log terbaru
        $logCetakList = $this->readPrintLogs();

        return view('reports', [
            'logCetakList'  => $logCetakList,
            'previewActive' => true,
            'jenis'         => $jenis,
            'judulLaporan'  => $judulLaporan,
            'dataLaporan'   => $dataLaporan,
            'mulai'         => $mulai,
            'selesai'       => $selesai,
            'logAktif'      => $logBaru,
        ]);
    }
}