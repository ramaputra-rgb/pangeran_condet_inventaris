@extends('layouts.app')

@section('title', 'Pangeran Condet, Purchase Order')

@section('content')
@php
    $urgCounts = ['kritis' => 0, 'tinggi' => 0, 'normal' => 0, 'selesai' => 0];
    $topPo = null;
    $topAnalysis = null;
    $topRank = PHP_INT_MAX;
    $poModalMap = [];

    foreach ($poList as $idx => $po) {
        $an = $poAnalysis[$po->no_po_produk] ?? [];
        $urgRaw = strtoupper((string) ($an['urgensi_level'] ?? 'NORMAL'));
        $isDone = in_array($po->status_po, ['SELESAI', 'DIKIRIM', 'STOK_DIPOTONG'], true) || $urgRaw === 'SELESAI';

        if ($isDone) {
            $chipKey = 'selesai';
        } elseif ($urgRaw === 'KRITIS') {
            $chipKey = 'kritis';
        } elseif ($urgRaw === 'TINGGI') {
            $chipKey = 'tinggi';
        } else {
            $chipKey = 'normal';
        }
        $urgCounts[$chipKey]++;

        $rank = isset($an['rekomendasi_rank']) && $an['rekomendasi_rank'] !== null
            ? (int) $an['rekomendasi_rank']
            : (1000 + $idx);

        if (!$isDone && $rank < $topRank) {
            $topRank = $rank;
            $topPo = $po;
            $topAnalysis = $an;
        }

        $itemsData = [];
        $totalAlokasiItems = 0;
        foreach ($po->detailAlokasi as $det) {
            $qty = (int) $det->jml_alokasi;
            $totalAlokasiItems += $qty;
            $prod = $det->produkJadi;
            $stokNow = $prod ? (int) $prod->stok : 0;
            $itemsData[] = [
                'nama' => $prod ? $prod->nama_produk : ('Produk #' . $det->id_produk_jadi),
                'meta' => $prod ? ($prod->varian . ' · ' . $prod->netto) : '-',
                'qty' => $qty,
                'stok' => $stokNow,
            ];
        }

        $totalTarget = (int) ($an['total_target'] ?? ($totalAlokasiItems > 0 ? $totalAlokasiItems : $po->jumlah_po));
        $totalSiap = (int) ($an['total_siap'] ?? 0);
        $butuhProduksi = (int) ($an['butuh_produksi_pcs'] ?? max(0, $totalTarget - $totalSiap));
        $kodePo = 'PO-' . str_pad($po->no_po_produk, 4, '0', STR_PAD_LEFT);
        $hasPendingBatch = !empty($an['has_pending_batch']);
        $isReady = !empty($an['is_semua_item_siap']);

        $stepIndex = 1;
        if (in_array($po->status_po, ['DIKIRIM', 'SELESAI'], true) || $po->suratJalan) {
            $stepIndex = 5;
        } elseif ($po->status_po === 'STOK_DIPOTONG') {
            $stepIndex = 4;
        } elseif ($isReady) {
            $stepIndex = 3;
        } elseif ($hasPendingBatch || $po->status_po === 'PROSES') {
            $stepIndex = 2;
        }

        $nextAction = ['label' => '', 'href' => ''];
        if ($po->status_po === 'SELESAI' || $po->status_po === 'DIKIRIM') {
            $nextAction = ['label' => 'Buka halaman pengiriman', 'href' => route('shipments.index')];
        } elseif ($hasPendingBatch) {
            $nextAction = ['label' => 'Selesaikan QC batch di Produksi', 'href' => route('log-produksi.index')];
        } elseif (!$isReady && $po->status_po !== 'STOK_DIPOTONG') {
            $nextAction = ['label' => 'Jadwalkan batch produksi', 'href' => route('po-produk.buat-batch', $po->no_po_produk)];
        }

        $pendingCodes = $an['pending_batch_codes'] ?? [];
        if (is_array($pendingCodes)) {
            $pendingCodesStr = implode(', ', $pendingCodes);
        } else {
            $pendingCodesStr = (string) $pendingCodes;
        }

        $poModalMap[(string) $po->no_po_produk] = [
            'kode' => $kodePo,
            'pelanggan' => (string) $po->nama_pelanggan,
            'penerima' => (string) ($po->nama_penerima ?: $po->nama_pelanggan),
            'alamat' => (string) ($po->alamat_pelanggan ?: '-'),
            'tipe_jual' => (string) $po->tipe_jual,
            'ecommerce_no' => (string) ($po->nomor_pesanan_ecommerce ?: ''),
            'tgl_po' => $po->tgl_po ? \Carbon\Carbon::parse($po->tgl_po)->format('d M Y') : '-',
            'tgl_tempo' => $po->tgl_jatuh_tempo_po ? \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->format('d M Y') : '-',
            'status' => (string) $po->status_po,
            'rank_label' => isset($an['rekomendasi_rank']) && $an['rekomendasi_rank'] !== null
                ? ('Prioritas #' . $an['rekomendasi_rank'])
                : ($an['urgensi_label'] ?? $po->status_po),
            'rank_tone' => $chipKey === 'kritis' ? 'alert' : ($chipKey === 'tinggi' ? 'warm' : 'muted'),
            'kapasitas_text' => ($an['butuh_batch'] ?? 0) . ' batch (~' . ($an['butuh_hari'] ?? 0) . ' hari kerja)',
            'estimasi_selesai' => (string) ($an['estimasi_selesai_tgl'] ?? '-'),
            'alasan' => (string) ($an['alasan_prioritas'] ?? 'Siap diproses sesuai jadwal antrean.'),
            'pending_batches' => $pendingCodesStr,
            'surat_jalan' => $po->suratJalan ? ('SJ-' . str_pad($po->suratJalan->no_ref, 4, '0', STR_PAD_LEFT)) : '',
            'total_target' => $totalTarget,
            'total_siap' => $totalSiap,
            'butuh_produksi' => $butuhProduksi,
            'step' => $stepIndex,
            'items' => $itemsData,
            'next_action' => $nextAction,
        ];
    }

    $auditCount = count($auditLogs ?? []);
    $isOverload = !empty($bebanAntreanInfo['is_overload']);
    $kritisCount = (int) ($bebanAntreanInfo['jumlah_po_kritis'] ?? 0);
