@extends('layouts.app')

@section('title', 'Pangeran Condet, Produksi')

@section('content')
@php
    $gorengCount = 0;
    $lolosCount = 0;
    $totalGradeA = 0;
    $batchModalMap = [];

    foreach ($logProduksi as $log) {
        $isSelesaiQc = strtoupper((string) $log->status_approval) === 'APPROVED'
            || ((int) $log->jml_grade_A > 0)
            || ((int) $log->jml_grade_B > 0);

        if ($isSelesaiQc) {
            $lolosCount++;
            $totalGradeA += (int) $log->jml_grade_A;
        } else {
            $gorengCount++;
        }

        $itemsList = [];
        $targetSum = 0;
        foreach ($log->detailProduksi as $det) {
            $qtyPlan = (int) $det->jml_produk_jadi;
            $targetSum += $qtyPlan;
            $prod = $det->produkJadi;
            $itemsList[] = [
                'detail_id' => $det->getKey(),
                'produk_nama' => $prod ? ($prod->nama_produk . ' (' . $prod->varian . ')') : ('Produk #' . $det->id_produk_jadi),
                'netto' => $prod ? (string) $prod->netto : '-',
                'target_qty' => $qtyPlan,
            ];
        }

        $kodeBatch = 'BCH-' . str_pad($log->no_batch, 4, '0', STR_PAD_LEFT);
        $batchModalMap[(string) $log->no_batch] = [
            'kode' => $kodeBatch,
            'tanggal' => $log->tgl_produksi ? \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') : '-',
            'shift' => (string) ($log->waktu_produksi ?: '-'),
            'po_kode' => $log->no_po_produk ? ('PO-' . str_pad($log->no_po_produk, 4, '0', STR_PAD_LEFT)) : 'Stok Gudang',
            'pelanggan' => $log->poProduk ? (string) $log->poProduk->nama_pelanggan : 'Produksi Stok Gudang',
            'is_done' => $isSelesaiQc,
            'grade_a' => (int) $log->jml_grade_A,
            'grade_b' => (int) $log->jml_grade_B,
            'exp_range' => (string) ($log->rentang_tgl_exp ?: '-'),
            'ket' => (string) ($log->ket ?: '-'),
            'total_target' => $targetSum,
            'items' => $itemsList,
        ];
    }

    $poBatchUiMap = [];
    foreach ($poList as $po) {
        $poItems = [];
        foreach ($po->detailAlokasi as $alok) {
            $prod = $alok->produkJadi;
            $stokAtp = $prod ? (int) $prod->stok_atp : 0;
            $needed = (int) $alok->jml_alokasi;
            $poItems[] = [
                'id_produk_jadi' => (int) $alok->id_produk_jadi,
                'nama' => $prod ? ($prod->nama_produk . ' (' . $prod->varian . ')') : ('Produk #' . $alok->id_produk_jadi),
                'qty' => $needed,
                'stok_atp' => $stokAtp,
            ];
        }
        $poBatchUiMap[(string) $po->no_po_produk] = [
            'kode' => 'PO-' . str_pad($po->no_po_produk, 4, '0', STR_PAD_LEFT),
            'pelanggan' => (string) $po->nama_pelanggan,
            'tempo' => $po->tgl_jatuh_tempo_po ? \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->format('d M Y') : '-',
            'items' => $poItems,
        ];
    }
@endphp

<section class="hero sm" aria-labelledby="j-produksi">
    <div>
        <h1 id="j-produksi">Jadwal batch goreng &amp; kontrol kualitas</h1>
        <p>Pantau batch produksi yang sedang digoreng, input hasil Grade A dan Grade B setelah pengemasan, atau jadwalkan batch baru untuk PO maupun stok gudang.</p>
        <div class="acts">
            <button type="button" class="btn" onclick="openBatchModal('po')">+ Jadwalkan batch PO</button>
            <button type="button" class="btn ghost" onclick="openBatchModal('stok_gudang')">+ Batch stok gudang</button>
        </div>
    </div>
    <ul class="trio four" aria-label="Ringkasan produksi">
        <li>
            <div class="disc {{ $logProduksi->count() > 0 ? 'fill' : 'empty' }}">{{ number_format($logProduksi->count()) }}</div>
            <b>Total batch</b>
            <span>tercatat</span>
        </li>
        <li>
            <div class="disc {{ $gorengCount > 0 ? 'alert' : 'empty' }}">{{ number_format($gorengCount) }}</div>
            <b>Digoreng</b>
            <span>menunggu QC</span>
        </li>
        <li>
            <div class="disc {{ $lolosCount > 0 ? 'fill' : 'empty' }}">{{ number_format($lolosCount) }}</div>
            <b>Lulus QC</b>
            <span>selesai kemas</span>
        </li>
        <li>
            <div class="disc {{ $totalGradeA > 0 ? 'fill' : 'empty' }}">{{ number_format($totalGradeA) }}</div>
            <b>Grade A</b>
            <span>pcs masuk</span>
        </li>
    </ul>
