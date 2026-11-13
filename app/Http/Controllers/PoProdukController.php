<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use App\Models\SuratJalan;
use App\Models\DetailAlokasiPo;
use App\Models\LogProduksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PoProdukController extends Controller
{
    private $auditLogFile = 'audit_log_po_produk.json';
    private $capacityConfigFile = 'kitchen_capacity_config.json';

    /**
     * Helper Internal: Mencatat Log Aktivitas Modul PO Pelanggan
     */
    private function recordAuditLog(string $aksi, string $kodePo, string $pelanggan, string $detail, ?string $alasan = null)
    {
        $user = Auth::user();
        $logs = Storage::exists($this->auditLogFile)
            ? json_decode(Storage::get($this->auditLogFile), true) ?? []
            : [];

        array_unshift($logs, [
            'datetime'  => now()->format('d M Y H:i:s'),
            'user_name' => $user->name ?? 'System',
            'role'      => strtoupper($user->role ?? 'STAFF'),
            'aksi'      => $aksi,
            'kode_po'   => $kodePo,
            'pelanggan' => $pelanggan,
            'detail'    => $detail,
            'alasan'    => $alasan ?: '-',
        ]);

        $logs = array_slice($logs, 0, 200);
        Storage::put($this->auditLogFile, json_encode($logs, JSON_PRETTY_PRINT));
    }

    /**
     * Helper Internal: Mengambil Konfigurasi Kapasitas Produksi Harian Dapur
     */
    private function getKitchenCapacityConfig(): array
    {
        $defaults = [
            'max_batch_per_hari' => 4,
            'max_pcs_per_batch'  => 100,
            'jam_kerja_per_hari' => 8,
            'catatan_kapasitas'  => '2 Kuali Utama (Shift Pagi & Siang)',
        ];

        if (!Storage::exists($this->capacityConfigFile)) {
            return $defaults;
        }

        $saved = json_decode(Storage::get($this->capacityConfigFile), true) ?? [];
        return array_merge($defaults, $saved);
    }

    /**
     * SIMPAN PENGATURAN KAPASITAS PRODUKSI HARIAN DAPUR
     */
    public function updateKapasitas(Request $request)
    {
        $request->validate([
            'max_batch_per_hari' => 'required|integer|min:1|max:50',
            'max_pcs_per_batch'  => 'required|integer|min:10|max:5000',
            'jam_kerja_per_hari' => 'nullable|integer|min:1|max:24',
            'catatan_kapasitas'  => 'nullable|string|max:255',
        ]);

        $config = [
            'max_batch_per_hari' => (int) $request->max_batch_per_hari,
            'max_pcs_per_batch'  => (int) $request->max_pcs_per_batch,
            'jam_kerja_per_hari' => (int) ($request->jam_kerja_per_hari ?? 8),
            'catatan_kapasitas'  => $request->catatan_kapasitas ?: 'Standar Operasional Dapur Condet',
        ];

        Storage::put($this->capacityConfigFile, json_encode($config, JSON_PRETTY_PRINT));

        $kapasitasHarian = $config['max_batch_per_hari'] * $config['max_pcs_per_batch'];

        $this->recordAuditLog(
            'KAPASITAS DAPUR',
            'KONFIG-DAPUR',
            'Dapur Produksi Condet',
            "Memperbarui kapasitas harian: {$config['max_batch_per_hari']} batch/hari × {$config['max_pcs_per_batch']} Pcs/batch = " . number_format($kapasitasHarian) . ' Pcs/hari',
            $config['catatan_kapasitas']
        );

        return redirect()->route('po-produk.index')
                         ->with('success', 'Perkiraan kapasitas produksi harian dapur berhasil diperbarui (' . number_format($kapasitasHarian) . ' Pcs/hari).');
    }

    public function index()
    {
        $poList = PoProduk::with(['detailAlokasi.produkJadi', 'suratJalan', 'logProduksi.detailProduksi'])
                    ->where('nama_pelanggan', '!=', 'STOK INTERNAL GUDANG')
                    ->latest()
                    ->get();

        $products   = ProdukJadi::all();
        $suratJalan = SuratJalan::all();

        // 1. Konfigurasi & Referensi Historis Kapasitas Dapur
        $kapasitasConfig    = $this->getKitchenCapacityConfig();
        $maxBatchPerHari    = (int) $kapasitasConfig['max_batch_per_hari'];
        $maxPcsPerBatch     = (int) $kapasitasConfig['max_pcs_per_batch'];
        $kapasitasHarianPcs = max(1, $maxBatchPerHari * $maxPcsPerBatch);

        $approvedLogs = LogProduksi::where('status_approval', 'APPROVED')->get();
        $avgPcsPerBatchAktual = $approvedLogs->count() > 0
            ? (int) round($approvedLogs->avg('jml_grade_A'))
            : $maxPcsPerBatch;

        $maxHarianAktual = $approvedLogs->groupBy(function ($item) {
            return Carbon::parse($item->tgl_produksi)->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('jml_grade_A');
        })->max() ?? 0;

        $kapasitasInfo = [
            'max_batch_per_hari'       => $maxBatchPerHari,
            'max_pcs_per_batch'        => $maxPcsPerBatch,
            'kapasitas_harian_pcs'     => $kapasitasHarianPcs,
            'jam_kerja_per_hari'       => (int) $kapasitasConfig['jam_kerja_per_hari'],
            'catatan_kapasitas'        => $kapasitasConfig['catatan_kapasitas'],
            'avg_pcs_per_batch_aktual' => $avgPcsPerBatchAktual,
            'max_harian_aktual'        => (int) $maxHarianAktual,
        ];

        // 2. Algoritma Penjadwalan & Prioritas Dinamis (Capacity-Aware Critical Ratio + Volume Load)
        $today = Carbon::today();
        $totalAntreanPcs = 0;
        $totalAntreanBatch = 0;
        $deadlineTerdekatHari = null;

        $poAnalysis = [];
        foreach ($poList as $po) {
            $pendingBatches = $po->logProduksi->filter(fn($log) => strtoupper($log->status_approval) !== 'APPROVED');
            $hasPendingBatch = $pendingBatches->isNotEmpty();

            $hasilGorengPerProduk = [];
            foreach ($po->logProduksi as $log) {
                if (strtoupper($log->status_approval) === 'APPROVED') {
                    foreach ($log->detailProduksi as $det) {
                        $hasilGorengPerProduk[$det->id_produk_jadi] = ($hasilGorengPerProduk[$det->id_produk_jadi] ?? 0) + $det->jml_produk_jadi;
                    }
                }
            }

            $isStatusSudahLanjut = in_array($po->status_po, ['STOK_DIPOTONG', 'DIKIRIM', 'SELESAI']);
            $totalTarget = 0;
            $totalSiap = 0;
            $butuhProduksiPcs = 0;
            $isSemuaItemSiap = true;

            foreach ($po->detailAlokasi as $alokasi) {
                $targetQty = (int) $alokasi->jml_alokasi;
                $stokFisik = max(0, (int) ($alokasi->produkJadi->stok ?? 0));

                $totalTarget += $targetQty;

                if ($isStatusSudahLanjut) {
                    $totalSiap += $targetQty;
                } else {
                    $siapItem = min($targetQty, $stokFisik);
                    $totalSiap += $siapItem;
                    $kurangItem = max(0, $targetQty - $stokFisik);
                    $butuhProduksiPcs += $kurangItem;

                    if ($hasPendingBatch || $stokFisik < $targetQty) {
                        $isSemuaItemSiap = false;
                    }
                }
            }

            if ($po->detailAlokasi->isEmpty() || $hasPendingBatch) {
                $isSemuaItemSiap = false;
            }

            $isEcommerce = !empty($po->nomor_pesanan_ecommerce)
                || strcasecmp((string) $po->sumber_transaksi, 'E-Commerce') === 0
                || stripos((string) $po->tipe_jual, 'commerce') !== false;

            $jatuhTempo = Carbon::parse($po->tgl_jatuh_tempo_po)->startOfDay();
            $sisaHariTempo = (int) $today->diffInDays($jatuhTempo, false);

            if ($isStatusSudahLanjut || $isSemuaItemSiap) {
                $urgensiLevel = 'SELESAI';
                $urgensiLabel = $isStatusSudahLanjut ? 'TERPROSES' : 'SIAP POTONG STOK';
                $alasanPrioritas = $isStatusSudahLanjut
                    ? 'Stok sudah dialokasikan / dikirim'
                    : 'Stok gudang sudah mencukupi 100% target PO';
                $butuhBatch = 0;
                $butuhHari = 0;
                $slackHari = $sisaHariTempo;
                $criticalRatio = 999;
                $priorityScore = -1;
            } else {
                $bebanPcs = max($butuhProduksiPcs, $hasPendingBatch ? $totalTarget : 0);
                $butuhBatch = (int) ceil($bebanPcs / $maxPcsPerBatch);
                $butuhHari = round($bebanPcs / $kapasitasHarianPcs, 2);
                $slackHari = round($sisaHariTempo - $butuhHari, 2);
                $criticalRatio = round(max(0.1, $sisaHariTempo) / max(0.1, $butuhHari), 2);

                $volumeLoadRatio = $bebanPcs / $kapasitasHarianPcs;
                $baseScore = (100 / max(0.25, $criticalRatio)) + ($volumeLoadRatio * 28);

                if ($sisaHariTempo <= 0) {
                    $baseScore += 120;
                } elseif ($sisaHariTempo <= 1) {
                    $baseScore += 75;
                }

                if ($sisaHariTempo <= 1 || $criticalRatio <= 1.15 || $slackHari <= 0.5) {
                    $urgensiLevel = 'KRITIS';
                    $urgensiLabel = 'KRITIS • WAJIB DAHULUKAN';
                    if ($sisaHariTempo < 0) {
                        $alasanPrioritas = 'Terlambat ' . abs($sisaHariTempo) . ' hari! Butuh ' . $butuhBatch . ' batch (' . $butuhHari . ' hari dapur).';
                    } elseif ($sisaHariTempo <= 1) {
                        $alasanPrioritas = 'Jatuh tempo ' . ($sisaHariTempo === 0 ? 'HARI INI' : 'BESOK') . ' • Butuh ' . $butuhBatch . ' batch segera.';
                    } else {
                        $alasanPrioritas = 'Kelonggaran waktu tinggal ' . max(0, $slackHari) . ' hari untuk ' . $butuhBatch . ' batch (' . $butuhHari . ' hari produksi).';
                    }
                } elseif ($criticalRatio <= 2.0 || $slackHari <= 2.0 || $volumeLoadRatio >= 0.6) {
                    $urgensiLevel = 'TINGGI';
                    $urgensiLabel = 'PRIORITAS TINGGI';
                    if ($volumeLoadRatio >= 0.6) {
                        $alasanPrioritas = 'Pesanan besar (' . number_format($bebanPcs) . ' Pcs / ' . $butuhBatch . ' batch = ' . $butuhHari . ' hari). Cicil sekarang agar tidak tertimpa PO lain.';
                    } else {
                        $alasanPrioritas = 'Sisa kelonggaran ' . $slackHari . ' hari untuk ' . $butuhBatch . ' batch produksi.';
                    }
                } else {
                    $urgensiLevel = 'NORMAL';
                    $urgensiLabel = 'NORMAL / AMAN';
                    $alasanPrioritas = 'Kelonggaran ' . $slackHari . ' hari masih aman (' . $butuhBatch . ' batch / ' . $butuhHari . ' hari dapur).';
                }

                $priorityScore = round($baseScore, 2);

                if (!$hasPendingBatch) {
                    $totalAntreanPcs += $butuhProduksiPcs;
                    $totalAntreanBatch += $butuhBatch;
                }
                if ($deadlineTerdekatHari === null || $sisaHariTempo < $deadlineTerdekatHari) {
                    $deadlineTerdekatHari = $sisaHariTempo;
                }
            }

            $poAnalysis[$po->no_po_produk] = [
                'is_ecommerce'         => $isEcommerce,
                'has_pending_batch'    => $hasPendingBatch,
                'pending_batch_codes'  => $pendingBatches->map(fn($b) => 'BATCH-00' . $b->no_batch)->implode(', '),
                'hasil_goreng_qc'      => $hasilGorengPerProduk,
                'is_semua_item_siap'   => $isSemuaItemSiap,
                'total_target'         => $totalTarget,
                'total_siap'           => $totalSiap,
                'butuh_produksi_pcs'   => $butuhProduksiPcs,
                'butuh_batch'          => $butuhBatch,
                'butuh_hari'           => $butuhHari,
                'sisa_hari_tempo'      => $sisaHariTempo,
                'slack_hari'           => $slackHari,
                'critical_ratio'       => $criticalRatio,
                'urgensi_level'        => $urgensiLevel,
                'urgensi_label'        => $urgensiLabel,
                'alasan_prioritas'     => $alasanPrioritas,
                'priority_score'       => $priorityScore,
                'rekomendasi_rank'     => null,
                'estimasi_selesai_tgl' => null,
                'is_berisiko_telat'    => false,
            ];
        }

        // 3. Simulasi Antrean Kumulatif Berdasarkan Urutan Algoritma Prioritas
        $activePoSorted = $poList->filter(function ($po) use ($poAnalysis) {
            return ($poAnalysis[$po->no_po_produk]['urgensi_level'] ?? 'SELESAI') !== 'SELESAI';
        })->sort(function ($a, $b) use ($poAnalysis) {
            $scoreA = $poAnalysis[$a->no_po_produk]['priority_score'];
            $scoreB = $poAnalysis[$b->no_po_produk]['priority_score'];
            if ($scoreA === $scoreB) {
                return $a->no_po_produk <=> $b->no_po_produk;
            }
            return $scoreB <=> $scoreA;
        })->values();

        $kumulatifHariKerja = 0.0;
        foreach ($activePoSorted as $idx => $poItem) {
            $id = $poItem->no_po_produk;
            $butuhHari = $poAnalysis[$id]['butuh_hari'];
            $kumulatifHariKerja += $butuhHari;

            $estSelesai = $today->copy()->addDays((int) ceil($kumulatifHariKerja));
            $jatuhTempo = Carbon::parse($poItem->tgl_jatuh_tempo_po)->startOfDay();

            $poAnalysis[$id]['rekomendasi_rank']     = $idx + 1;
            $poAnalysis[$id]['estimasi_selesai_tgl'] = $estSelesai->format('d M Y');
            $poAnalysis[$id]['is_berisiko_telat']    = $estSelesai->gt($jatuhTempo);
        }

        $poList = $poList->sortBy(function ($po) use ($poAnalysis) {
            $rank = $poAnalysis[$po->no_po_produk]['rekomendasi_rank'];
            return $rank !== null ? $rank : (10000 + $po->no_po_produk);
        })->values();

        $estimasiTotalHariAntrean = round($totalAntreanPcs / $kapasitasHarianPcs, 1);
        $isOverloadKapasitas = ($deadlineTerdekatHari !== null && $estimasiTotalHariAntrean > max(0, $deadlineTerdekatHari));

        $bebanAntreanInfo = [
            'total_antrean_pcs'      => $totalAntreanPcs,
            'total_antrean_batch'    => $totalAntreanBatch,
            'estimasi_hari_kerja'    => $estimasiTotalHariAntrean,
            'deadline_terdekat_hari' => $deadlineTerdekatHari,
            'is_overload'            => $isOverloadKapasitas,
            'jumlah_po_kritis'       => collect($poAnalysis)->where('urgensi_level', 'KRITIS')->count(),
            'jumlah_po_tinggi'       => collect($poAnalysis)->where('urgensi_level', 'TINGGI')->count(),
            'jumlah_po_ecommerce'    => collect($poAnalysis)->where('is_ecommerce', true)->count(),
        ];

        $totalPo         = $poList->count();
        $poPendingCount  = $poList->where('status_po', 'PENDING')->count();
        $poProsesCount   = $poList->where('status_po', 'PROSES')->count();
        $poSelesaiCount  = $poList->where('status_po', 'SELESAI')->count();

        $auditLogs = Storage::exists($this->auditLogFile)
            ? json_decode(Storage::get($this->auditLogFile), true) ?? []
            : [];

        return view('sales.po_produk', compact(
            'poList',
            'products',
            'suratJalan',
            'totalPo',
            'poPendingCount',
            'poProsesCount',
            'poSelesaiCount',
            'auditLogs',
            'kapasitasInfo',
            'poAnalysis',
            'bebanAntreanInfo'
        ));
    }

    /**
     * INPUT DATA PO PELANGGAN
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan'          => 'required|string|max:255',
            'nama_penerima'           => 'required|string|max:255',
            'tipe_jual'               => 'required|string',
            'tgl_po'                  => 'required|date',
            'tgl_jatuh_tempo_po'      => 'required|date',
            'alamat_pelanggan'        => 'required|string',
            'produk_ids'              => 'required|array|min:1',
            'produk_qty'              => 'required|array|min:1',
            'no_ref'                  => 'nullable',
            'nomor_pesanan_ecommerce' => 'nullable|string|max:100',
            'buat_batch_langsung'     => 'nullable|boolean',
        ]);

        $existingPo = PoProduk::where('nama_pelanggan', trim($request->nama_pelanggan))
            ->where('tgl_po', $request->tgl_po)
            ->first();

        if ($existingPo) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! PO untuk pelanggan ini pada tanggal tersebut sudah pernah dibuat.');
        }

        $createdPo = null;
        $itemSummaries = [];
        $directToBatch = $request->filled('buat_batch_langsung') || $request->input('action') === 'buat_batch';

        DB::transaction(function () use ($request, &$createdPo, &$itemSummaries, $directToBatch) {
            $totalQtyPo = array_sum($request->produk_qty);
            $isEcom = $request->filled('nomor_pesanan_ecommerce') || $request->tipe_jual === 'E-Commerce';

            $po = PoProduk::create([
                'nama_pelanggan'          => trim($request->nama_pelanggan),
                'nama_penerima'           => trim($request->nama_penerima),
                'tipe_jual'               => $request->tipe_jual,
                'jumlah_po'               => $totalQtyPo,
                'tgl_po'                  => $request->tgl_po,
                'tgl_jatuh_tempo_po'      => $request->tgl_jatuh_tempo_po,
                'status_po'               => $directToBatch ? 'PROSES' : 'PENDING',
                'alamat_pelanggan'        => $request->alamat_pelanggan,
                'no_ref'                  => $request->no_ref ?: null,
                'nomor_pesanan_ecommerce' => $request->nomor_pesanan_ecommerce ?: null,
                'sumber_transaksi'        => $isEcom ? 'E-Commerce' : 'Manual PO',
            ]);

            foreach ($request->produk_ids as $index => $idProduk) {
                $qty = (int) ($request->produk_qty[$index] ?? 0);
                if ($idProduk && $qty > 0) {
                    DetailAlokasiPo::create([
                        'id_produk_jadi' => $idProduk,
                        'no_po_produk'   => $po->no_po_produk,
                        'jml_alokasi'    => $qty,
                    ]);

                    $prod = ProdukJadi::find($idProduk);
                    $itemSummaries[] = ($prod->nama_produk ?? "SKU #{$idProduk}") . " ({$qty} Pcs)";
                }
            }

            $createdPo = $po;
        });

        if ($createdPo) {
            $this->recordAuditLog(
                'ADD PO',
                'PO-00' . $createdPo->no_po_produk,
                $createdPo->nama_pelanggan,
                'Membuat PO baru (' . count($itemSummaries) . ' varian: ' . implode(', ', $itemSummaries) . ') | Jenis: ' . $createdPo->tipe_jual,
                'Input PO Pelanggan Baru'
            );

            if ($directToBatch) {
                return redirect()->route('log-produksi.index', ['no_po' => $createdPo->no_po_produk])
                                 ->with('success', 'PO berhasil disimpan! Silakan konfirmasi jadwal Batch Produksi untuk PO-00' . $createdPo->no_po_produk . '.');
            }
        }

        return redirect()->route('po-produk.index')
                         ->with('success', 'Data Berhasil Disimpan! PO Pelanggan berhasil dicatat.');
    }

    /**
     * POTONG STOK READY DENGAN VALIDASI QC GATE & ANTI-MINUS
     */
    public function potongStokReady($id)
    {
        $po = PoProduk::with(['detailAlokasi.produkJadi', 'logProduksi'])->findOrFail($id);

        if (in_array($po->status_po, ['STOK_DIPOTONG', 'SELESAI', 'DIKIRIM'])) {
            return redirect()->back()->with('error', "Stok untuk PO-00{$po->no_po_produk} sudah pernah dipotong sebelumnya.");
        }

        // 1. VALIDASI GATE QC
        $pendingBatches = $po->logProduksi->filter(function ($log) {
            return strtoupper($log->status_approval) !== 'APPROVED';
        });

        if ($pendingBatches->isNotEmpty()) {
            $kodeBatches = $pendingBatches->map(fn($b) => 'BATCH-00' . $b->no_batch)->implode(', ');
            return redirect()->back()->with(
                'error',
                "Ditolak! Masih ada proses produksi ({$kodeBatches}) yang belum selesai & belum input QC di Log Produksi."
            );
        }

        // 2. VALIDASI ANTI-MINUS
        $itemKurang = [];
        foreach ($po->detailAlokasi as $alokasi) {
            $stokAktual = (int) ($alokasi->produkJadi->stok ?? 0);
            $butuhQty   = (int) $alokasi->jml_alokasi;

            if ($stokAktual < $butuhQty) {
                $namaProd = $alokasi->produkJadi->nama_produk ?? "SKU #{$alokasi->id_produk_jadi}";
                $selisih  = $butuhQty - $stokAktual;
                $itemKurang[] = "{$namaProd} (Butuh: {$butuhQty} Pcs, Stok Gudang: {$stokAktual} Pcs, Kurang: {$selisih} Pcs)";
            }
        }

        if (!empty($itemKurang)) {
            return redirect()->back()->with(
                'error',
                'Gagal Potong Stok! Stok fisik produk jadi di gudang tidak mencukupi: ' . implode('; ', $itemKurang) . '. Selesaikan batch produksi & input QC terlebih dahulu.'
            );
        }

        try {
            DB::transaction(function () use ($po) {
                foreach ($po->detailAlokasi as $alokasi) {
                    if ($alokasi->produkJadi) {
                        $produk = ProdukJadi::where('id_produk_jadi', $alokasi->id_produk_jadi)
                            ->lockForUpdate()
                            ->first();

                        if (!$produk || $produk->stok < $alokasi->jml_alokasi) {
                            throw new \RuntimeException('Stok fisik berubah dan tidak mencukupi saat transaksi diproses.');
                        }

                        $produk->decrement('stok', $alokasi->jml_alokasi);
                    }
                }

                $po->update(['status_po' => 'STOK_DIPOTONG']);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', 'Gagal Potong Stok! ' . $e->getMessage());
        }

        $this->recordAuditLog(
            'POTONG STOK',
            'PO-00' . $po->no_po_produk,
            $po->nama_pelanggan,
            'Memotong stok produk jadi sebesar ' . number_format($po->jumlah_po) . ' Pcs untuk persiapan pengiriman',
            'Alokasi Stok Ready'
        );

        return redirect()->route('po-produk.index')
                        ->with('success', 'Stok ready berhasil dipotong! Silakan terbitkan Surat Jalan.');
    }

    /**
     * ARAHKAN LANGSUNG KE FORM INPUT BATCH PRODUKSI DENGAN ACUAN PO TERPILIH
     */
    public function buatProdukBatch(Request $request, $id)
    {
        $po = PoProduk::findOrFail($id);

        if ($po->status_po === 'PENDING') {
            $po->update(['status_po' => 'PROSES']);
        }

        return redirect()->route('log-produksi.index', ['no_po' => $po->no_po_produk]);
    }

    /**
     * HAPUS PO PELANGGAN DENGAN ALASAN WAJIB + RESTORE STOK + AUDIT LOG
     */
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'kategori_alasan' => 'required|string|max:100',
            'detail_alasan'   => 'required|string|max:255',
        ], [
            'kategori_alasan.required' => 'Kategori alasan penghapusan wajib dipilih.',
            'detail_alasan.required'   => 'Keterangan detail alasan penghapusan wajib diisi.',
        ]);

        $po = PoProduk::with(['detailAlokasi.produkJadi', 'suratJalan', 'logProduksi.detailProduksi'])->findOrFail($id);

        if ($po->suratJalan || in_array($po->status_po, ['DIKIRIM', 'SELESAI'])) {
            return redirect()->back()->with(
                'error',
                "Ditolak! PO-00{$po->no_po_produk} tidak dapat dihapus karena sudah memiliki Surat Jalan atau berstatus {$po->status_po}."
            );
        }

        $hasHasilProduksi = $po->logProduksi->contains(function ($log) {
            return ($log->jml_grade_A > 0 || $log->jml_grade_B > 0 || $log->detailProduksi->count() > 0);
        });

        if ($hasHasilProduksi) {
            return redirect()->back()->with(
                'error',
                "Ditolak! PO-00{$po->no_po_produk} sudah memiliki jadwal/hasil penggorengan tercatat pada Log Produksi Dapur."
            );
        }

        $kodePo        = 'PO-00' . $po->no_po_produk;
        $namaPelanggan = $po->nama_pelanggan;
        $statusSebelum = $po->status_po;
        $itemList      = [];
        $isStokRestored = false;

        DB::transaction(function () use ($po, &$itemList, &$isStokRestored) {
            if ($po->status_po === 'STOK_DIPOTONG') {
                foreach ($po->detailAlokasi as $alokasi) {
                    if ($alokasi->produkJadi) {
                        $alokasi->produkJadi->increment('stok', $alokasi->jml_alokasi);
                    }
                }
                $isStokRestored = true;
            }

            foreach ($po->detailAlokasi as $alokasi) {
                $namaProd = $alokasi->produkJadi->nama_produk ?? "SKU #{$alokasi->id_produk_jadi}";
                $itemList[] = "{$namaProd} ({$alokasi->jml_alokasi} Pcs)";
            }

            foreach ($po->logProduksi as $log) {
                $log->delete();
            }

            $po->detailAlokasi()->delete();
            $po->delete();
        });

        $infoRestore = $isStokRestored ? ' [Stok fisik otomatis dikembalikan ke gudang]' : '';
        $alasanGabungan = "[{$request->kategori_alasan}] {$request->detail_alasan}";

        $this->recordAuditLog(
            'DELETE PO',
            $kodePo,
            $namaPelanggan,
            "Menghapus pesanan (Status terakhir: {$statusSebelum} | Total: {$po->jumlah_po} Pcs | Item: " . implode(', ', $itemList) . "){$infoRestore}",
            $alasanGabungan
        );

        return redirect()->route('po-produk.index')
                         ->with('success', "{$kodePo} ({$namaPelanggan}) berhasil dihapus dan dicatat di Riwayat Audit Log.");
    }
}