@endphp

<section class="workshop" aria-labelledby="j-po">
    <div class="workshop-top">
        <div>
            <span class="stamp-tag {{ ($isOverload || $kritisCount > 0) ? 'alert' : '' }}">
                {{ $isOverload ? 'Antrean melebihi kapasitas' : ($kritisCount > 0 ? $kritisCount . ' PO prioritas kritis' : 'Kapasitas terkendali') }}
            </span>
            <h1 id="j-po">Antrean produksi &amp; Purchase Order</h1>
            <p>Urutan prioritas dihitung otomatis dari tenggat waktu, slack hari kerja, kesiapan stok produk jadi, dan beban goreng harian dapur.</p>
        </div>
        <div class="acts">
            <button type="button" class="btn" onclick="openDialog('modal-po')">+ Tambah PO baru</button>
            <button type="button" class="btn outline" onclick="openDialog('modal-kapasitas')">Atur kapasitas ({{ number_format($kapasitasInfo['max_batch_per_hari'] ?? 0) }}&times;{{ number_format($kapasitasInfo['max_pcs_per_batch'] ?? 0) }})</button>
        </div>
    </div>

    <div class="workshop-rail" aria-label="Ringkasan beban PO">
        <div class="rail-cell">
            <div class="rail-lbl"><span class="rail-dot {{ (($poPendingCount ?? 0) + ($poProsesCount ?? 0)) > 0 ? 'mid' : 'ok' }}"></span> Total PO aktif</div>
            <strong>{{ number_format(($poPendingCount ?? 0) + ($poProsesCount ?? 0)) }}</strong>
            <span>dari {{ number_format($totalPo ?? $poList->count()) }} PO &middot; {{ number_format($poSelesaiCount ?? 0) }} selesai</span>
        </div>
        <div class="rail-cell">
            <div class="rail-lbl"><span class="rail-dot {{ $kritisCount > 0 ? 'warn' : 'ok' }}"></span> PO kritis &amp; tinggi</div>
            <strong>{{ number_format($kritisCount) }}</strong>
            <span>{{ number_format($bebanAntreanInfo['jumlah_po_tinggi'] ?? 0) }} tinggi &middot; {{ number_format($bebanAntreanInfo['jumlah_po_ecommerce'] ?? 0) }} E-Commerce</span>
        </div>
        <div class="rail-cell">
            <div class="rail-lbl"><span class="rail-dot {{ $isOverload ? 'warn' : 'mid' }}"></span> Beban antrean dapur</div>
            <strong>{{ number_format($bebanAntreanInfo['total_antrean_pcs'] ?? 0) }}</strong>
            <span>{{ number_format($bebanAntreanInfo['total_antrean_batch'] ?? 0) }} batch (~{{ $bebanAntreanInfo['estimasi_hari_kerja'] ?? 0 }} hari kerja)</span>
        </div>
        <div class="rail-cell">
            <div class="rail-lbl"><span class="rail-dot ok"></span> Kapasitas harian</div>
            <strong>{{ number_format($kapasitasInfo['kapasitas_harian_pcs'] ?? 0) }}</strong>
            <span>pcs/hari ({{ $kapasitasInfo['max_batch_per_hari'] ?? 0 }} batch &times; {{ $kapasitasInfo['max_pcs_per_batch'] ?? 0 }} pcs)</span>
        </div>
    </div>
</section>

