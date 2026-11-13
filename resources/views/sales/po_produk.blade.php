@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Pesanan PO Pelanggan (Make-to-Order)</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Pencatatan Purchase Order E-Commerce, Konsinyasi, Reseller & Grosir dengan penjadwalan prioritas berbasis kapasitas dapur.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2 no-print">
        <button type="button" onclick="document.getElementById('modal-kapasitas').classList.remove('hidden')" class="btn btn-outline">
            ⚙️ Atur Kapasitas Dapur
        </button>

        <button onclick="document.getElementById('modal-po').classList.remove('hidden')" class="btn btn-primary">
            + Buat PO Pelanggan Baru
        </button>

        <button onclick="window.print()" class="btn btn-outline">
            🖨️ Cetak Laporan PO
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 no-print">
    <div class="card-summary" style="border-left: 4px solid #dc2626;">
        <p class="card-title uppercase" style="color: #dc2626;">PO KRITIS / PRIORITAS TINGGI</p>
        <h3 class="card-value" style="color: var(--pc-dark);">
            {{ ($bebanAntreanInfo['jumlah_po_kritis'] ?? 0) + ($bebanAntreanInfo['jumlah_po_tinggi'] ?? 0) }}
            <span style="font-size:0.75rem; color: var(--text-muted);">PO Mendesak</span>
        </h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #dc2626;">
            🔴 {{ $bebanAntreanInfo['jumlah_po_kritis'] ?? 0 }} Kritis • 🟠 {{ $bebanAntreanInfo['jumlah_po_tinggi'] ?? 0 }} Tinggi
        </p>
    </div>

    <div class="card-summary" style="border-left: 4px solid var(--pc-orange);">
        <p class="card-title uppercase" style="color: var(--pc-orange);">BEBAN ANTREAN PRODUKSI</p>
        <h3 class="card-value" style="color: var(--pc-dark);">
            {{ number_format($bebanAntreanInfo['total_antrean_pcs'] ?? 0) }}
            <span style="font-size:0.75rem; color: var(--text-muted);">Pcs</span>
        </h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #b45309;">
            🍳 ±{{ $bebanAntreanInfo['total_antrean_batch'] ?? 0 }} Batch ({{ $bebanAntreanInfo['estimasi_hari_kerja'] ?? 0 }} Hari Dapur)
        </p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #2563eb;">
        <p class="card-title uppercase" style="color: #2563eb;">KAPASITAS MAKS. HARIAN</p>
        <h3 class="card-value" style="color: var(--pc-dark);">
            {{ number_format($kapasitasInfo['kapasitas_harian_pcs'] ?? 400) }}
            <span style="font-size:0.75rem; color: var(--text-muted);">Pcs/Hari</span>
        </h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #1d4ed8;">
            ⚡ {{ $kapasitasInfo['max_batch_per_hari'] ?? 4 }} Batch/hari × {{ $kapasitasInfo['max_pcs_per_batch'] ?? 100 }} Pcs/batch
        </p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #10b981;">
        <p class="card-title uppercase" style="color: #059669;">PO SELESAI / TERKIRIM</p>
        <h3 class="card-value" style="color: #059669;">{{ $poSelesaiCount ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">Dari {{ $totalPo ?? 0 }} PO</span></h3>
        <p class="mt-2 font-bold" style="font-size:0.625rem; color: #059669;">
            🛒 {{ $bebanAntreanInfo['jumlah_po_ecommerce'] ?? 0 }} E-Commerce • {{ ($totalPo ?? 0) - ($bebanAntreanInfo['jumlah_po_ecommerce'] ?? 0) }} PO Manual
        </p>
    </div>
</div>

<!-- PANEL PERKIRAAN KAPASITAS PRODUKSI HARIAN & ANALISIS BEBAN ANTREAN -->
<div class="mt-4 no-print" style="background: {{ ($bebanAntreanInfo['is_overload'] ?? false) ? 'linear-gradient(to right, #fef2f2, #fff1f2)' : 'linear-gradient(to right, #f8fafc, #f1f5f9)' }}; border: 1px solid {{ ($bebanAntreanInfo['is_overload'] ?? false) ? '#fecaca' : '#cbd5e1' }}; border-radius: 0.875rem; padding: 0.875rem 1rem;">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div style="font-size: 0.75rem;">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-black" style="color: var(--pc-dark);">🏭 Estimasi Kapasitas Dapur Condet:</span>
                <span class="badge badge-blue">Maks {{ $kapasitasInfo['max_batch_per_hari'] }} Batch / Hari</span>
                <span class="badge badge-blue">Maks {{ number_format($kapasitasInfo['kapasitas_harian_pcs']) }} Pcs Rengginang / Hari</span>
                <span style="font-size: 0.6875rem; color: var(--text-muted);">
                    (Rata-rata riil QC: <strong>{{ number_format($kapasitasInfo['avg_pcs_per_batch_aktual']) }} Pcs/batch</strong>)
                </span>
            </div>
            <p style="margin: 0.35rem 0 0 0; color: {{ ($bebanAntreanInfo['is_overload'] ?? false) ? '#991b1b' : '#334155' }}; font-size: 0.6875rem;">
                @if(($bebanAntreanInfo['total_antrean_pcs'] ?? 0) === 0)
                    ✅ <strong>Antrean Produksi Kosong:</strong> Seluruh pesanan PO pelanggan sudah terjadwal atau siap potong stok.
                @elseif($bebanAntreanInfo['is_overload'] ?? false)
                    ⚠️ <strong>Peringatan Beban Padat:</strong> Total kekurangan PO aktif mencapai <strong>{{ number_format($bebanAntreanInfo['total_antrean_pcs']) }} Pcs ({{ $bebanAntreanInfo['total_antrean_batch'] }} Batch)</strong> atau butuh <strong>{{ $bebanAntreanInfo['estimasi_hari_kerja'] }} hari kerja penuh</strong>, sedangkan jatuh tempo terdekat tinggal <strong>{{ max(0, $bebanAntreanInfo['deadline_terdekat_hari']) }} hari</strong>. Ikuti urutan prioritas algoritma di bawah!
                @else
                    📊 <strong>Analisis Beban Antrean:</strong> Total pesanan belum terjadwal sebesar <strong>{{ number_format($bebanAntreanInfo['total_antrean_pcs']) }} Pcs ({{ $bebanAntreanInfo['total_antrean_batch'] }} Batch)</strong> dapat diselesaikan dalam estimasi <strong>{{ $bebanAntreanInfo['estimasi_hari_kerja'] }} hari kerja dapur</strong>. Sistem otomatis mengurutkan pesanan bervolume besar & tenggat ketat di posisi teratas.
                @endif
            </p>
        </div>
        <button type="button" onclick="document.getElementById('modal-kapasitas').classList.remove('hidden')" class="btn btn-outline" style="white-space: nowrap; background: #fff;">
            ⚙️ Ubah Kapasitas
        </button>
    </div>
</div>

<!-- TOMBOL NAVIGASI TAB TERPISAH -->
<div class="tab-navigation-bar no-print">
    <button type="button" onclick="switchTab('daftar')" id="tab-btn-daftar" class="tab-nav-btn active">
        📋 Daftar Purchase Order ({{ count($poList ?? []) }})
    </button>
    <button type="button" onclick="switchTab('riwayat')" id="tab-btn-riwayat" class="tab-nav-btn">
        📜 Riwayat Audit Log ({{ count($auditLogs ?? []) }})
    </button>
</div>

@php
    $poModalMap = [];
@endphp

<!-- TAB 1: DAFTAR PURCHASE ORDER PELANGGAN -->
<div id="tab-content-daftar">
    <!-- BAR FILTER KANAL/JENIS PO, URGENSI, & PENGURUTAN ALGORITMA -->
    <div class="no-print" style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 0.875rem; padding: 0.75rem 1rem; margin-bottom: 0.875rem;">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
            <div>
                <label class="font-bold block" style="font-size: 0.625rem; color: var(--text-muted); margin-bottom: 0.2rem;">FILTER KANAL & JENIS PO</label>
                <select id="filter-kanal" onchange="applyPoFilters()" class="form-input font-bold" style="margin:0; background:#fff; font-size: 0.75rem;">
                    <option value="ALL">Semua Kanal & Jenis PO</option>
                    <option value="ECOMMERCE">🛒 Pesanan E-Commerce</option>
                    <option value="NON_ECOMMERCE">🏢 PO Manual (Non-E-Commerce)</option>
                    <option value="Konsinyasi">🤝 PO Konsinyasi</option>
                    <option value="Jual Putus">💼 PO Jual Putus</option>
                    <option value="Reseller">🏪 PO Reseller</option>
                    <option value="Eceran">🛍️ PO Eceran</option>
                </select>
            </div>

            <div>
                <label class="font-bold block" style="font-size: 0.625rem; color: var(--text-muted); margin-bottom: 0.2rem;">FILTER STATUS URGENSI</label>
                <select id="filter-urgensi" onchange="applyPoFilters()" class="form-input font-bold" style="margin:0; background:#fff; font-size: 0.75rem;">
                    <option value="ALL">Semua Tingkat Urgensi</option>
                    <option value="MENDESAK">🔥 Semua Mendesak (Kritis + Tinggi)</option>
                    <option value="KRITIS">🔴 KRITIS (Wajib Dahulukan Sekarang)</option>
                    <option value="TINGGI">🟠 PRIORITAS TINGGI (Pesanan Besar / Cicil)</option>
                    <option value="NORMAL">🟢 NORMAL (Kelonggaran Waktu Aman)</option>
                    <option value="SELESAI">✅ SIAP / SELESAI PRODUKSI</option>
                </select>
            </div>

            <div>
                <label class="font-bold block" style="font-size: 0.625rem; color: var(--text-muted); margin-bottom: 0.2rem;">URUTKAN PRIORITAS PENGERJAAN</label>
                <select id="sort-mode" onchange="applyPoFilters()" class="form-input font-bold" style="margin:0; background:#fff; font-size: 0.75rem;">
                    <option value="ALGORITMA">🧠 Rekomendasi Algoritma Prioritas</option>
                    <option value="DEADLINE">📅 Jatuh Tempo Terdekat</option>
                    <option value="VOLUME">📦 Jumlah Pesanan Terbesar</option>
                    <option value="TERBARU">🕒 Nomor PO Terbaru</option>
                </select>
            </div>

            <div>
                <label class="font-bold block" style="font-size: 0.625rem; color: var(--text-muted); margin-bottom: 0.2rem;">CARI NO. PO / PELANGGAN / E-COMMERCE</label>
                <div class="flex items-center gap-1">
                    <input type="text" id="filter-search" onkeyup="applyPoFilters()" placeholder="Ketik nama toko / PO-00X..." class="form-input" style="margin:0; font-size: 0.75rem;">
                    <button type="button" onclick="resetPoFilters()" class="btn btn-outline" style="padding: 0.45rem 0.6rem; font-size: 0.6875rem;" title="Reset Filter">↺</button>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-between items-center mt-2 pt-2" style="border-top: 1px dashed #e5e7eb; font-size: 0.6875rem; color: var(--text-muted);">
            <span id="filter-result-counter">Menampilkan <strong>{{ count($poList) }}</strong> dari <strong>{{ count($poList) }}</strong> pesanan PO</span>
            <span>🔒 <em>Status PO berjalan otomatis satu arah (Pending → Proses → Stok Dipotong → Dikirim → Selesai).</em></span>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header-bar">
            <span class="font-bold" style="color: var(--pc-dark);">📋 Daftar Purchase Order (PO) & Antrean Prioritas Produksi</span>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table" id="table-po-produk">
                <thead>
                    <tr>
                        <th style="white-space: nowrap;">PRIORITAS & NO. PO</th>
                        <th>PELANGGAN & KANAL</th>
                        <th>RINGKASAN PESANAN & PROGRES</th>
                        <th>ANALISIS KAPASITAS & URGENSI</th>
                        <th class="text-center" style="white-space: nowrap;">TANGGAL & TEMPO</th>
                        <th class="text-center">STATUS OTOMATIS</th>
                        <th class="text-center no-print">AKSI EKSEKUSI</th>
                    </tr>
                </thead>
                <tbody id="tbody-po-produk">
                    @forelse($poList as $po)
                    @php
                        $analysis = $poAnalysis[$po->no_po_produk];
                        $isSemuaItemSelesai = $analysis['is_semua_item_siap'];
                        $hasPendingBatch = $analysis['has_pending_batch'];
                        $pendingBatchCodes = $analysis['pending_batch_codes'];
                        $hasilGorengPerProduk = $analysis['hasil_goreng_qc'];
                        $totalTargetKeseluruhan = $analysis['total_target'];
                        $totalSiapKeseluruhan = $analysis['total_siap'];
                        $isEcommerce = $analysis['is_ecommerce'];
                        $urgensiLevel = $analysis['urgensi_level'];

                        $isStatusSudahLanjut = in_array($po->status_po, ['STOK_DIPOTONG', 'DIKIRIM', 'SELESAI']);
                        $detailItemsForModal = [];

                        foreach($po->detailAlokasi as $alokasi) {
                            $idProd = $alokasi->id_produk_jadi;
                            $targetQty = (int) $alokasi->jml_alokasi;
                            $sudahGorengQc = (int) ($hasilGorengPerProduk[$idProd] ?? 0);
                            $stokGudangFisik = max(0, (int) ($alokasi->produkJadi->stok ?? 0));

                            if ($isStatusSudahLanjut) {
                                $totalSiapItem = $targetQty;
                                $isItemCukup = true;
                            } else {
                                $totalSiapItem = min($targetQty, $stokGudangFisik);
                                $isItemCukup = (!$hasPendingBatch) && ($stokGudangFisik >= $targetQty);
                            }

                            $persenItem = $targetQty > 0 ? min(100, round(($totalSiapItem / $targetQty) * 100)) : 0;

                            $detailItemsForModal[] = [
                                'nama_produk' => $alokasi->produkJadi->nama_produk ?? 'Produk',
                                'varian'      => $alokasi->produkJadi->varian ?? '-',
                                'target_qty'  => $targetQty,
                                'stok_fisik'  => (int) ($alokasi->produkJadi->stok ?? 0),
                                'sudah_goreng'=> $sudahGorengQc,
                                'total_siap'  => $totalSiapItem,
                                'kurang'      => max(0, $targetQty - $totalSiapItem),
                                'persen'      => $persenItem,
                                'is_cukup'    => $isItemCukup,
                                'has_pending' => $hasPendingBatch,
                            ];
                        }

                        $persenKeseluruhan = $totalTargetKeseluruhan > 0
                            ? min(100, round(($totalSiapKeseluruhan / $totalTargetKeseluruhan) * 100))
                            : 0;

                        $canDelete = !$po->suratJalan && !in_array($po->status_po, ['DIKIRIM', 'SELESAI']);

                        $poModalMap[$po->no_po_produk] = [
                            'id'          => $po->no_po_produk,
                            'kode_po'     => 'PO-00' . $po->no_po_produk,
                            'pelanggan'   => $po->nama_pelanggan,
                            'penerima'    => $po->nama_penerima ?? '-',
                            'alamat'      => $po->alamat_pelanggan ?? '-',
                            'tipe_jual'   => $po->tipe_jual,
                            'ecom_ref'    => $po->nomor_pesanan_ecommerce ?? ($po->no_ref ?? null),
                            'tgl_po'      => \Carbon\Carbon::parse($po->tgl_po ?? $po->created_at)->format('d M Y'),
                            'jatuh_tempo' => \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->format('d M Y'),
                            'status_po'   => $po->status_po,
                            'sj_ref'      => $po->suratJalan ? '#' . $po->suratJalan->no_ref : null,
                            'total_target'=> $totalTargetKeseluruhan,
                            'total_siap'  => $totalSiapKeseluruhan,
                            'persen_total'=> $persenKeseluruhan,
                            'items'       => $detailItemsForModal,
                        ];

                        $searchText = strtolower('po-00' . $po->no_po_produk . ' ' . $po->nama_pelanggan . ' ' . ($po->nama_penerima ?? '') . ' ' . $po->tipe_jual . ' ' . ($po->nomor_pesanan_ecommerce ?? ''));
                    @endphp
                    <tr class="po-data-row"
                        data-po-id="{{ $po->no_po_produk }}"
                        data-kanal="{{ $isEcommerce ? 'ECOMMERCE' : 'NON_ECOMMERCE' }}"
                        data-tipe-jual="{{ $po->tipe_jual }}"
                        data-urgensi="{{ $urgensiLevel }}"
                        data-rank="{{ $analysis['rekomendasi_rank'] ?? (10000 + $po->no_po_produk) }}"
                        data-deadline="{{ \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->timestamp }}"
                        data-volume="{{ $po->jumlah_po }}"
                        data-search="{{ $searchText }}"
                        style="{{ $urgensiLevel === 'KRITIS' ? 'background-color: #fffafb;' : '' }}">

                        <td class="font-bold" style="white-space: nowrap;">
                            @if($analysis['rekomendasi_rank'] !== null)
                                <div style="margin-bottom: 0.25rem;">
                                    <span class="badge" style="background-color: {{ $urgensiLevel === 'KRITIS' ? '#dc2626' : ($urgensiLevel === 'TINGGI' ? '#ea580c' : '#475569') }}; color: #fff; font-size: 0.5625rem;">
                                        #{{ $analysis['rekomendasi_rank'] }} PRIORITAS
                                    </span>
                                </div>
                            @endif
                            PO-00{{ $po->no_po_produk }}
                            @if($po->suratJalan)
                                <p style="font-size:0.625rem; color: var(--text-muted); margin:0;">SJ Ref: #{{ $po->suratJalan->no_ref }}</p>
                            @endif
                        </td>

                        <td>
                            <p class="font-black" style="color: var(--pc-dark); margin:0; font-size:0.875rem;">{{ $po->nama_pelanggan }}</p>
                            <p class="font-bold" style="font-size:0.625rem; color: #4b5563; margin:0.125rem 0 0 0;">👤 Penerima: {{ $po->nama_penerima ?? '-' }}</p>
                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                @if($isEcommerce)
                                    <span class="badge badge-blue" title="Pesanan Kanal E-Commerce">
                                        🛒 E-Commerce {{ $po->nomor_pesanan_ecommerce ? '(' . $po->nomor_pesanan_ecommerce . ')' : '' }}
                                    </span>
                                @else
                                    <span class="badge" style="background-color:#f3f4f6; color:#374151;">
                                        🏢 {{ $po->tipe_jual }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- KOLOM RINGKASAN PESANAN -->
                        <td>
                            <div class="item-box" style="margin-bottom:0; max-width: 17.5rem;">
                                <div class="flex justify-between items-center gap-2" style="font-size:0.75rem;">
                                    <span class="font-black" style="color: var(--pc-dark);">
                                        📦 {{ $po->detailAlokasi->count() }} Varian ({{ number_format($po->jumlah_po) }} Pcs)
                                    </span>
                                    @if($isSemuaItemSelesai)
                                        <span class="badge badge-emerald">
                                            ✅ SIAP ({{ number_format($totalSiapKeseluruhan) }}/{{ number_format($totalTargetKeseluruhan) }})
                                        </span>
                                    @elseif($hasPendingBatch)
                                        <span class="badge badge-amber">
                                            ⏳ MENUNGGU QC
                                        </span>
                                    @else
                                        <span class="badge badge-amber">
                                            🔥 KURANG {{ number_format($analysis['butuh_produksi_pcs']) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill {{ $isSemuaItemSelesai ? 'bg-emerald-fill' : 'bg-orange-fill' }}"
                                         style="width: {{ $persenKeseluruhan }}%"></div>
                                </div>

                                <div class="flex justify-between items-center" style="font-size:0.625rem;">
                                    <span style="color: var(--text-muted);">Stok Siap: <strong>{{ $persenKeseluruhan }}%</strong></span>
                                    <button type="button" onclick="openDetailPoModal({{ $po->no_po_produk }})"
                                            style="background:none; border:none; padding:0; color: var(--pc-maroon); font-weight:800; cursor:pointer; text-decoration:underline; font-size:0.625rem;">
                                        👁️ Detail Item →
                                    </button>
                                </div>
                            </div>
                        </td>

                        <!-- KOLOM ANALISIS KAPASITAS & ALGORITMA URGENSI -->
                        <td style="max-width: 17rem;">
                            @if($urgensiLevel === 'KRITIS')
                                <span class="badge" style="background-color: #fee2e2; color: #dc2626; font-weight: 900;">
                                    🔴 {{ $analysis['urgensi_label'] }}
                                </span>
                            @elseif($urgensiLevel === 'TINGGI')
                                <span class="badge" style="background-color: #ffedd5; color: #c2410c; font-weight: 900;">
                                    🟠 {{ $analysis['urgensi_label'] }}
                                </span>
                            @elseif($urgensiLevel === 'NORMAL')
                                <span class="badge badge-emerald">
                                    🟢 {{ $analysis['urgensi_label'] }}
                                </span>
                            @else
                                <span class="badge badge-blue">
                                    ✅ {{ $analysis['urgensi_label'] }}
                                </span>
                            @endif

                            @if($urgensiLevel !== 'SELESAI')
                                <p class="font-bold" style="font-size: 0.6875rem; color: var(--pc-dark); margin: 0.25rem 0 0 0;">
                                    🍳 Butuh: <strong>{{ $analysis['butuh_batch'] }} Batch</strong> (±{{ $analysis['butuh_hari'] }} Hari Dapur)
                                </p>
                                <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0; line-height: 1.3;">
                                    {{ $analysis['alasan_prioritas'] }}
                                </p>
                                @if($analysis['estimasi_selesai_tgl'])
                                    <p style="font-size: 0.5625rem; font-weight: bold; color: {{ $analysis['is_berisiko_telat'] ? '#dc2626' : '#059669' }}; margin: 0.125rem 0 0 0;">
                                        Est. Selesai Antrean: {{ $analysis['estimasi_selesai_tgl'] }} {{ $analysis['is_berisiko_telat'] ? '⚠️' : '✓' }}
                                    </p>
                                @endif
                            @else
                                <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.25rem 0 0 0;">
                                    {{ $analysis['alasan_prioritas'] }}
                                </p>
                            @endif
                        </td>

                        <td class="text-center font-bold" style="white-space: nowrap;">
                            <p style="color: var(--pc-dark); margin:0; font-size:0.75rem;">
                                PO: {{ \Carbon\Carbon::parse($po->tgl_po ?? $po->created_at)->format('d M Y') }}
                            </p>
                            <p style="font-size: 0.625rem; color: {{ $analysis['sisa_hari_tempo'] <= 1 && !$isStatusSudahLanjut ? '#dc2626' : 'var(--text-muted)' }}; margin: 0.125rem 0 0 0;">
                                Tempo: {{ \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->format('d M Y') }}
                                @if(!$isStatusSudahLanjut)
                                    ({{ $analysis['sisa_hari_tempo'] < 0 ? 'Telat ' . abs($analysis['sisa_hari_tempo']) . 'h' : ($analysis['sisa_hari_tempo'] === 0 ? 'Hari ini' : 'H-' . $analysis['sisa_hari_tempo']) }})
                                @endif
                            </p>
                        </td>

                        <!-- STATUS OTOMATIS KESELURUHAN PO -->
                        <td class="text-center" style="white-space: nowrap;">
                            @if($po->status_po === 'SELESAI')
                                <span class="badge badge-emerald">✅ SELESAI</span>
                            @elseif($po->status_po === 'DIKIRIM')
                                <span class="badge badge-purple">🚚 DIKIRIM</span>
                            @elseif($po->status_po === 'STOK_DIPOTONG')
                                <span class="badge badge-blue">📦 STOK DIPOTONG</span>
                            @elseif($hasPendingBatch)
                                <span class="badge badge-amber">🔥 PROSES DAPUR</span>
                                <p style="font-size: 0.5625rem; color: #b45309; font-weight: bold; margin: 0.125rem 0 0 0;">
                                    {{ $pendingBatchCodes }} (Belum QC)
                                </p>
                            @elseif($isSemuaItemSelesai)
                                <span class="badge badge-emerald">✓ SEMUA ITEM SIAP</span>
                            @else
                                <span class="badge badge-amber">⏳ {{ $po->status_po === 'PROSES' ? 'PROSES (BUTUH BATCH)' : 'MENUNGGU PRODUKSI' }}</span>
                            @endif
                        </td>

                        <!-- AKSI EKSEKUSI TERKENDALI -->
                        <td class="text-center no-print" style="white-space: nowrap;">
                            <div class="flex flex-col gap-1 items-center">
                                @if($po->status_po !== 'SELESAI' && $po->status_po !== 'DIKIRIM')
                                    @if($hasPendingBatch && $po->status_po !== 'STOK_DIPOTONG')
                                        <a href="{{ route('log-produksi.index') }}" class="btn" style="width:100%; justify-content:center; background-color: #fffbeb; color: #b45309; border: 1px solid #fcd34d;" title="Selesaikan batch & input QC di Log Produksi terlebih dahulu">
                                            ⏳ Menunggu Selesai QC
                                        </a>
                                    @elseif($isSemuaItemSelesai && $po->status_po !== 'STOK_DIPOTONG')
                                        <form action="{{ route('po-produk.potong-stok', $po->no_po_produk) }}" method="POST" style="width:100%;">
                                            @csrf
                                            <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                                                ✂️ Potong Stok Ready
                                            </button>
                                        </form>
                                    @elseif($po->status_po === 'STOK_DIPOTONG' && !$po->suratJalan)
                                        <button onclick="openModalSj('{{ $po->no_po_produk }}', '{{ addslashes($po->nama_penerima ?? $po->nama_pelanggan) }}')" class="btn btn-info" style="width:100%; justify-content:center;">
                                            📄 Terbitkan Surat Jalan
                                        </button>
                                    @elseif($po->suratJalan)
                                        <span class="font-bold" style="font-size:0.625rem; color: #059669;">✓ SJ Sudah Terbit</span>
                                    @else
                                        <a href="{{ route('po-produk.buat-batch', $po->no_po_produk) }}" class="btn btn-primary" style="width:100%; justify-content:center;">
                                            🍳 Buat Batch Produksi
                                        </a>
                                    @endif
                                @endif

                                <div class="flex items-center justify-center gap-1" style="width:100%;">
                                    <button type="button" onclick="openDetailPoModal({{ $po->no_po_produk }})" class="btn btn-outline" style="flex:1; justify-content:center; padding: 0.25rem 0.5rem;" title="Lihat Detail Pesanan PO">
                                        👁️ Detail
                                    </button>
                                    <button type="button"
                                            onclick="openDeletePoModal('{{ $po->no_po_produk }}', '{{ addslashes($po->nama_pelanggan) }}', '{{ $po->status_po }}', {{ $canDelete ? 'true' : 'false' }})"
                                            class="btn"
                                            style="flex:1; justify-content:center; background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.25rem 0.5rem;"
                                            title="Hapus Pesanan PO">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada pesanan PO pelanggan yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: RIWAYAT AUDIT LOG PO PELANGGAN -->
<div id="tab-content-riwayat" class="hidden">
    <div class="table-container">
        <div class="table-header-bar flex justify-between items-center">
            <span class="font-bold" style="color: var(--pc-dark);">📜 Riwayat Audit Log PO Pelanggan (Input, Kapasitas Dapur, Alokasi Stok, & Penghapusan)</span>
            <span style="font-size: 0.6875rem; color: var(--text-muted);">Tercatat Otomatis per Akun & Role</span>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>WAKTU (DATETIME)</th>
                        <th>AKTOR & ROLE</th>
                        <th class="text-center">AKSI</th>
                        <th>NO. PO & PELANGGAN</th>
                        <th>DETAIL AKTIVITAS</th>
                        <th>ALASAN / KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                    <tr>
                        <td class="font-bold" style="font-size: 0.75rem; white-space: nowrap;">
                            🕒 {{ $log['datetime'] ?? '-' }}
                        </td>
                        <td>
                            <p class="font-black" style="margin:0; font-size: 0.75rem; color: var(--pc-dark);">{{ $log['user_name'] ?? 'System' }}</p>
                            <span class="badge" style="background-color: #f3f4f6; color: var(--pc-maroon); font-size: 0.5625rem; margin-top: 0.125rem;">
                                👤 {{ $log['role'] ?? 'STAFF' }}
                            </span>
                        </td>
                        <td class="text-center" style="white-space: nowrap;">
                            @if(($log['aksi'] ?? '') === 'ADD PO')
                                <span class="badge badge-emerald">+ ADD PO</span>
                            @elseif(($log['aksi'] ?? '') === 'DELETE PO')
                                <span class="badge" style="background-color: #fee2e2; color: #dc2626;">🗑️ DELETE PO</span>
                            @elseif(($log['aksi'] ?? '') === 'POTONG STOK')
                                <span class="badge badge-blue">✂️ POTONG STOK</span>
                            @elseif(($log['aksi'] ?? '') === 'KAPASITAS DAPUR')
                                <span class="badge badge-purple">⚙️ KAPASITAS DAPUR</span>
                            @else
                                <span class="badge badge-amber">✏️ {{ $log['aksi'] ?? 'UPDATE' }}</span>
                            @endif
                        </td>
                        <td>
                            <p class="font-black" style="margin:0; font-size: 0.75rem; color: var(--pc-maroon);">{{ $log['kode_po'] ?? '-' }}</p>
                            <p style="margin:0.125rem 0 0 0; font-size: 0.6875rem; color: var(--pc-dark);">{{ $log['pelanggan'] ?? '-' }}</p>
                        </td>
                        <td style="font-size: 0.75rem; color: #374151;">{{ $log['detail'] ?? '-' }}</td>
                        <td style="font-size: 0.6875rem; color: var(--text-muted); font-style: italic;">{{ $log['alasan'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada riwayat audit aktivitas pada modul PO Pelanggan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PENGATURAN PERKIRAAN KAPASITAS PRODUKSI HARIAN DAPUR -->
<div id="modal-kapasitas" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 30rem;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <div>
                <h3 class="font-black text-sm" style="margin:0; color: var(--pc-dark);">⚙️ Perkiraan Kapasitas Produksi Harian Dapur</h3>
                <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0;">Digunakan algoritma untuk menghitung estimasi hari produksi & prioritas PO.</p>
            </div>
            <button onclick="document.getElementById('modal-kapasitas').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('po-produk.kapasitas') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.75rem; padding: 0.75rem; font-size: 0.6875rem; color: #1e40af; margin-bottom: 0.75rem;">
                📊 <strong>Referensi Historis Log Produksi:</strong><br>
                • Rata-rata hasil lolos QC per batch: <strong>{{ number_format($kapasitasInfo['avg_pcs_per_batch_aktual']) }} Pcs/batch</strong><br>
                • Rekor produksi tertinggi dalam 1 hari: <strong>{{ number_format($kapasitasInfo['max_harian_aktual']) }} Pcs/hari</strong>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="font-bold">Maks. Batch per Hari *</label>
                    <input type="number" id="input-max-batch" name="max_batch_per_hari" value="{{ $kapasitasInfo['max_batch_per_hari'] }}" min="1" max="50" required oninput="hitungPreviewKapasitas()" class="form-input font-black">
                </div>
                <div>
                    <label class="font-bold">Maks. Rengginang / Batch (Pcs) *</label>
                    <input type="number" id="input-max-pcs" name="max_pcs_per_batch" value="{{ $kapasitasInfo['max_pcs_per_batch'] }}" min="10" max="5000" required oninput="hitungPreviewKapasitas()" class="form-input font-black">
                </div>
            </div>

            <div class="mt-2" style="padding: 0.625rem 0.75rem; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem; font-size: 0.75rem; color: #065f46;">
                ⚡ <strong>Kapasitas Maksimal Harian:</strong> <span id="preview-kapasitas-harian" class="font-black">{{ number_format($kapasitasInfo['kapasitas_harian_pcs']) }}</span> Pcs / Hari
            </div>

            <div class="mt-2">
                <label class="font-bold">Catatan Fasilitas / Kuali Dapur</label>
                <input type="text" name="catatan_kapasitas" value="{{ $kapasitasInfo['catatan_kapasitas'] }}" placeholder="Misal: 2 Kuali Utama (Shift Pagi & Siang)" class="form-input">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-kapasitas').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kapasitas Dapur</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL POP-UP DETAIL PO LENGKAP -->
<div id="modal-detail-po" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 38rem; max-height: 90vh; overflow-y: auto;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <div>
                <h3 class="font-black text-sm" id="detail-po-title" style="margin:0; color: var(--pc-maroon);">Detail Purchase Order</h3>
                <p id="detail-po-subtitle" style="font-size: 0.6875rem; color: var(--text-muted); margin: 0.125rem 0 0 0;"></p>
            </div>
            <button onclick="document.getElementById('modal-detail-po').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <!-- Indikator Tahapan Otomatis Satu Arah -->
        <div id="detail-po-pipeline" class="flex flex-wrap items-center justify-between gap-1 mt-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.5rem 0.75rem; font-size: 0.625rem;">
        </div>

        <div style="background-color: #f9fafb; border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 0.75rem; margin-top: 0.75rem; font-size: 0.75rem;">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.625rem;">PELANGGAN / TOKO</span>
                    <p id="detail-po-pelanggan" class="font-black" style="margin:0; color: var(--pc-dark);"></p>
                </div>
                <div>
                    <span style="color: var(--text-muted); font-size: 0.625rem;">PENERIMA BARANG</span>
                    <p id="detail-po-penerima" class="font-bold" style="margin:0; color: var(--pc-dark);"></p>
                </div>
                <div>
                    <span style="color: var(--text-muted); font-size: 0.625rem;">TANGGAL PO & JATUH TEMPO</span>
                    <p id="detail-po-tanggal" class="font-bold" style="margin:0; color: var(--pc-dark);"></p>
                </div>
                <div>
                    <span style="color: var(--text-muted); font-size: 0.625rem;">TIPE JUAL & REFERENSI</span>
                    <p id="detail-po-tipe" class="font-bold" style="margin:0; color: var(--pc-dark);"></p>
                </div>
            </div>
            <div class="mt-2" style="border-top: 1px dashed var(--border-color); padding-top: 0.5rem;">
                <span style="color: var(--text-muted); font-size: 0.625rem;">ALAMAT PENGIRIMAN LENGKAP</span>
                <p id="detail-po-alamat" style="margin:0.125rem 0 0 0; color: #374151;"></p>
            </div>
        </div>

        <div class="table-responsive-wrapper mt-3">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>VARIAN PRODUK</th>
                        <th class="text-center">TARGET PO</th>
                        <th class="text-center">STOK GUDANG & HASIL QC</th>
                        <th>PROGRES & STATUS</th>
                    </tr>
                </thead>
                <tbody id="detail-po-items-tbody">
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-4">
            <button type="button" onclick="document.getElementById('modal-detail-po').classList.add('hidden')" class="btn btn-outline">Tutup Detail</button>
        </div>
    </div>
</div>

<!-- MODAL POP-UP KONFIRMASI HAPUS PO -->
<div id="modal-delete-po" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0; color: #dc2626;">🗑️ Konfirmasi Hapus Pesanan PO</h3>
            <button onclick="document.getElementById('modal-delete-po').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-delete-po" method="POST" style="margin-top: 1rem;">
            @csrf
            @method('DELETE')

            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 0.625rem; padding: 0.75rem; font-size: 0.75rem; color: #991b1b;">
                <p style="margin:0;">Anda akan menghapus pesanan berikut secara permanen:</p>
                <p id="delete-po-title" class="font-black" style="font-size: 0.875rem; margin: 0.25rem 0;"></p>
                <p id="delete-po-warning" style="margin: 0.25rem 0 0 0; font-size: 0.6875rem;"></p>
            </div>

            <div class="mt-3">
                <label class="font-bold">Kategori Alasan Penghapusan *</label>
                <select name="kategori_alasan" id="delete-kategori-alasan" required class="form-input font-bold" style="background:#fff;">
                    <option value="">-- Pilih Alasan Penghapusan --</option>
                    <option value="Salah Input Data">Salah Input Data (Produk / Qty / Pelanggan)</option>
                    <option value="Pembatalan Pelanggan">Pembatalan Pesanan oleh Pelanggan</option>
                    <option value="Duplikasi PO">Duplikasi / Dobel Input PO</option>
                    <option value="Revisi Order Baru">Ganti dengan Nomor PO Baru</option>
                    <option value="Lainnya">Alasan Lainnya</option>
                </select>
            </div>

            <div class="mt-2">
                <label class="font-bold">Keterangan Detail Alasan (Wajib untuk Audit Log) *</label>
                <textarea name="detail_alasan" id="delete-detail-alasan" rows="2" required placeholder="Jelaskan alasan penghapusan secara spesifik..." class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-delete-po').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" id="btn-submit-delete-po" class="btn btn-primary" style="background-color: #dc2626; border-color: #dc2626;">Ya, Hapus PO</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Terbitkan Surat Jalan -->
<div id="modal-sj" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Terbitkan Surat Jalan</h3>
            <button onclick="document.getElementById('modal-sj').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('shipments.surat-jalan.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <input type="hidden" name="no_po_produk" id="sj-no-po">
            <input type="hidden" name="nama_penerima" id="sj-nama-penerima">

            <div>
                <label class="font-bold">Tanggal Terbit Surat Jalan</label>
                <input type="date" name="tgl_terbit" value="{{ date('Y-m-d') }}" required class="form-input font-bold">
            </div>

            <div class="mt-2">
                <label class="font-bold">Nama Kurir / Pengirim</label>
                <input type="text" name="nama_kurir" required placeholder="Misal: Pak Maman (Kurir Condet)" class="form-input">
            </div>

            <div class="mt-2">
                <label class="font-bold">Keterangan / Catatan Pengiriman (Opsional)</label>
                <textarea name="ket" rows="2" placeholder="Misal: Kirim via Mobil Box / Titip Security" class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-sj').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-info">Simpan & Terbitkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Buat PO Pelanggan Baru -->
<div id="modal-po" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 32rem; max-height: 90vh; overflow-y: auto;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Input Pesanan PO Pelanggan Baru (Multi-Item)</h3>
            <button onclick="document.getElementById('modal-po').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('po-produk.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="font-bold">Nama Pelanggan / Toko</label>
                    <input type="text" name="nama_pelanggan" required placeholder="Misal: HERO Super Market" class="form-input">
                </div>
                <div>
                    <label class="font-bold">Nama Penerima Barang</label>
                    <input type="text" name="nama_penerima" required placeholder="Misal: Pak Budi (Gudang)" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-2">
                <div>
                    <label class="font-bold">Tipe / Kanal Penjualan</label>
                    <select name="tipe_jual" id="input-tipe-jual-po" onchange="toggleEcomRefField()" required class="form-input font-bold" style="background:#fff;">
                        <option value="Konsinyasi">Konsinyasi</option>
                        <option value="Jual Putus">Jual Putus</option>
                        <option value="Reseller">Reseller</option>
                        <option value="Eceran">Eceran</option>
                        <option value="E-Commerce">E-Commerce</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold">Tanggal PO</label>
                    <input type="date" name="tgl_po" value="{{ date('Y-m-d') }}" required class="form-input">
                </div>
                <div>
                    <label class="font-bold">Jatuh Tempo</label>
                    <input type="date" name="tgl_jatuh_tempo_po" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required class="form-input">
                </div>
            </div>

            <div id="box-ecom-ref" class="mt-2 hidden">
                <label class="font-bold">Nomor Pesanan E-Commerce (Shopee / Tokopedia / TikTok)</label>
                <input type="text" name="nomor_pesanan_ecommerce" placeholder="Misal: SHP-20261113-8891" class="form-input">
            </div>

            <div class="mt-2" style="border: 1px solid var(--border-color); border-radius: 1rem; padding: 0.75rem; background-color: rgba(249,250,251,0.5);">
                <div class="flex justify-between items-center" style="margin-bottom: 0.5rem;">
                    <span class="font-black" style="color: var(--pc-dark); font-size:0.75rem;">Daftar Produk Dipesan:</span>
                    <button type="button" onclick="addProdukRow()" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.5625rem;">+ Tambah Produk</button>
                </div>

                <div id="produk-row-container" class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 produk-item">
                        <select name="produk_ids[]" required class="form-input font-bold" style="width: 65%; margin:0; background:#fff;">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id_produk_jadi }}">{{ $prod->nama_produk }} (Stok: {{ $prod->stok }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="produk_qty[]" required min="1" placeholder="Qty (Pcs)" class="form-input" style="width: 30%; margin:0;">
                        <button type="button" onclick="removeProdukRow(this)" style="color: #ef4444; font-weight: bold; background: none; border: none; cursor: pointer;">✕</button>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <label class="font-bold">Alamat Pengiriman Lengkap</label>
                <textarea name="alamat_pelanggan" required rows="2" placeholder="Jl. Raya Condet No. XX, Jakarta" class="form-input"></textarea>
            </div>

            <div class="mt-3 flex items-center gap-2" style="background-color: #fef2f2; padding: 0.625rem; border-radius: 0.5rem; border: 1px solid #fecaca;">
                <input type="checkbox" id="buat_batch_langsung" name="buat_batch_langsung" value="1" checked style="width: 1rem; height: 1rem; accent-color: var(--pc-maroon); cursor: pointer;">
                <label for="buat_batch_langsung" class="font-bold" style="font-size: 0.75rem; color: #991b1b; cursor: pointer;">
                    🔥 Langsung arahkan ke penjadwalan Batch Produksi di Dapur (Status jadi PROSES)
                </label>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-po').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pesanan PO</button>
            </div>
        </form>
    </div>
</div>

<script>
const poDataMap = @json($poModalMap);

function hitungPreviewKapasitas() {
    const batch = parseInt(document.getElementById('input-max-batch').value || 0, 10);
    const pcs = parseInt(document.getElementById('input-max-pcs').value || 0, 10);
    const total = batch * pcs;
    document.getElementById('preview-kapasitas-harian').innerText = total.toLocaleString('id-ID');
}

function toggleEcomRefField() {
    const tipe = document.getElementById('input-tipe-jual-po').value;
    const box = document.getElementById('box-ecom-ref');
    if (tipe === 'E-Commerce') {
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
    }
}

function applyPoFilters() {
    const kanalFilter = document.getElementById('filter-kanal').value;
    const urgensiFilter = document.getElementById('filter-urgensi').value;
    const sortMode = document.getElementById('sort-mode').value;
    const searchQuery = document.getElementById('filter-search').value.trim().toLowerCase();

    const tbody = document.getElementById('tbody-po-produk');
    const rows = Array.from(tbody.querySelectorAll('tr.po-data-row'));
    const totalAll = rows.length;

    rows.sort((a, b) => {
        if (sortMode === 'ALGORITMA') {
            return parseInt(a.dataset.rank, 10) - parseInt(b.dataset.rank, 10);
        } else if (sortMode === 'DEADLINE') {
            return parseInt(a.dataset.deadline, 10) - parseInt(b.dataset.deadline, 10);
        } else if (sortMode === 'VOLUME') {
            return parseInt(b.dataset.volume, 10) - parseInt(a.dataset.volume, 10);
        } else {
            return parseInt(b.dataset.poId, 10) - parseInt(a.dataset.poId, 10);
        }
    });

    let visibleCount = 0;
    rows.forEach(row => {
        tbody.appendChild(row);

        const rowKanal = row.dataset.kanal;
        const rowTipe = row.dataset.tipeJual;
        const rowUrgensi = row.dataset.urgensi;
        const rowSearch = row.dataset.search;

        let matchKanal = true;
        if (kanalFilter === 'ECOMMERCE') {
            matchKanal = (rowKanal === 'ECOMMERCE');
        } else if (kanalFilter === 'NON_ECOMMERCE') {
            matchKanal = (rowKanal === 'NON_ECOMMERCE');
        } else if (kanalFilter !== 'ALL') {
            matchKanal = (rowTipe.toLowerCase() === kanalFilter.toLowerCase());
        }

        let matchUrgensi = true;
        if (urgensiFilter === 'MENDESAK') {
            matchUrgensi = (rowUrgensi === 'KRITIS' || rowUrgensi === 'TINGGI');
        } else if (urgensiFilter !== 'ALL') {
            matchUrgensi = (rowUrgensi === urgensiFilter);
        }

        let matchSearch = true;
        if (searchQuery !== '') {
            matchSearch = rowSearch.includes(searchQuery);
        }

        if (matchKanal && matchUrgensi && matchSearch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const counterEl = document.getElementById('filter-result-counter');
    if (counterEl) {
        counterEl.innerHTML = `Menampilkan <strong>${visibleCount}</strong> dari <strong>${totalAll}</strong> pesanan PO`;
    }
}

function resetPoFilters() {
    document.getElementById('filter-kanal').value = 'ALL';
    document.getElementById('filter-urgensi').value = 'ALL';
    document.getElementById('sort-mode').value = 'ALGORITMA';
    document.getElementById('filter-search').value = '';
    applyPoFilters();
}

function switchTab(tab) {
    const btnDaftar = document.getElementById('tab-btn-daftar');
    const btnRiwayat = document.getElementById('tab-btn-riwayat');
    const contentDaftar = document.getElementById('tab-content-daftar');
    const contentRiwayat = document.getElementById('tab-content-riwayat');

    if (tab === 'daftar') {
        contentDaftar.classList.remove('hidden');
        contentRiwayat.classList.add('hidden');
        btnDaftar.classList.add('active');
        btnRiwayat.classList.remove('active');
    } else {
        contentDaftar.classList.add('hidden');
        contentRiwayat.classList.remove('hidden');
        btnRiwayat.classList.add('active');
        btnDaftar.classList.remove('active');
    }
}

function openDetailPoModal(poId) {
    const data = poDataMap[poId];
    if (!data) return;

    document.getElementById('detail-po-title').innerText = 'Detail ' + data.kode_po + ' — ' + data.pelanggan;
    document.getElementById('detail-po-subtitle').innerText = 'Status Otomatis: ' + data.status_po + (data.sj_ref ? ' | Surat Jalan: ' + data.sj_ref : '');
    document.getElementById('detail-po-pelanggan').innerText = data.pelanggan;
    document.getElementById('detail-po-penerima').innerText = data.penerima;
    document.getElementById('detail-po-tanggal').innerText = 'PO: ' + data.tgl_po + ' | Tempo: ' + data.jatuh_tempo;
    document.getElementById('detail-po-tipe').innerText = data.tipe_jual + (data.ecom_ref ? ' (' + data.ecom_ref + ')' : '');
    document.getElementById('detail-po-alamat').innerText = data.alamat;

    // Render indikator tahapan satu arah
    const steps = ['PENDING', 'PROSES', 'STOK_DIPOTONG', 'DIKIRIM', 'SELESAI'];
    const currentIdx = steps.indexOf(data.status_po);
    const pipelineEl = document.getElementById('detail-po-pipeline');
    pipelineEl.innerHTML = steps.map((step, idx) => {
        const isDone = idx <= currentIdx;
        const isCurrent = idx === currentIdx;
        const style = isCurrent
            ? 'background-color: var(--pc-maroon); color: #fff; font-weight: 900; padding: 0.2rem 0.45rem; border-radius: 0.375rem;'
            : (isDone
                ? 'color: #059669; font-weight: 800;'
                : 'color: #9ca3af;');
        const arrow = idx < steps.length - 1 ? '<span style="color:#cbd5e1;">→</span>' : '';
        return `<span style="${style}">${idx + 1}. ${step.replace('_', ' ')}</span> ${arrow}`;
    }).join('');

    const tbody = document.getElementById('detail-po-items-tbody');
    tbody.innerHTML = '';

    if (data.items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center" style="color:var(--text-muted); padding:1rem;">Tidak ada rincian item produk.</td></tr>';
    } else {
        data.items.forEach(item => {
            let badgeHtml = '';
            if (item.is_cukup) {
                badgeHtml = `<span class="badge badge-emerald">✅ SIAP (${item.total_siap}/${item.target_qty} Pcs)</span>`;
            } else if (item.has_pending) {
                badgeHtml = `<span class="badge badge-amber">⏳ MENUNGGU QC DAPUR</span>`;
            } else {
                badgeHtml = `<span class="badge badge-amber">🔥 KURANG ${item.kurang} PCS</span>`;
            }

            const barClass = item.is_cukup ? 'bg-emerald-fill' : 'bg-orange-fill';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <p class="font-black" style="margin:0; color:var(--pc-dark);">${item.nama_produk}</p>
                    <p style="margin:0.125rem 0 0 0; font-size:0.625rem; color:var(--text-muted);">Varian: ${item.varian}</p>
                </td>
                <td class="text-center font-black" style="color:var(--pc-maroon);">${item.target_qty} Pcs</td>
                <td class="text-center" style="font-size:0.6875rem;">
                    <div>Fisik Gudang: <strong>${item.stok_fisik} Pcs</strong></div>
                    <div style="color:var(--text-muted);">Lolos QC PO ini: ${item.sudah_goreng} Pcs</div>
                </td>
                <td>
                    ${badgeHtml}
                    <div class="progress-bar-bg" style="margin-top:0.35rem;">
                        <div class="progress-bar-fill ${barClass}" style="width:${item.persen}%"></div>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('modal-detail-po').classList.remove('hidden');
}

function openDeletePoModal(poId, pelanggan, statusPo, canDelete) {
    document.getElementById('delete-po-title').innerText = 'PO-00' + poId + ' — ' + pelanggan + ' (Status: ' + statusPo + ')';
    document.getElementById('form-delete-po').action = '/po-produk/destroy/' + poId;
    document.getElementById('delete-kategori-alasan').value = '';
    document.getElementById('delete-detail-alasan').value = '';

    const warningEl = document.getElementById('delete-po-warning');
    const submitBtn = document.getElementById('btn-submit-delete-po');

    if (!canDelete) {
        warningEl.innerHTML = '⚠️ <strong>Tidak dapat dihapus!</strong> PO ini sudah diterbitkan Surat Jalan atau berstatus <strong>' + statusPo + '</strong>.';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    } else if (statusPo === 'STOK_DIPOTONG') {
        warningEl.innerHTML = 'ℹ️ Status PO saat ini <strong>STOK DIPOTONG</strong>. Jika dihapus, stok produk jadi akan otomatis dikembalikan ke gudang dan tercatat di Audit Log.';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    } else {
        warningEl.innerHTML = '✓ Penghapusan PO akan mencatat waktu, akun Anda, dan alasan penghapusan ke dalam Riwayat Audit Log.';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }

    document.getElementById('modal-delete-po').classList.remove('hidden');
}

function addProdukRow() {
    const container = document.getElementById('produk-row-container');
    const firstRow = container.children[0];
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('input[name="produk_qty[]"]').value = '';
    container.appendChild(newRow);
}

function removeProdukRow(btn) {
    const container = document.getElementById('produk-row-container');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    }
}

function openModalSj(noPo, namaPenerima) {
    document.getElementById('sj-no-po').value = noPo;
    document.getElementById('sj-nama-penerima').value = namaPenerima;
    document.getElementById('modal-sj').classList.remove('hidden');
}
</script>

@endsection