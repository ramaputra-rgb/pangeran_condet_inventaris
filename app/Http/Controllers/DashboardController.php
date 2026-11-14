<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BahanProduksi;
use App\Models\LogPengiriman;
use App\Models\LogProduksi;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use App\Models\TransaksiBeliBahan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. Metrics Pengadaan & Bahan
        $stokKritisCount = BahanProduksi::where('qty_stok', '<=', 10)->count();
        $totalPengeluaran = (float) TransaksiBeliBahan::sum('total_harga');

        // 2. Metrics Produksi & QC
        $allProduksi = LogProduksi::with('poProduk')->orderBy('tgl_produksi')->get();
        $totalBatchProduksi = $allProduksi->count();
        $totalGradeA = (int) $allProduksi->sum('jml_grade_A');
        $totalGradeB = (int) $allProduksi->sum('jml_grade_B');

        // 3. Metrics Produk Jadi (FG)
        $allProducts = ProdukJadi::with('detailAlokasiPo.poProduk')->get();
        $totalStokFg = (int) $allProducts->sum('stok');
        $totalNilaiAsetFg = $allProducts->sum(function ($p) {
            return (int) $p->stok * (float) $p->harga_jual;
        });

        // 4. Metrics Penjualan & Logistik
        $customerPos = PoProduk::with('detailAlokasi.produkJadi')
            ->where('nama_pelanggan', '!=', 'STOK INTERNAL GUDANG')
            ->latest()
            ->get();

        $poActiveStatuses = ['PENDING', 'PROSES', 'STOK_DIPOTONG', 'DIKIRIM'];
        $poActiveCount = $customerPos->whereIn('status_po', $poActiveStatuses)->count();
        $pengirimanProses = LogPengiriman::where('status_pengiriman', 'DIPROSES')->count();

        // 5. Aktivitas Terbaru (untuk ringkasan operasional di bawah grafik)
        $recentLogProduksi = LogProduksi::with('poProduk')->latest()->take(5)->get();
        $recentPoList = $customerPos->take(5);

        // 6. Grafik 1: Tren Permintaan PO vs Output Produksi
        $poByDate = [];
        foreach ($customerPos as $po) {
            if (! $po->tgl_po) {
                continue;
            }
            $dateKey = Carbon::parse($po->tgl_po)->format('Y-m-d');
            if (! isset($poByDate[$dateKey])) {
                $poByDate[$dateKey] = ['qty' => 0, 'count' => 0];
            }
            $poByDate[$dateKey]['qty'] += (int) $po->jumlah_po;
            $poByDate[$dateKey]['count']++;
        }

        $prodByDate = [];
        foreach ($allProduksi as $log) {
            if (! $log->tgl_produksi) {
                continue;
            }
            $dateKey = Carbon::parse($log->tgl_produksi)->format('Y-m-d');
            if (! isset($prodByDate[$dateKey])) {
                $prodByDate[$dateKey] = ['qty' => 0, 'gradeA' => 0, 'gradeB' => 0, 'batches' => 0];
            }
            $gradeA = (int) $log->jml_grade_A;
            $gradeB = (int) $log->jml_grade_B;
            $prodByDate[$dateKey]['qty'] += $gradeA + $gradeB;
            $prodByDate[$dateKey]['gradeA'] += $gradeA;
            $prodByDate[$dateKey]['gradeB'] += $gradeB;
            $prodByDate[$dateKey]['batches']++;
        }

        $dateKeys = array_values(array_unique(array_merge(array_keys($poByDate), array_keys($prodByDate))));
        sort($dateKeys);
        if (count($dateKeys) > 7) {
            $dateKeys = array_slice($dateKeys, -7);
        }
        $padOffset = 0;
        while (count($dateKeys) < 5 && $padOffset < 10) {
            $candidate = Carbon::today()->subDays($padOffset)->format('Y-m-d');
            if (! in_array($candidate, $dateKeys, true)) {
                $dateKeys[] = $candidate;
            }
            $padOffset++;
        }
        sort($dateKeys);
        if (count($dateKeys) > 7) {
            $dateKeys = array_slice($dateKeys, -7);
        }

        $trendSeries = [];
        $trendRawMax = 0;
        foreach ($dateKeys as $dateKey) {
            $poQty = $poByDate[$dateKey]['qty'] ?? 0;
            $poCount = $poByDate[$dateKey]['count'] ?? 0;
            $prodQty = $prodByDate[$dateKey]['qty'] ?? 0;
            $trendRawMax = max($trendRawMax, $poQty, $prodQty);

            $parsedDate = Carbon::parse($dateKey);
            $trendSeries[] = [
                'date' => $dateKey,
                'label' => $parsedDate->format('d/m'),
                'fullDate' => $parsedDate->format('d M Y'),
                'poQty' => $poQty,
                'poCount' => $poCount,
                'prodQty' => $prodQty,
                'gradeA' => $prodByDate[$dateKey]['gradeA'] ?? 0,
                'gradeB' => $prodByDate[$dateKey]['gradeB'] ?? 0,
                'batches' => $prodByDate[$dateKey]['batches'] ?? 0,
            ];
        }
        $trendYMax = $this->computeNiceMax($trendRawMax, 10);

        // 7. Grafik 2: Distribusi Status PO Pelanggan (Donut)
        $statusDefs = [
            'PENDING' => ['label' => 'Menunggu', 'tone' => 'pending'],
            'PROSES' => ['label' => 'Diproses', 'tone' => 'proses'],
            'STOK_DIPOTONG' => ['label' => 'Siap kirim', 'tone' => 'ready'],
            'DIKIRIM' => ['label' => 'Dikirim', 'tone' => 'shipped'],
            'SELESAI' => ['label' => 'Selesai', 'tone' => 'done'],
        ];
        $totalPoAll = $customerPos->count();
        $poStatusChart = [];
        foreach ($statusDefs as $code => $meta) {
            $matching = $customerPos->where('status_po', $code);
            $cnt = $matching->count();
            $poStatusChart[] = [
                'code' => $code,
                'label' => $meta['label'],
                'tone' => $meta['tone'],
                'count' => $cnt,
                'qty' => (int) $matching->sum('jumlah_po'),
                'pct' => $totalPoAll > 0 ? round(($cnt / $totalPoAll) * 100, 1) : 0,
            ];
        }

        // 8. Grafik 3: Permintaan PO Aktif vs Stok Produk Jadi (Top 6)
        $productDemandChart = $allProducts->map(function ($produk) use ($poActiveStatuses) {
            $poDemand = 0;
            foreach ($produk->detailAlokasiPo as $alokasi) {
                $po = $alokasi->poProduk;
                if (
                    $po &&
                    $po->nama_pelanggan !== 'STOK INTERNAL GUDANG' &&
                    in_array($po->status_po, $poActiveStatuses, true)
                ) {
                    $poDemand += (int) $alokasi->jml_alokasi;
                }
            }
            $stok = (int) $produk->stok;

            return [
                'name' => $produk->nama_produk,
                'variant' => $produk->varian ?: $produk->netto,
                'poQty' => $poDemand,
                'stok' => $stok,
                'shortage' => max(0, $poDemand - $stok),
                'score' => ($poDemand * 2) + $stok,
            ];
        })
            ->sortByDesc('score')
            ->take(6)
            ->values();

        $productMaxVal = max(
            1,
            (int) $productDemandChart->max('poQty'),
            (int) $productDemandChart->max('stok')
        );

        // 9. Grafik 4: Level Stok Bahan Baku Terendah vs Batas Minimum (10)
        $lowestMaterials = BahanProduksi::orderBy('qty_stok', 'asc')->take(8)->get();
        $materialChart = $lowestMaterials->map(function ($mat) {
            $qty = (float) $mat->qty_stok;
            $tone = $qty <= 5 ? 'alert' : ($qty <= 10 ? 'warn' : 'ok');
            $statusLabel = $qty <= 5 ? 'Kritis' : ($qty <= 10 ? 'Perlu restok' : 'Aman');

            return [
                'name' => $mat->nama_bahan,
                'shortName' => Str::limit($mat->nama_bahan, 13, '…'),
                'qty' => $qty,
                'qtyLabel' => rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.'),
                'unit' => $mat->ukuran,
                'tone' => $tone,
                'statusLabel' => $statusLabel,
            ];
        })->values();

        $materialRawMax = (float) ($materialChart->max('qty') ?? 0);
        $materialYMax = $this->computeNiceMax((int) ceil(max(12, $materialRawMax)), 15);

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
            'recentPoList',
            'trendSeries',
            'trendYMax',
            'poStatusChart',
            'totalPoAll',
            'productDemandChart',
            'productMaxVal',
            'materialChart',
            'materialYMax'
        ));
    }

    private function computeNiceMax(int $rawMax, int $floor = 10): int
    {
        if ($rawMax <= 0) {
            return $floor;
        }

        $target = max($floor, $rawMax);
        $magnitude = (int) pow(10, max(0, (int) floor(log10($target)) - 1));
        $stepUnit = max(5, $magnitude * 5);

        return (int) (ceil($target / $stepUnit) * $stepUnit);
    }
}