<section class="callout {{ ($isOverload || $kritisCount > 0) ? '' : 'is-calm' }}" aria-label="Rekomendasi antrean">
    <div class="callout-body">
        <div class="callout-pills">
            <span class="pill-meta {{ ($isOverload || $kritisCount > 0) ? 'alert' : 'ok' }}">
                {{ $isOverload ? 'Overload antrean terdeteksi' : ($kritisCount > 0 ? 'Perlu tindakan segera' : 'Ritme antrean stabil') }}
            </span>
            @if(isset($bebanAntreanInfo['deadline_terdekat_hari']) && $bebanAntreanInfo['deadline_terdekat_hari'] !== null)
                <span class="pill-meta">Deadline terdekat: {{ $bebanAntreanInfo['deadline_terdekat_hari'] }} hari</span>
            @endif
            @if(!empty($kapasitasInfo['avg_pcs_per_batch_aktual']))
                <span class="pill-meta">Rata-rata aktual: {{ number_format($kapasitasInfo['avg_pcs_per_batch_aktual']) }} pcs/batch</span>
            @endif
        </div>
        @if($topPo)
            @php
                $topKode = 'PO-' . str_pad($topPo->no_po_produk, 4, '0', STR_PAD_LEFT);
            @endphp
            <p>
                Prioritas teratas saat ini: <b>{{ $topKode }} ({{ $topPo->nama_pelanggan }})</b> &mdash; {{ $topAnalysis['alasan_prioritas'] ?? 'Siap ditindaklanjuti sesuai antrean.' }}
            </p>
        @else
            <p>Semua Purchase Order yang aktif sudah tertangani atau belum ada antrean produksi baru.</p>
        @endif
    </div>
    @if($topPo && empty($topAnalysis['is_semua_item_siap']) && empty($topAnalysis['has_pending_batch']))
        <a class="btn" href="{{ route('po-produk.buat-batch', $topPo->no_po_produk) }}">Mulai batch #1 ({{ 'PO-' . str_pad($topPo->no_po_produk, 4, '0', STR_PAD_LEFT) }})</a>
    @elseif($topPo)
        <button type="button" class="btn" onclick="openPoDetailModal({{ $topPo->no_po_produk }})">Lihat PO #1</button>
    @endif
</section>