</section>

<section class="panel" id="panel-produksi" aria-label="Batch Produksi">
    <div class="tabs" role="group" aria-label="Tampilan produksi">
        <button type="button" data-t="batch-semua" aria-selected="true">Daftar batch ({{ $logProduksi->count() }})</button>
        <button type="button" data-t="batch-panduan" aria-selected="false">Panduan alur produksi</button>
    </div>

    <div id="t-batch-semua" data-filter-box data-total="{{ $logProduksi->count() }}" data-unit="batch">
        <div class="controls">
            <input
                type="search"
                class="field grow"
                data-search
                placeholder="Cari nomor batch, kode PO, pelanggan, shift, atau produk..."
                aria-label="Cari batch produksi"
            >
            <button type="button" class="reset-btn" data-reset>Reset</button>
        </div>

        <div class="chips">
            <button type="button" data-f="semua" aria-pressed="true">Semua ({{ $logProduksi->count() }})</button>
            <button type="button" data-f="goreng" aria-pressed="false">Sedang digoreng ({{ $gorengCount }})</button>
            <button type="button" data-f="lolos" aria-pressed="false">Lulus QC ({{ $lolosCount }})</button>
            <p>Klik <b>Input hasil QC</b> saat pengemasan selesai untuk menambah stok produk jadi.</p>
        </div>

        @if($logProduksi->isNotEmpty())
            <ul class="rows">
                @foreach($logProduksi as $log)
                    @php
                        $isSelesaiQc = strtoupper((string) $log->status_approval) === 'APPROVED'
                            || ((int) $log->jml_grade_A > 0)
                            || ((int) $log->jml_grade_B > 0);
                        $kodeBatch = 'BCH-' . str_pad($log->no_batch, 4, '0', STR_PAD_LEFT);
                        $kodePo = $log->no_po_produk ? ('PO-' . str_pad($log->no_po_produk, 4, '0', STR_PAD_LEFT)) : '';
                        $itemNames = $log->detailProduksi->map(function ($d) {
                            $nama = optional($d->produkJadi)->nama_produk ?: ('Produk #' . $d->id_produk_jadi);
                            return $nama . ' (' . number_format((int) $d->jml_produk_jadi) . ' pcs)';
                        })->implode(', ');
                        $targetBatch = (int) $log->detailProduksi->sum('jml_produk_jadi');
                    @endphp
                    <li class="row batch-row {{ $isSelesaiQc ? 'ok' : 'mid' }}"
                        data-s="{{ $isSelesaiQc ? 'lolos' : 'goreng' }}"
                        data-q="{{ strtolower($kodeBatch . ' ' . $kodePo . ' ' . ($log->poProduk->nama_pelanggan ?? 'stok gudang') . ' ' . ($log->waktu_produksi ?? '') . ' ' . $itemNames . ' ' . ($log->ket ?? '')) }}"
                        @if($loop->index >= 6) data-more="1" hidden @endif>
                        <div>
                            <b>{{ $kodeBatch }}</b>
                            <small>{{ $log->tgl_produksi ? \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') : '-' }} &middot; {{ $log->waktu_produksi ?? '-' }}</small>
                            <div class="tags">
                                @if($log->poProduk)
                                    <span class="tag ok">{{ $kodePo }}</span>
                                @else
                                    <span class="tag">Stok gudang</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <b>{{ $log->poProduk ? $log->poProduk->nama_pelanggan : 'Produksi stok umum' }}</b>
                            <small>{{ $itemNames ?: 'Belum ada rincian item produk' }}</small>
                            @if($log->ket)
                                <small>{{ $log->ket }}</small>
                            @endif
                        </div>

                        <div class="qty">
                            @if($isSelesaiQc)
                                {{ number_format((int) $log->jml_grade_A) }} <small>Grade A{{ (int) $log->jml_grade_B > 0 ? ' · ' . number_format((int) $log->jml_grade_B) . ' Grade B' : '' }}</small>
                            @else
                                {{ number_format($targetBatch) }} <small>pcs target</small>
                            @endif
                        </div>

                        <span class="st">{{ $isSelesaiQc ? 'Lulus QC' : 'Sedang digoreng' }}</span>

                        <div class="act">
                            @if(!$isSelesaiQc)
                                <button type="button" onclick="openCompleteQcModal({{ $log->no_batch }})">Input hasil QC</button>
                                <form action="{{ route('log-produksi.destroy', $log->no_batch) }}" method="POST" onsubmit="return confirm('Batalkan dan hapus batch {{ $kodeBatch }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="del">Hapus</button>
                                </form>
                            @else
                                <button type="button" onclick="openQcSummaryModal({{ $log->no_batch }})">Rincian</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="empty-filter" hidden>
                <strong>Tidak ada batch yang cocok</strong>
                Ubah kata kunci pencarian atau filter status batch.
            </div>

            <div class="foot">
                <span id="cnt" data-cnt>Menampilkan {{ min(6, $logProduksi->count()) }} dari {{ $logProduksi->count() }} batch</span>
                @if($logProduksi->count() > 6)
                    <button type="button" class="btn" data-expand="Lihat semua {{ $logProduksi->count() }} batch" data-collapse="Tampilkan 6 batch">Lihat semua {{ $logProduksi->count() }} batch</button>
                @endif
            </div>
        @else
            <x-void-state
                title="Belum ada batch produksi"
                description="Jadwalkan batch baru untuk memenuhi antrean Purchase Order pelanggan atau menambah buffer stok gudang."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openBatchModal('po')">+ Jadwalkan batch PO</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>

    <div id="t-batch-panduan" hidden>
        <ul class="rows">
            <li class="row guide ok">
                <div>
                    <b>1. Pilih mode batch</b>
                    <small>Untuk PO atau stok gudang</small>
                </div>
                <div>
                    <b>Hubungkan ke PO prioritas tertinggi atau buat buffer stok.</b>
                    <small>Sistem akan menampilkan item produk dari PO terpilih secara otomatis agar operator tidak salah input target pcs.</small>
                </div>
                <span class="st">Tahap awal</span>
            </li>
            <li class="row guide mid">
                <div>
                    <b>2. Proses goreng dapur</b>
                    <small>Shift Pagi / Siang / Sore</small>
                </div>
                <div>
                    <b>Batch berstatus Sedang digoreng hingga tim QC menimbang hasil.</b>
                    <small>Selama status masih menunggu QC, batch masih dapat dibatalkan bila terjadi perubahan jadwal mendadak.</small>
                </div>
                <span class="st">Proses</span>
            </li>
            <li class="row guide ok">
                <div>
                    <b>3. Input hasil Grade A &amp; B</b>
                    <small>Penambahan stok otomatis</small>
                </div>
                <div>
                    <b>Klik Input hasil QC setelah penimbangan dan pengemasan selesai.</b>
                    <small>Jumlah Grade A otomatis menambah stok fisik produk jadi sehingga siap dipotong ke PO terkait dan dikirim.</small>
                </div>
                <span class="st">Finalisasi</span>
            </li>
        </ul>
    </div>
</section>

<!-- Modal Jadwalkan Batch Produksi -->
<x-modal
    id="modal-log-produksi"
    title="Jadwalkan batch produksi"
    subtitle="Pilih mode PO pelanggan atau stok bebas gudang, lalu tentukan shift dan target qty."
    :wide="true"
>
    <form action="{{ route('log-produksi.store') }}" method="POST" id="form-batch-store">
        @csrf
        <input type="hidden" name="mode_produksi" id="batch-mode-input" value="po">
        <input type="hidden" name="tgl_produksi" id="batch-tgl-hidden" value="{{ date('Y-m-d') }}">

        <div class="modal-body">
            <div class="tabs" role="group" aria-label="Mode produksi">
                <button type="button" id="tab-mode-po" aria-selected="true" onclick="switchBatchMode('po')">Batch untuk PO</button>
                <button type="button" id="tab-mode-stok" aria-selected="false" onclick="switchBatchMode('stok_gudang')">Batch stok gudang</button>
            </div>

            <div id="wrap-mode-po" class="form-grid">
                <div class="form-grid two">
                    <div class="form-field">
                        <label class="form-label" for="batch-po-select">Pilih Purchase Order Aktif *</label>
                        <select id="batch-po-select" name="no_po_produk" class="form-input" onchange="prefillItemsFromPo()">
                            <option value="">-- Pilih PO aktif --</option>
                            @foreach($poList as $po)
                                <option value="{{ $po->no_po_produk }}">
                                    PO-{{ str_pad($po->no_po_produk, 4, '0', STR_PAD_LEFT) }} &mdash; {{ $po->nama_pelanggan }} ({{ $po->status_po }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="tgl-produksi-po">Tanggal Produksi *</label>
                        <input id="tgl-produksi-po" type="date" value="{{ date('Y-m-d') }}" oninput="syncBatchDate(this.value)" class="form-input">
                    </div>
                </div>
            </div>

            <div id="wrap-mode-stok" class="form-grid is-hidden">
                <div class="form-field">
                    <label class="form-label" for="tgl-produksi-stok">Tanggal Produksi *</label>
                    <input id="tgl-produksi-stok" type="date" value="{{ date('Y-m-d') }}" oninput="syncBatchDate(this.value)" class="form-input">
                </div>
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="batch-shift">Shift Produksi *</label>
                    <select id="batch-shift" name="waktu_produksi" required class="form-input">
                        <option value="Shift Pagi">Shift Pagi</option>
                        <option value="Shift Siang">Shift Siang</option>
                        <option value="Shift Sore">Shift Sore</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="batch-ket">Catatan Dapur (Opsional)</label>
                    <input id="batch-ket" type="text" name="ket" placeholder="Misal: Prioritas wajan 1 &amp; 2" class="form-input">
                </div>
            </div>

            <div class="form-box">
                <div class="form-box-head">
                    <span>Daftar varian produk &amp; target goreng (pcs) *</span>
                    <button type="button" class="btn outline" onclick="addBatchItemRow()">+ Tambah produk</button>
                </div>
                <div id="batch-items-container" class="form-grid">
                    <div class="inline-item-row">
                        <select name="produk_ids[]" required class="form-input" aria-label="Pilih produk batch">
                            <option value="">-- Pilih produk jadi --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id_produk_jadi }}">
                                    {{ $prod->nama_produk }} ({{ $prod->varian }} &middot; {{ $prod->netto }})
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="produk_qty[]" min="1" value="50" required placeholder="Qty" class="form-input" aria-label="Target pcs">
                        <button type="button" class="row-remove-btn" onclick="removeBatchItemRow(this)" title="Hapus baris">&times;</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Simpan jadwal batch</button>
        </div>
    </form>
</x-modal>

<!-- Modal Input Hasil QC -->
<x-modal
    id="modal-complete-qc"
    title="Input hasil Quality Control batch"
    subtitle="Masukkan jumlah pcs Grade A per produk dan total Grade B setelah pengemasan."
    subtitleId="qc-modal-subtitle"
    :wide="true"
>
    <form id="form-complete-qc" method="POST">
        @csrf
        <div class="modal-body">
            <div class="modal-meta">
                <div>
                    <span>Kode Batch</span>
                    <b id="qc-info-kode">-</b>
                </div>
                <div>
                    <span>Tujuan PO / Jadwal</span>
                    <b id="qc-info-po">-</b>
                </div>
            </div>

            <div>
                <span class="modal-section-title">Hasil Grade A per varian (pcs) *</span>
                <ul class="modal-items" id="qc-items-list"></ul>
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="qc-grade-b">Total Grade B / Patah (pcs) *</label>
                    <input id="qc-grade-b" type="number" name="jml_grade_B" min="0" value="0" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="qc-ket">Catatan QC (Opsional)</label>
                    <input id="qc-ket" type="text" name="ket" placeholder="Misal: Lolos kerenyahan &amp; kemasan vakum" class="form-input">
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Selesaikan QC &amp; tambah stok</button>
        </div>
    </form>
</x-modal>

<!-- Modal Rincian Batch Lulus QC -->
<x-modal
    id="modal-qc-summary"
    title="Rincian batch selesai QC"
    subtitle="Laporan ringkas hasil produksi dan kualitas batch."
    subtitleId="qcs-subtitle"
>
    <div class="modal-body">
        <div class="modal-meta">
            <div>
                <span>Batch &amp; Shift</span>
                <b id="qcs-batch">-</b>
            </div>
            <div>
                <span>Tujuan</span>
                <b id="qcs-po">-</b>
            </div>
            <div>
                <span>Grade A / Grade B</span>
                <b id="qcs-grades">-</b>
            </div>
            <div>
                <span>Catatan</span>
                <b id="qcs-ket">-</b>
            </div>
        </div>

        <div>
            <span class="modal-section-title">Item direncanakan</span>
            <ul class="modal-items" id="qcs-items"></ul>
        </div>
    </div>
    <div class="modal-foot">
        <button type="button" class="btn outline" data-close>Tutup</button>
    </div>
</x-modal>

<template id="tpl-batch-item-row">
    <div class="inline-item-row">
        <select name="produk_ids[]" required class="form-input" aria-label="Pilih produk batch">
            <option value="">-- Pilih produk jadi --</option>
            @foreach($products as $prod)
                <option value="{{ $prod->id_produk_jadi }}">
                    {{ $prod->nama_produk }} ({{ $prod->varian }} &middot; {{ $prod->netto }})
                </option>
            @endforeach
        </select>
        <input type="number" name="produk_qty[]" min="1" value="50" required placeholder="Qty" class="form-input" aria-label="Target pcs">
        <button type="button" class="row-remove-btn" onclick="removeBatchItemRow(this)" title="Hapus baris">&times;</button>
    </div>
</template>
@endsection

@push('scripts')
<script>
    var batchModalMap = @json($batchModalMap);
    var poBatchUiMap = @json($poBatchUiMap);
    var selectedPoOnLoad = @json($selectedPoNo);

    function syncBatchDate(val) {
        document.getElementById('batch-tgl-hidden').value = val;
        document.getElementById('tgl-produksi-po').value = val;
        document.getElementById('tgl-produksi-stok').value = val;
    }

    function switchBatchMode(mode) {
        var isPo = mode === 'po';
        document.getElementById('batch-mode-input').value = isPo ? 'po' : 'stok_gudang';
        document.getElementById('tab-mode-po').setAttribute('aria-selected', isPo ? 'true' : 'false');
        document.getElementById('tab-mode-stok').setAttribute('aria-selected', !isPo ? 'true' : 'false');
        document.getElementById('wrap-mode-po').classList.toggle('is-hidden', !isPo);
        document.getElementById('wrap-mode-stok').classList.toggle('is-hidden', isPo);
        document.getElementById('batch-po-select').required = isPo;
        if (!isPo) {
            document.getElementById('batch-po-select').value = '';
        }
    }

    function createBatchItemRow(selectedId, qtyVal) {
        var tpl = document.getElementById('tpl-batch-item-row');
        var fragment = tpl.content.cloneNode(true);
        var row = fragment.querySelector('.inline-item-row');
        var select = row.querySelector('select');
        var qtyInput = row.querySelector('input[type="number"]');
        if (selectedId) select.value = String(selectedId);
        if (qtyVal) qtyInput.value = Number(qtyVal);
        return row;
    }

    function addBatchItemRow() {
        document.getElementById('batch-items-container').appendChild(createBatchItemRow('', 50));
    }

    function removeBatchItemRow(btn) {
        var container = document.getElementById('batch-items-container');
        if (container.querySelectorAll('.inline-item-row').length > 1) {
            btn.closest('.inline-item-row').remove();
        } else {
            alert('Minimal harus terdapat 1 produk pada batch produksi.');
        }
    }

    function prefillItemsFromPo() {
        var poId = document.getElementById('batch-po-select').value;
        var container = document.getElementById('batch-items-container');
        var po = poBatchUiMap[String(poId)];
        container.textContent = '';
        if (po && po.items && po.items.length > 0) {
            po.items.forEach(function (item) {
                container.appendChild(createBatchItemRow(item.id_produk_jadi, item.qty));
            });
        } else {
            container.appendChild(createBatchItemRow('', 50));
        }
    }

    function openBatchModal(mode) {
        switchBatchMode(mode);
        syncBatchDate(document.getElementById('batch-tgl-hidden').value || '{{ date('Y-m-d') }}');
        openDialog('modal-log-produksi');
    }

    function openCompleteQcModal(batchId) {
        var data = batchModalMap[String(batchId)];
        if (!data) return;

        document.getElementById('form-complete-qc').action = '/log-produksi/complete-qc/' + batchId;
        document.getElementById('qc-modal-subtitle').textContent = data.kode + ' \u00B7 ' + data.tanggal + ' (' + data.shift + ')';
        document.getElementById('qc-info-kode').textContent = data.kode + ' (' + data.shift + ')';
        document.getElementById('qc-info-po').textContent = data.po_kode + ' \u2014 ' + data.pelanggan;
        document.getElementById('qc-grade-b').value = 0;
        document.getElementById('qc-ket').value = '';

        var list = document.getElementById('qc-items-list');
        list.textContent = '';
        data.items.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'modal-item';

            var left = document.createElement('div');
            var nameEl = document.createElement('b');
            nameEl.textContent = item.produk_nama;
            var metaEl = document.createElement('small');
            metaEl.textContent = item.netto + ' \u00B7 Target rencana: ' + Number(item.target_qty).toLocaleString('id-ID') + ' pcs';
            left.appendChild(nameEl);
            left.appendChild(metaEl);

            var right = document.createElement('div');
            right.className = 'modal-item-right';
            var inp = document.createElement('input');
            inp.type = 'number';
            inp.name = 'grade_a_qty[' + item.detail_id + ']';
            inp.min = '0';
            inp.required = true;
            inp.value = String(item.target_qty);
            inp.className = 'form-input is-qty';
            inp.setAttribute('aria-label', 'Grade A ' + item.produk_nama);
            right.appendChild(inp);

            li.appendChild(left);
            li.appendChild(right);
            list.appendChild(li);
        });

        openDialog('modal-complete-qc');
    }

    function openQcSummaryModal(batchId) {
        var data = batchModalMap[String(batchId)];
        if (!data) return;

        document.getElementById('qcs-subtitle').textContent = data.kode + ' \u00B7 Lulus Quality Control';
        document.getElementById('qcs-batch').textContent = data.kode + ' (' + data.tanggal + ', ' + data.shift + ')';
        document.getElementById('qcs-po').textContent = data.po_kode + ' \u2014 ' + data.pelanggan;
        document.getElementById('qcs-grades').textContent =
            Number(data.grade_a).toLocaleString('id-ID') + ' Grade A / ' +
            Number(data.grade_b).toLocaleString('id-ID') + ' Grade B';
        document.getElementById('qcs-ket').textContent = data.ket || '-';

        var list = document.getElementById('qcs-items');
        list.textContent = '';
        data.items.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'modal-item';

            var left = document.createElement('div');
            var b = document.createElement('b');
            b.textContent = item.produk_nama;
            var sm = document.createElement('small');
            sm.textContent = item.netto;
            left.appendChild(b);
            left.appendChild(sm);

            var qty = document.createElement('div');
            qty.className = 'modal-qty';
            qty.textContent = Number(item.target_qty).toLocaleString('id-ID') + ' ';
            var u = document.createElement('small');
            u.textContent = 'pcs target';
            qty.appendChild(u);

            li.appendChild(left);
            li.appendChild(qty);
            list.appendChild(li);
        });

        openDialog('modal-qc-summary');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (selectedPoOnLoad) {
            switchBatchMode('po');
            var sel = document.getElementById('batch-po-select');
            sel.value = String(selectedPoOnLoad);
            prefillItemsFromPo();
            openDialog('modal-log-produksi');
        }
    });
</script>
@endpush