<section class="panel" id="panel-po" aria-label="Daftar Purchase Order">
    <div class="tabs" role="group" aria-label="Tampilan PO">
        <button type="button" data-t="daftar-po" aria-selected="true">Antrean &amp; daftar PO ({{ $poList->count() }})</button>
        <button type="button" data-t="audit-po" aria-selected="false">Histori audit PO ({{ $auditCount }})</button>
    </div>

    <div id="t-daftar-po" data-filter-box data-total="{{ $poList->count() }}" data-unit="PO">
        <div class="controls">
            <input
                type="search"
                class="field grow"
                data-search
                placeholder="Cari kode PO, pelanggan, nomor e-commerce, atau varian..."
                aria-label="Cari Purchase Order"
            >
            <select class="field" data-kanal aria-label="Filter kanal penjualan">
                <option value="semua">Semua kanal</option>
                <option value="ecommerce">Khusus E-Commerce</option>
                <option value="manual">Non-E-Commerce</option>
                <option value="Konsinyasi">Konsinyasi</option>
                <option value="Jual Putus">Jual Putus</option>
                <option value="Reseller">Reseller</option>
                <option value="Eceran">Eceran</option>
            </select>
            <select class="field" data-sort aria-label="Urutkan PO">
                <option value="prio">Urut: Rekomendasi prioritas</option>
                <option value="due">Urut: Deadline terdekat</option>
                <option value="short">Urut: Kurang produksi terbanyak</option>
                <option value="num">Urut: Target pcs terbanyak</option>
            </select>
            <button type="button" class="reset-btn" data-reset>Reset</button>
        </div>

        <div class="chips">
            <button type="button" data-f="semua" aria-pressed="true">Semua ({{ $poList->count() }})</button>
            <button type="button" data-f="kritis" aria-pressed="false">Kritis ({{ $urgCounts['kritis'] }})</button>
            <button type="button" data-f="tinggi" aria-pressed="false">Tinggi ({{ $urgCounts['tinggi'] }})</button>
            <button type="button" data-f="normal" aria-pressed="false">Normal ({{ $urgCounts['normal'] }})</button>
            <button type="button" data-f="selesai" aria-pressed="false">Selesai / Siap ({{ $urgCounts['selesai'] }})</button>
            <p>Klik <b>Rincian</b> untuk melihat pipeline produksi &amp; detail alokasi item.</p>
        </div>

        @if($poList->isNotEmpty())
            <ul class="rows">
                @foreach($poList as $po)
                    @php
                        $an = $poAnalysis[$po->no_po_produk] ?? [];
                        $urgRaw = strtoupper((string) ($an['urgensi_level'] ?? 'NORMAL'));
                        $isDone = in_array($po->status_po, ['SELESAI', 'DIKIRIM', 'STOK_DIPOTONG'], true) || $urgRaw === 'SELESAI';

                        if ($isDone) {
                            $chipState = 'selesai';
                            $rowTone = in_array($po->status_po, ['SELESAI', 'DIKIRIM'], true) ? 'ok' : 'mid';
                            $rankClass = 'muted';
                        } elseif ($urgRaw === 'KRITIS') {
                            $chipState = 'kritis';
                            $rowTone = 'low';
                            $rankClass = '';
                        } elseif ($urgRaw === 'TINGGI') {
                            $chipState = 'tinggi';
                            $rowTone = 'mid';
                            $rankClass = 'warm';
                        } else {
                            $chipState = 'normal';
                            $rowTone = 'ok';
                            $rankClass = 'muted';
                        }

                        $kodePo = 'PO-' . str_pad($po->no_po_produk, 4, '0', STR_PAD_LEFT);
                        $rankNum = $an['rekomendasi_rank'] ?? null;
                        $rankLabel = $rankNum !== null ? ('#' . $rankNum . ' Prioritas') : ($an['urgensi_label'] ?? $po->status_po);

                        $totalTarget = (int) ($an['total_target'] ?? $po->jumlah_po);
                        $totalSiap = (int) ($an['total_siap'] ?? 0);
                        $butuhProduksi = (int) ($an['butuh_produksi_pcs'] ?? max(0, $totalTarget - $totalSiap));
                        $sisaHari = $an['sisa_hari_tempo'] ?? null;
                        $dueVal = $sisaHari !== null ? (int) $sisaHari : 9999;
                        $prioVal = $rankNum !== null ? (int) $rankNum : (10000 + $loop->index);

                        $hasPendingBatch = !empty($an['has_pending_batch']);
                        $isAllReady = !empty($an['is_semua_item_siap']);

                        $statusMap = [
                            'PENDING' => $isAllReady ? 'Siap Potong' : ($hasPendingBatch ? 'Menunggu QC' : 'Perlu Produksi'),
                            'PROSES' => $hasPendingBatch ? 'Proses / QC' : ($isAllReady ? 'Siap Potong' : 'Dalam Proses'),
                            'STOK_DIPOTONG' => 'Stok Dipotong',
                            'DIKIRIM' => 'Dalam Kirim',
                            'SELESAI' => 'Selesai',
                        ];
                        $statusStamp = $statusMap[$po->status_po] ?? $po->status_po;

                        $dueText = $po->tgl_jatuh_tempo_po
                            ? \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->format('d M Y')
                            : '-';
                        $itemNamesSearch = $po->detailAlokasi->map(fn($d) => optional($d->produkJadi)->nama_produk . ' ' . optional($d->produkJadi)->varian)->implode(' ');
                    @endphp
                    <li class="row po {{ $rowTone }}"
                        data-s="{{ $chipState }}"
                        data-kanal="{{ !empty($an['is_ecommerce']) ? 'ecommerce' : 'manual' }}"
                        data-tipe-jual="{{ $po->tipe_jual }}"
                        data-prio="{{ $prioVal }}"
                        data-due="{{ $dueVal }}"
                        data-short="{{ $butuhProduksi }}"
                        data-num="{{ $totalTarget }}"
                        data-q="{{ strtolower($kodePo . ' ' . $po->nama_pelanggan . ' ' . ($po->nama_penerima ?? '') . ' ' . ($po->nomor_pesanan_ecommerce ?? '') . ' ' . $po->tipe_jual . ' ' . $itemNamesSearch) }}"
                        @if($loop->index >= 6) data-more="1" hidden @endif>
                        <div class="prio">
                            <span class="rank {{ $rankClass }}">{{ $rankLabel }}</span>
                            <b>{{ $kodePo }}</b>
                            <small>{{ $an['urgensi_label'] ?? $po->status_po }}</small>
                        </div>

                        <div>
                            <b>{{ $po->nama_pelanggan }}</b>
                            <small>
                                {{ $po->tipe_jual }}@if($po->nomor_pesanan_ecommerce) &middot; {{ $po->nomor_pesanan_ecommerce }}@endif
                                &middot; Tempo {{ $dueText }}
                            </small>
                            <div class="tags">
                                <span class="tag">{{ $po->detailAlokasi->count() }} item &middot; {{ number_format($totalTarget) }} pcs</span>
                                @if($butuhProduksi > 0 && $po->status_po !== 'STOK_DIPOTONG' && $po->status_po !== 'SELESAI' && $po->status_po !== 'DIKIRIM')
                                    <span class="tag warn">Kurang {{ number_format($butuhProduksi) }} pcs</span>
                                @else
                                    <span class="tag ok">Stok siap {{ number_format($totalSiap) }} pcs</span>
                                @endif
                                @if($po->suratJalan)
                                    <span class="tag ok">SJ-{{ str_pad($po->suratJalan->no_ref, 4, '0', STR_PAD_LEFT) }}</span>
                                @endif
                                @if(!empty($an['is_berisiko_telat']))
                                    <span class="tag warn">Berisiko telat</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <b>{{ number_format($totalSiap) }}/{{ number_format($totalTarget) }} pcs siap</b>
                            <small>{{ $an['alasan_prioritas'] ?? 'Pantau progres produksi dan alokasi stok.' }}</small>
                            <small>
                                @if($sisaHari !== null)Sisa {{ $sisaHari }} hari &middot; @endif
                                {{ $an['butuh_batch'] ?? 0 }} batch ({{ $an['butuh_hari'] ?? 0 }} hr kerja)
                            </small>
                        </div>

                        <span class="st">{{ $statusStamp }}</span>

                        <div class="act">
                            <button type="button" onclick="openPoDetailModal({{ $po->no_po_produk }})">Rincian</button>

                            @if($po->status_po === 'SELESAI' || $po->status_po === 'DIKIRIM')
                                <a href="{{ route('shipments.index') }}">Kirim</a>
                            @elseif($po->status_po === 'STOK_DIPOTONG' && !$po->suratJalan)
                                <button type="button" onclick="openCreateSjModal({{ $po->no_po_produk }}, {{ \Illuminate\Support\Js::from($kodePo) }}, {{ \Illuminate\Support\Js::from($po->nama_penerima ?: $po->nama_pelanggan) }})">Terbitkan SJ</button>
                            @elseif($hasPendingBatch)
                                <a href="{{ route('log-produksi.index') }}">QC Batch</a>
                            @elseif($isAllReady && $po->status_po !== 'STOK_DIPOTONG')
                                <form action="{{ route('po-produk.potong-stok', $po->no_po_produk) }}" method="POST" onsubmit="return confirm('Potong stok produk jadi untuk {{ $kodePo }} sekarang?')">
                                    @csrf
                                    <button type="submit">Potong stok</button>
                                </form>
                            @else
                                <a href="{{ route('po-produk.buat-batch', $po->no_po_produk) }}">Buat batch</a>
                            @endif

                            @if(!$po->suratJalan && !in_array($po->status_po, ['DIKIRIM', 'SELESAI'], true))
                                <button type="button" class="del" onclick="openDeletePoModal({{ $po->no_po_produk }}, {{ \Illuminate\Support\Js::from($kodePo) }}, {{ \Illuminate\Support\Js::from($po->nama_pelanggan) }})">Hapus</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="empty-filter" hidden>
                <strong>Tidak ada PO yang cocok</strong>
                Ubah pencarian, kanal, atau filter urgensi untuk menampilkan PO lainnya.
            </div>

            <div class="foot">
                <span id="cnt" data-cnt>Menampilkan {{ min(6, $poList->count()) }} dari {{ $poList->count() }} PO</span>
                @if($poList->count() > 6)
                    <button type="button" class="btn" data-expand="Lihat semua {{ $poList->count() }} PO" data-collapse="Tampilkan 6 PO">Lihat semua {{ $poList->count() }} PO</button>
                @endif
            </div>
        @else
            <x-void-state
                title="Belum ada Purchase Order"
                description="Tambahkan Purchase Order pelanggan untuk mulai memprioritaskan jadwal goreng, alokasi stok, dan pengiriman."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-po')">+ Tambah PO baru</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>

    <div id="t-audit-po" hidden>
        @if($auditCount > 0)
            <ul class="rows">
                @foreach($auditLogs as $log)
                    <li class="row audit">
                        <div>
                            <b>{{ $log['aksi'] ?? '-' }} &middot; {{ $log['kode_po'] ?? '-' }}</b>
                            <small>{{ $log['datetime'] ?? '-' }}</small>
                        </div>
                        <div>
                            <b>{{ $log['pelanggan'] ?? '-' }}</b>
                            <small>{{ $log['user_name'] ?? '-' }} ({{ $log['role'] ?? '-' }})</small>
                        </div>
                        <div>
                            <b>{{ $log['detail'] ?? '-' }}</b>
                            @if(!empty($log['alasan']))
                                <small>Alasan: {{ $log['alasan'] }}</small>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-void-state
                title="Belum ada histori audit PO"
                description="Semua perubahan status, pemotongan stok, pengaturan kapasitas, atau pembatalan PO akan tercatat di sini."
            />
        @endif
    </div>
</section>

<!-- Modal Rincian PO (#po-modal) -->
<x-modal
    id="po-modal"
    title="Rincian PO"
    subtitle="Detail pesanan &amp; pipeline pemenuhan"
    badge="Prioritas"
    badgeId="pm-rank"
    titleId="pm-title"
    subtitleId="pm-subtitle"
    :wide="true"
>
    <div class="modal-body">
        <div class="modal-meta">
            <div>
                <span>No. Pesanan &amp; Kanal</span>
                <b id="pm-order">-</b>
            </div>
            <div>
                <span>Tanggal PO &amp; Jatuh Tempo</span>
                <b id="pm-dates">-</b>
            </div>
            <div>
                <span>Beban Kapasitas &amp; Estimasi</span>
                <b id="pm-cap">-</b>
            </div>
            <div>
                <span>Penerima &amp; Alamat</span>
                <b id="pm-customer">-</b>
            </div>
            <div>
                <span>Status Saat Ini</span>
                <b id="pm-status">-</b>
            </div>
            <div>
                <span>Analisa Prioritas</span>
                <b id="pm-reason">-</b>
            </div>
        </div>

        <div>
            <span class="modal-section-title">Alur pemenuhan PO</span>
            <div class="flow" id="pm-flow"></div>
        </div>

        <div>
            <span class="modal-section-title">Rincian item produk dialokasikan</span>
            <ul class="modal-items" id="pm-items"></ul>
        </div>
    </div>
    <div class="modal-foot">
        <span id="pm-summary">Total target: 0 pcs</span>
        <div class="acts">
            <button type="button" class="btn outline" data-close>Tutup</button>
            <a href="#" id="pm-action-btn" class="btn is-hidden">Lanjutkan proses</a>
        </div>
    </div>
</x-modal>

<!-- Modal Tambah PO Baru -->
<x-modal
    id="modal-po"
    title="Tambah Purchase Order baru"
    subtitle="Catat pesanan baru pelanggan beserta target alokasi varian produknya."
    :wide="true"
>
    <form action="{{ route('po-produk.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="po-nama-pelanggan">Nama Pelanggan / Toko *</label>
                    <input id="po-nama-pelanggan" type="text" name="nama_pelanggan" required placeholder="Misal: Toko Berkah Condet" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="po-nama-penerima">Nama Penerima (Opsional)</label>
                    <input id="po-nama-penerima" type="text" name="nama_penerima" placeholder="Default sama dengan pelanggan" class="form-input">
                </div>
            </div>

            <div class="form-grid three">
                <div class="form-field">
                    <label class="form-label" for="po-tipe-jual">Kanal / Tipe Jual *</label>
                    <select id="po-tipe-jual" name="tipe_jual" required class="form-input" onchange="toggleEcommerceField()">
                        <option value="Konsinyasi">Konsinyasi</option>
                        <option value="Jual Putus">Jual Putus</option>
                        <option value="Reseller">Reseller</option>
                        <option value="Eceran">Eceran</option>
                        <option value="E-Commerce">E-Commerce</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="po-tgl">Tanggal PO *</label>
                    <input id="po-tgl" type="date" name="tgl_po" value="{{ date('Y-m-d') }}" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="po-tgl-tempo">Jatuh Tempo PO *</label>
                    <input id="po-tgl-tempo" type="date" name="tgl_jatuh_tempo_po" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required class="form-input">
                </div>
            </div>

            <div class="form-field is-hidden" id="wrap-nomor-ecom">
                <label class="form-label" for="po-nomor-ecom">Nomor Pesanan E-Commerce *</label>
                <input id="po-nomor-ecom" type="text" name="nomor_pesanan_ecommerce" placeholder="Misal: 260918ABCDEF" class="form-input">
            </div>

            <div class="form-field">
                <label class="form-label" for="po-alamat">Alamat Pengiriman (Opsional)</label>
                <input id="po-alamat" type="text" name="alamat_pelanggan" placeholder="Alamat lengkap tujuan kirim" class="form-input">
            </div>

            <div class="form-box">
                <div class="form-box-head">
                    <span>Daftar item produk dipesan *</span>
                    <button type="button" class="btn outline" onclick="addPoItemRow()">+ Tambah item</button>
                </div>
                <div id="po-items-container" class="form-grid">
                    <div class="inline-item-row">
                        <select name="produk_ids[]" required class="form-input" aria-label="Pilih produk">
                            <option value="">-- Pilih produk jadi --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id_produk_jadi }}">
                                    {{ $prod->nama_produk }} ({{ $prod->varian }} &middot; {{ $prod->netto }}) &mdash; ATP: {{ number_format($prod->stok_atp) }}
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="produk_qty[]" min="1" value="10" required placeholder="Qty" class="form-input" aria-label="Jumlah pcs">
                        <button type="button" class="row-remove-btn" onclick="removePoItemRow(this)" title="Hapus baris">&times;</button>
                    </div>
                </div>
            </div>

            <label class="form-check">
                <input type="checkbox" name="buat_batch_langsung" value="1">
                <span>Langsung buka form jadwal batch produksi untuk PO ini setelah disimpan</span>
            </label>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Simpan Purchase Order</button>
        </div>
    </form>
</x-modal>

<!-- Modal Atur Kapasitas Harian -->
<x-modal
    id="modal-kapasitas"
    title="Atur kapasitas produksi harian"
    subtitle="Parameter ini menentukan estimasi hari kerja, deteksi overload, dan skor urgensi antrean PO."
>
    <form action="{{ route('po-produk.kapasitas') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="form-note is-warm">
                Aktual produksi: rata-rata <b>{{ number_format($kapasitasInfo['avg_pcs_per_batch_aktual'] ?? 0) }} pcs/batch</b>, puncak harian <b>{{ number_format($kapasitasInfo['max_harian_aktual'] ?? 0) }} pcs/hari</b>.
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="kap-max-batch">Max Batch per Hari *</label>
                    <input id="kap-max-batch" type="number" name="max_batch_per_hari" min="1" max="50" value="{{ $kapasitasInfo['max_batch_per_hari'] ?? 3 }}" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="kap-max-pcs">Max Pcs per Batch *</label>
                    <input id="kap-max-pcs" type="number" name="max_pcs_per_batch" min="10" max="5000" value="{{ $kapasitasInfo['max_pcs_per_batch'] ?? 150 }}" required class="form-input">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="kap-catatan">Catatan Kapasitas (Opsional)</label>
                <input id="kap-catatan" type="text" name="catatan_kapasitas" value="{{ $kapasitasInfo['catatan_kapasitas'] ?? '' }}" placeholder="Misal: 2 wajan penggorengan aktif" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Simpan kapasitas</button>
        </div>
    </form>
</x-modal>

<!-- Modal Terbitkan Surat Jalan -->
<x-modal
    id="modal-sj"
    title="Terbitkan Surat Jalan"
    subtitle="Siapkan dokumen pengiriman untuk PO yang stoknya sudah dipotong."
>
    <form action="{{ route('shipments.surat-jalan.store') }}" method="POST">
        @csrf
        <input type="hidden" name="no_po_produk" id="sj-no-po" value="">
        <div class="modal-body">
            <div class="form-note is-ok">
                PO Terpilih: <b id="sj-kode-po">-</b>
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="sj-nama-penerima">Nama Penerima *</label>
                    <input id="sj-nama-penerima" type="text" name="nama_penerima" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="sj-tgl-terbit">Tanggal Terbit *</label>
                    <input id="sj-tgl-terbit" type="date" name="tgl_terbit" value="{{ date('Y-m-d') }}" required class="form-input">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="sj-kurir">Kurir / Ekspedisi *</label>
                <input id="sj-kurir" type="text" name="nama_kurir" required placeholder="Misal: Kurir Toko / JNE / Lalamove" class="form-input">
            </div>

            <div class="form-field">
                <label class="form-label" for="sj-ket">Catatan Pengiriman (Opsional)</label>
                <input id="sj-ket" type="text" name="ket" placeholder="Misal: Kirim sebelum jam 15.00" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Terbitkan Surat Jalan</button>
        </div>
    </form>
</x-modal>

<!-- Modal Hapus PO -->
<x-modal
    id="modal-delete-po"
    title="Batalkan &amp; hapus Purchase Order"
    subtitle="Kunci alokasi PO akan dilepaskan. PO dengan Surat Jalan aktif tidak dapat dihapus."
    subtitleId="delete-po-subtitle"
>
    <form id="form-delete-po" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="delete-po-kategori">Kategori Alasan Pembatalan *</label>
                <select id="delete-po-kategori" name="kategori_alasan" required class="form-input">
                    <option value="Dibatalkan Pelanggan">Dibatalkan Pelanggan</option>
                    <option value="Kesalahan Input Data">Kesalahan Input Data</option>
                    <option value="Perubahan Pesanan">Perubahan Pesanan</option>
                    <option value="Duplikasi PO">Duplikasi PO</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="form-field">
                <label class="form-label" for="delete-po-detail">Detail Alasan (Wajib untuk Audit) *</label>
                <input id="delete-po-detail" type="text" name="detail_alasan" required placeholder="Jelaskan alasan penghapusan PO" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn danger">Hapus PO</button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
    var poDataMap = @json($poModalMap);

    function toggleEcommerceField() {
        var select = document.getElementById('po-tipe-jual');
        var wrap = document.getElementById('wrap-nomor-ecom');
        var input = document.getElementById('po-nomor-ecom');
        var isEcom = select.value === 'E-Commerce';
        wrap.classList.toggle('is-hidden', !isEcom);
        input.required = isEcom;
        if (!isEcom) input.value = '';
    }

    function addPoItemRow() {
        var container = document.getElementById('po-items-container');
        var first = container.querySelector('.inline-item-row');
        var clone = first.cloneNode(true);
        clone.querySelector('select').selectedIndex = 0;
        clone.querySelector('input[type="number"]').value = 10;
        container.appendChild(clone);
    }

    function removePoItemRow(btn) {
        var container = document.getElementById('po-items-container');
        if (container.querySelectorAll('.inline-item-row').length > 1) {
            btn.closest('.inline-item-row').remove();
        } else {
            alert('Minimal harus terdapat 1 item produk pada Purchase Order.');
        }
    }

    function openPoDetailModal(id) {
        var data = poDataMap[String(id)];
        if (!data) return;

        var rankEl = document.getElementById('pm-rank');
        rankEl.textContent = data.rank_label;
        rankEl.className = 'rank ' + (data.rank_tone === 'alert' ? '' : data.rank_tone);

        document.getElementById('pm-title').textContent = data.kode + ' \u2014 ' + data.pelanggan;
        document.getElementById('pm-subtitle').textContent =
            data.tipe_jual + (data.ecommerce_no ? ' (' + data.ecommerce_no + ')' : '');

        document.getElementById('pm-order').textContent =
            data.kode + ' \u00B7 ' + data.tipe_jual + (data.ecommerce_no ? ' (' + data.ecommerce_no + ')' : '');
        document.getElementById('pm-dates').textContent = data.tgl_po + ' \u2192 Tempo ' + data.tgl_tempo;
        document.getElementById('pm-cap').textContent =
            data.kapasitas_text + (data.estimasi_selesai && data.estimasi_selesai !== '-' ? ' \u00B7 Est. ' + data.estimasi_selesai : '');
        document.getElementById('pm-customer').textContent = data.penerima + ' \u00B7 ' + data.alamat;
        document.getElementById('pm-status').textContent =
            data.status + (data.surat_jalan ? ' (' + data.surat_jalan + ')' : '') + (data.pending_batches ? ' \u00B7 Batch: ' + data.pending_batches : '');
        document.getElementById('pm-reason').textContent = data.alasan;

        var steps = ['PO Masuk', 'Batch Produksi', 'Lulus QC', 'Potong Stok', 'Surat Jalan'];
        var flowEl = document.getElementById('pm-flow');
        flowEl.textContent = '';
        steps.forEach(function (label, index) {
            var stepNo = index + 1;
            var node = document.createElement('span');
            if (stepNo < data.step) {
                node.className = 'flow-step-done';
                node.textContent = '\u2713 ' + label;
            } else if (stepNo === data.step) {
                node.className = 'flow-step-active';
                node.textContent = stepNo + '. ' + label;
            } else {
                node.textContent = stepNo + '. ' + label;
            }
            flowEl.appendChild(node);
            if (index < steps.length - 1) {
                var sep = document.createElement('span');
                sep.textContent = '\u2192';
                flowEl.appendChild(sep);
            }
        });

        var itemsEl = document.getElementById('pm-items');
        itemsEl.textContent = '';
        if (!data.items || data.items.length === 0) {
            var emptyLi = document.createElement('li');
            emptyLi.className = 'modal-item';
            emptyLi.textContent = 'Tidak ada rincian alokasi item tercatat.';
            itemsEl.appendChild(emptyLi);
        } else {
            data.items.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'modal-item';

                var left = document.createElement('div');
                var nameEl = document.createElement('b');
                nameEl.textContent = item.nama;
                var metaEl = document.createElement('small');
                metaEl.textContent = item.meta + ' \u00B7 Stok gudang: ' + Number(item.stok).toLocaleString('id-ID') + ' pcs';
                left.appendChild(nameEl);
                left.appendChild(metaEl);

                var right = document.createElement('div');
                right.className = 'modal-item-right';
                var qtyWrap = document.createElement('div');
                qtyWrap.className = 'modal-qty';
                qtyWrap.textContent = Number(item.qty).toLocaleString('id-ID') + ' ';
                var unitEl = document.createElement('small');
                unitEl.textContent = 'pcs';
                qtyWrap.appendChild(unitEl);

                var tagEl = document.createElement('span');
                var cukup = Number(item.stok) >= Number(item.qty);
                tagEl.className = 'tag ' + (cukup ? 'ok' : 'warn');
                tagEl.textContent = cukup ? 'Cukup' : 'Kurang ' + Number(item.qty - item.stok).toLocaleString('id-ID');

                right.appendChild(qtyWrap);
                right.appendChild(tagEl);
                li.appendChild(left);
                li.appendChild(right);
                itemsEl.appendChild(li);
            });
        }

        document.getElementById('pm-summary').textContent =
            'Target: ' + Number(data.total_target).toLocaleString('id-ID') + ' pcs \u00B7 ' +
            'Siap: ' + Number(data.total_siap).toLocaleString('id-ID') + ' pcs \u00B7 ' +
            'Kurang: ' + Number(data.butuh_produksi).toLocaleString('id-ID') + ' pcs';

        var actBtn = document.getElementById('pm-action-btn');
        if (data.next_action && data.next_action.href) {
            actBtn.textContent = data.next_action.label;
            actBtn.href = data.next_action.href;
            actBtn.classList.remove('is-hidden');
        } else {
            actBtn.classList.add('is-hidden');
        }

        openDialog('po-modal');
    }

    function openCreateSjModal(noPo, kodePo, penerima) {
        document.getElementById('sj-no-po').value = noPo;
        document.getElementById('sj-kode-po').textContent = kodePo + ' (' + penerima + ')';
        document.getElementById('sj-nama-penerima').value = penerima;
        document.getElementById('sj-kurir').value = '';
        document.getElementById('sj-ket').value = '';
        openDialog('modal-sj');
    }

    function openDeletePoModal(noPo, kodePo, pelanggan) {
        document.getElementById('form-delete-po').action = '/po-produk/destroy/' + noPo;
        document.getElementById('delete-po-subtitle').textContent = kodePo + ' \u2014 ' + pelanggan;
        document.getElementById('delete-po-detail').value = '';
        openDialog('modal-delete-po');
    }
</script>
@endpush
