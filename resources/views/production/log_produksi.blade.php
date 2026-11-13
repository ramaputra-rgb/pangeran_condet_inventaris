@extends('layouts.app')

@section('content')

<!-- Header Modul -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Log Produksi Dapur & Quality Control (QC)</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Pencatatan batch penggorengan kuali, konversi bahan ke produk jadi, serta verifikasi hasil QC Dapur Condet.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button type="button" onclick="openManualStockBatchModal()" class="btn btn-primary">
            + Input Batch Produksi Baru
        </button>

        <button onclick="window.print()" class="btn btn-outline">
            🖨️ Cetak Laporan Log
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar Konsisten -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 no-print">
    <div class="card-summary" style="border-left: 4px solid #10b981;">
        <p class="card-title uppercase" style="color: #059669;">TOTAL GRADE A (LOLOS QC)</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($logProduksi->where('status_approval', 'APPROVED')->sum('jml_grade_A')) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Masuk Stok Ready Gudang</p>
    </div>

    <div class="card-summary border-l-amber">
        <p class="card-title uppercase" style="color: #d97706;">GRADE B (REMAHAN / PECAH)</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($logProduksi->where('status_approval', 'APPROVED')->sum('jml_grade_B')) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #d97706;">⚠️ Dijual Eceran / Non-Grade</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid var(--pc-orange);">
        <p class="card-title uppercase" style="color: var(--pc-orange);">SEDANG PROSES GORENG</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $logProduksi->where('status_approval', '!=', 'APPROVED')->count() }} <span style="font-size:0.75rem; color: var(--text-muted);">Batch</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #d97706;">🔥 Menunggu Selesai & Input QC</p>
    </div>

    <div class="card-summary">
        <p class="card-title uppercase" style="color: var(--pc-maroon);">SELESAI & LOLOS QC</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $logProduksi->where('status_approval', 'APPROVED')->count() }} <span style="font-size:0.75rem; color: var(--text-muted);">Batch</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Terverifikasi Head Kitchen</p>
    </div>
</div>

<!-- Tabel Riwayat Log Produksi -->
<div class="table-container mt-6">
    <div class="table-header-bar">
        <span class="font-bold" style="color: var(--pc-dark);">Daftar Batch Produksi & Hasil Quality Control (QC)</span>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="white-space: nowrap;">NO. BATCH & TGL</th>
                    <th>SUMBER / ACUAN PRODUKSI</th>
                    <th>PRODUK YANG DIGORENG</th>
                    <th class="text-center">GRADE A (UTUH)</th>
                    <th class="text-center">GRADE B (REMAHAN)</th>
                    <th class="text-center">STATUS PRODUKSI & QC</th>
                    <th>KETERANGAN</th>
                    <th class="text-center no-print">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logProduksi as $log)
                @php
                    $isSelesaiQc = strtoupper($log->status_approval) === 'APPROVED';
                    $isInternalStock = !$log->no_po_produk || ($log->poProduk && $log->poProduk->nama_pelanggan === 'STOK INTERNAL GUDANG');
                @endphp
                <tr>
                    <td class="font-bold" style="white-space: nowrap;">
                        BATCH-00{{ $log->no_batch }}
                        <p style="font-size: 0.625rem; color: var(--text-muted); font-weight: normal; margin: 0.125rem 0 0 0;">
                            📅 {{ \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') }}
                        </p>
                    </td>
                    <td>
                        @if($isInternalStock)
                            <span class="badge badge-blue">🏭 STOK GUDANG</span>
                            <p class="font-bold" style="font-size: 0.625rem; color: #6b7280; margin: 0.125rem 0 0 0;">Produksi Mandiri (Tanpa PO)</p>
                        @else
                            <span class="font-black" style="color: var(--pc-maroon);">PO-00{{ $log->no_po_produk }}</span>
                            <p class="font-bold" style="font-size: 0.625rem; color: #6b7280; margin: 0.125rem 0 0 0;">{{ $log->poProduk->nama_pelanggan ?? '-' }}</p>
                        @endif
                    </td>
                    <td>
                        @forelse($log->detailProduksi as $detail)
                            <div class="flex items-center justify-between gap-2 mb-1 p-1" style="background-color: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                                <span class="font-bold" style="color: var(--pc-dark);">{{ $detail->produkJadi->nama_produk ?? 'Produk' }}</span>
                                <span class="badge {{ $isSelesaiQc ? 'badge-emerald' : 'badge-amber' }}">
                                    {{ $isSelesaiQc ? 'Hasil:' : 'Target:' }} {{ number_format($detail->jml_produk_jadi) }} Pcs
                                </span>
                            </div>
                        @empty
                            <span style="color: var(--text-muted); font-style: italic; font-size: 0.625rem;">Belum ada detail produk</span>
                        @endforelse
                    </td>
                    <td class="text-center font-black" style="color: {{ $isSelesaiQc ? '#059669' : 'var(--text-muted)' }}; font-size: 0.875rem;">
                        @if($isSelesaiQc)
                            {{ number_format($log->jml_grade_A) }} Pcs
                        @else
                            <span style="font-size: 0.6875rem; font-weight: normal;">Menunggu QC</span>
                        @endif
                    </td>
                    <td class="text-center font-black" style="color: {{ $isSelesaiQc ? '#d97706' : 'var(--text-muted)' }}; font-size: 0.875rem;">
                        @if($isSelesaiQc)
                            {{ number_format($log->jml_grade_B) }} Pcs
                        @else
                            <span style="font-size: 0.6875rem; font-weight: normal;">-</span>
                        @endif
                    </td>
                    <td class="text-center" style="white-space: nowrap;">
                        @if($isSelesaiQc)
                            <span class="badge badge-emerald">
                                ✓ SELESAI & LOLOS QC
                            </span>
                        @else
                            <span class="badge badge-amber">
                                🔥 PROSES PRODUKSI
                            </span>
                        @endif
                    </td>
                    <td style="color: var(--text-muted); font-style: italic; font-size: 0.6875rem;">
                        {{ $log->ket ?? '-' }}
                    </td>
                    <td class="text-center no-print" style="white-space: nowrap;">
                        @if(!$isSelesaiQc)
                            <div class="flex flex-col gap-1 items-center">
                                <button type="button" onclick="openCompleteQcModal({{ $log->no_batch }})" class="btn btn-success" style="padding: 0.25rem 0.5rem;">
                                    ✅ Selesai & Input QC
                                </button>
                                <form action="{{ route('log-produksi.destroy', $log->no_batch) }}" method="POST" onsubmit="return confirm('Batalkan dan hapus batch produksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.2rem 0.5rem; font-size: 0.625rem;">
                                        🗑️ Batal
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="font-bold" style="font-size: 0.6875rem; color: #059669;">✓ Stok Masuk Gudang</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada catatan log produksi dapur.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAHAP 1: INPUT REQUEST BATCH PRODUKSI DAPUR (2 MODE: DARI PO vs STOK GUDANG) -->
<div id="modal-log-produksi" class="modal-overlay {{ isset($selectedPoNo) && $selectedPoNo ? '' : 'hidden' }}">
    <div class="modal-box" style="max-width: 34rem; max-height: 92vh; overflow-y: auto;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <div>
                <h3 class="font-black text-sm" id="modal-batch-title" style="margin:0; color: var(--pc-dark);">
                    🔥 Input Batch Produksi Dapur
                </h3>
                <p id="modal-batch-subtitle" style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0;">
                    Tahap 1: Penjadwalan penggorengan kuali. Hasil Grade A & Grade B diinput setelah produksi selesai.
                </p>
            </div>
            <button type="button" onclick="closeLogProduksiModal()" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('log-produksi.store') }}" method="POST" id="form-log-produksi" style="margin-top: 1rem;">
            @csrf
            <input type="hidden" name="mode_produksi" id="input-mode-produksi" value="{{ isset($selectedPoNo) && $selectedPoNo ? 'po' : 'stok_gudang' }}">

            <!-- SECTION A: ACUAN PO PELANGGAN (HANYA MUNCUL JIKA DIRECT DARI PO PELANGGAN) -->
            <div id="section-mode-po" class="{{ isset($selectedPoNo) && $selectedPoNo ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold">Acuan PO Pelanggan <span style="color: #ef4444;">*</span></label>
                        <select id="select-po-produk" name="no_po_produk" onchange="onPoChanged()" class="form-input font-bold" style="background:#fff; color: var(--pc-dark);">
                            @foreach($poList as $po)
                                <option value="{{ $po->no_po_produk }}"
                                    {{ (isset($selectedPoNo) && $selectedPoNo == $po->no_po_produk) ? 'selected' : '' }}>
                                    PO-00{{ $po->no_po_produk }} - {{ $po->nama_pelanggan }} ({{ $po->jumlah_po }} Pcs)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-bold">Tanggal Mulai Produksi <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="tgl-produksi-po" value="{{ date('Y-m-d') }}" onchange="syncTanggalProduksi(this.value)" class="form-input font-bold" style="color: var(--pc-dark);">
                    </div>
                </div>

                <!-- CARD DETAIL PO TERPILIH -->
                <div id="po-info-banner" class="mt-2" style="padding: 0.75rem; background: linear-gradient(to right, #fffbeb, #fef3c7); border: 1px solid #fde68a; border-radius: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span id="po-badge-tipe" class="badge" style="background-color: var(--pc-maroon); color: #fff;">Grosir</span>
                            <span id="po-nama-pelanggan" class="font-black" style="color: var(--pc-dark); font-size: 0.75rem;">Memuat data PO...</span>
                        </div>
                        <p style="font-size: 0.625rem; color: #6b7280; margin: 0.25rem 0 0 0;">
                            Target Total PO: <span id="po-target-total" class="font-bold" style="color: var(--pc-maroon);">0 Pcs</span>
                            • Penerima: <span id="po-nama-penerima" class="font-bold" style="color: #374151;">-</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-1 badge badge-emerald" style="align-self: flex-start;">
                        <span>✓ Tersinkron Otomatis dari PO Terpilih</span>
                    </div>
                </div>

                <!-- DAFTAR VARIAN PRODUK DARI PO -->
                <div class="mt-2" style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 0.75rem; background-color: rgba(249, 250, 251, 0.6);">
                    <div class="flex justify-between items-center" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                        <div>
                            <span class="font-black" style="color: var(--pc-dark); font-size: 0.75rem;">Daftar Produk yang Akan Digoreng (Acuan PO):</span>
                            <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0;">Target jumlah disesuaikan dengan sisa kebutuhan PO pelanggan.</p>
                        </div>
                    </div>

                    <div id="goreng-row-container" class="flex flex-col gap-2">
                        <!-- Diisi otomatis via JS -->
                    </div>
                </div>
            </div>

            <!-- SECTION B: PRODUKSI STOK GUDANG MANDIRI (TANPA ACUAN PO) -->
            <div id="section-mode-stok" class="{{ isset($selectedPoNo) && $selectedPoNo ? 'hidden' : '' }}">
                <div style="padding: 0.625rem 0.75rem; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.75rem; font-size: 0.6875rem; color: #1e40af; margin-bottom: 0.75rem;">
                    🏭 <strong>Mode Produksi Stok Gudang (Tanpa Acuan PO):</strong> Digunakan untuk menambah stok persediaan gudang (Stock Opname / Buffer Stock) tanpa terikat pesanan PO tertentu.
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-bold">Tanggal Mulai Produksi <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="tgl-produksi-stok" value="{{ date('Y-m-d') }}" onchange="syncTanggalProduksi(this.value)" class="form-input font-bold" style="color: var(--pc-dark);">
                    </div>
                    <div>
                        <label class="font-bold">Shift / Waktu Goreng</label>
                        <select name="waktu_produksi" class="form-input font-bold" style="background:#fff;">
                            <option value="Shift Pagi">Shift Pagi</option>
                            <option value="Shift Siang">Shift Siang</option>
                            <option value="Shift Sore">Shift Sore</option>
                        </select>
                    </div>
                </div>

                <div class="mt-2" style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 0.75rem; background-color: rgba(249, 250, 251, 0.6);">
                    <div class="flex justify-between items-center" style="margin-bottom: 0.5rem;">
                        <span class="font-black" style="color: var(--pc-dark); font-size: 0.75rem;">Pilih Varian Rengginang & Target Goreng:</span>
                        <button type="button" onclick="addManualProdukRow()" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.5625rem;">+ Tambah Varian</button>
                    </div>

                    <div id="manual-stok-row-container" class="flex flex-col gap-2">
                        <!-- Diisi via JS saat mode stok gudang aktif -->
                    </div>
                </div>
            </div>

            <input type="hidden" name="tgl_produksi" id="final-tgl-produksi" value="{{ date('Y-m-d') }}">

            <!-- INFO TAHAPAN PRODUKSI (BELUM ADA GRADE A/B) -->
            <div class="mt-2" style="padding: 0.625rem 0.75rem; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem; font-size: 0.6875rem; color: #92400e;">
                ℹ️ <strong>Catatan Alur QC:</strong> Batch ini akan masuk dengan status <strong>🔥 PROSES PRODUKSI</strong>. Pengisian hasil riil <strong>Grade A (Utuh)</strong> dan <strong>Grade B (Remahan)</strong> dilakukan setelah proses penggorengan selesai melalui tombol <em>"Selesai & Input QC"</em>.
            </div>

            <div class="mt-2">
                <label class="font-bold">Catatan Dapur / Kuali</label>
                <textarea id="input-ket" name="ket" rows="2" placeholder="Misal: Kuali 1 & 2, penjemuran batch pagi..." class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4" style="border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                <button type="button" onclick="closeLogProduksiModal()" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">🔥 Mulai Proses Produksi</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAHAP 2: SELESAI PRODUKSI & INPUT HASIL QC (GRADE A & GRADE B) -->
<div id="modal-qc-selesai" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 34rem; max-height: 92vh; overflow-y: auto;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <div>
                <h3 class="font-black text-sm" id="qc-modal-title" style="margin:0; color: #059669;">
                    ✅ Selesai Produksi & Input Hasil QC
                </h3>
                <p id="qc-modal-subtitle" style="font-size: 0.6875rem; color: var(--text-muted); margin: 0.125rem 0 0 0;"></p>
            </div>
            <button type="button" onclick="document.getElementById('modal-qc-selesai').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-complete-qc" method="POST" style="margin-top: 1rem;">
            @csrf

            <div style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 0.75rem; background-color: #f9fafb;">
                <span class="font-black" style="color: var(--pc-dark); font-size: 0.75rem;">Input Hasil Riil Lolos QC (Grade A / Utuh) per Varian:</span>
                <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0.5rem 0;">Masukkan jumlah kemasan utuh yang lolos QC untuk ditambahkan ke stok gudang.</p>

                <div id="qc-items-container" class="flex flex-col gap-2">
                    <!-- Diisi via JS -->
                </div>
            </div>

            <div class="mt-3" style="padding: 0.75rem; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem;">
                <label class="font-bold block" style="color: #92400e; margin-bottom: 0.25rem;">⚠️ Hasil Grade B (Remahan / Pecah) — Pcs</label>
                <input type="number" name="jml_grade_B" id="qc-input-grade-b" value="0" min="0" required class="form-input font-black" style="background: #fff; color: #92400e;">
                <p style="font-size: 0.5625rem; color: var(--text-muted); margin: 0.25rem 0 0 0;">Otomatis menambah stok Rengginang Remahan (Grade B) jika lebih dari 0.</p>
            </div>

            <div class="mt-2">
                <label class="font-bold">Catatan Verifikasi Quality Control (QC)</label>
                <textarea name="ket" id="qc-input-ket" rows="2" placeholder="Misal: Matang merata, kemasan tersegel rapi" class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4" style="border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                <button type="button" onclick="document.getElementById('modal-qc-selesai').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-success">💾 Simpan Hasil QC & Tambah Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
const poDataMap = @json($poDataMap ?? []);
const batchQcMap = @json($batchQcMap ?? []);
const allProducts = @json($products ?? []);
const initialSelectedPo = @json($selectedPoNo ?? null);

function syncTanggalProduksi(val) {
    document.getElementById('final-tgl-produksi').value = val;
    const poDate = document.getElementById('tgl-produksi-po');
    const stokDate = document.getElementById('tgl-produksi-stok');
    if (poDate) poDate.value = val;
    if (stokDate) stokDate.value = val;
}

function closeLogProduksiModal() {
    const modal = document.getElementById('modal-log-produksi');
    if (modal) modal.classList.add('hidden');
}

/**
 * BUKA MODAL MODE MANUAL STOK GUDANG (TANPA ACUAN PO)
 */
function openManualStockBatchModal() {
    document.getElementById('input-mode-produksi').value = 'stok_gudang';
    document.getElementById('section-mode-po').classList.add('hidden');
    document.getElementById('section-mode-stok').classList.remove('hidden');

    // Kosongkan container PO agar input hidden-nya tidak ikut terkirim
    document.getElementById('goreng-row-container').innerHTML = '';
    const selectPo = document.getElementById('select-po-produk');
    if (selectPo) selectPo.removeAttribute('required');

    // Reset dan isi 1 baris pilihan produk manual
    const manualContainer = document.getElementById('manual-stok-row-container');
    manualContainer.innerHTML = '';
    addManualProdukRow();

    document.getElementById('input-ket').value = '';
    document.getElementById('modal-log-produksi').classList.remove('hidden');
}

function addManualProdukRow() {
    const container = document.getElementById('manual-stok-row-container');
    let optionsHtml = '';
    allProducts.forEach(p => {
        optionsHtml += `<option value="${p.id_produk_jadi}">${p.nama_produk} (Stok: ${p.stok})</option>`;
    });

    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
        <select name="produk_ids[]" required class="form-input font-bold" style="width: 65%; margin:0; background:#fff;">
            ${optionsHtml}
        </select>
        <input type="number" name="produk_qty[]" required min="1" placeholder="Target Qty" class="form-input" style="width: 30%; margin:0;">
        <button type="button" onclick="removeManualProdukRow(this)" style="color: #ef4444; font-weight: bold; background: none; border: none; cursor: pointer;">✕</button>
    `;
    container.appendChild(row);
}

function removeManualProdukRow(btn) {
    const container = document.getElementById('manual-stok-row-container');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    }
}

/**
 * MODE ACUAN PO PELANGGAN (SAAT DIKLIK DIRECT DARI MODUL PO PELANGGAN)
 */
function onPoChanged() {
    const select = document.getElementById('select-po-produk');
    if (!select) return;

    const selectedPoId = select.value;
    const po = poDataMap[selectedPoId];
    const container = document.getElementById('goreng-row-container');
    const infoBanner = document.getElementById('po-info-banner');
    const ketInput = document.getElementById('input-ket');

    // Kosongkan container manual stok agar tidak bentrok
    document.getElementById('manual-stok-row-container').innerHTML = '';

    if (!po) {
        if (infoBanner) infoBanner.classList.add('hidden');
        if (container) {
            container.innerHTML = '<p style="font-size: 0.75rem; color: var(--text-muted); font-style: italic; text-align: center; padding: 0.5rem;">Data PO tidak ditemukan.</p>';
        }
        return;
    }

    if (infoBanner) {
        infoBanner.classList.remove('hidden');
        document.getElementById('po-badge-tipe').textContent = po.tipe_jual || 'Pesanan';
        document.getElementById('po-nama-pelanggan').textContent = po.nama_pelanggan || ('PO-00' + po.no_po_produk);
        document.getElementById('po-target-total').textContent = (po.jumlah_po || 0) + ' Pcs';
        document.getElementById('po-nama-penerima').textContent = po.nama_penerima || po.nama_pelanggan || '-';
    }

    if (ketInput) {
        ketInput.value = 'Batch penggorengan dapur untuk PO-00' + po.no_po_produk + ' (' + po.nama_pelanggan + ')';
    }

    if (container) {
        container.innerHTML = '';
        if (po.items && po.items.length > 0) {
            po.items.forEach(item => {
                const row = document.createElement('div');
                row.style.cssText = 'padding: 0.625rem 0.75rem; background-color: #ffffff; border-radius: 0.75rem; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;';
                row.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span style="width: 0.625rem; height: 0.625rem; border-radius: 9999px; background-color: var(--pc-maroon); flex-shrink: 0;"></span>
                        <div>
                            <p class="font-black" style="color: var(--pc-dark); font-size: 0.75rem; margin:0;">${item.nama_produk}</p>
                            <p style="font-size: 0.625rem; color: var(--text-muted); margin:0;">Target PO: ${item.target_qty} Pcs</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <input type="hidden" name="produk_ids[]" value="${item.id_produk_jadi}">
                        <input type="number" name="produk_qty[]" value="${item.qty_default}" min="1" required class="form-input font-black text-center" style="width: 5rem; margin:0; padding: 0.25rem 0.5rem;">
                        <span style="font-size: 0.6875rem; font-weight: bold; color: var(--text-muted);">Pcs</span>
                    </div>
                `;
                container.appendChild(row);
            });
        }
    }
}

/**
 * BUKA MODAL TAHAP 2: SELESAI PRODUKSI & INPUT HASIL QC (GRADE A & B)
 */
function openCompleteQcModal(noBatch) {
    const batch = batchQcMap[noBatch];
    if (!batch) return;

    document.getElementById('qc-modal-title').innerText = '✅ Selesai Produksi & Input QC — ' + batch.kode_batch;
    document.getElementById('qc-modal-subtitle').innerText = 'Tanggal: ' + batch.tgl_produksi + ' | Sumber: ' + batch.sumber;
    document.getElementById('form-complete-qc').action = '/log-produksi/complete-qc/' + noBatch;
    document.getElementById('qc-input-grade-b').value = 0;
    document.getElementById('qc-input-ket').value = batch.ket || '';

    const container = document.getElementById('qc-items-container');
    container.innerHTML = '';

    batch.items.forEach(item => {
        const row = document.createElement('div');
        row.style.cssText = 'padding: 0.625rem 0.75rem; background-color: #ffffff; border-radius: 0.75rem; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;';
        row.innerHTML = `
            <div>
                <p class="font-black" style="color: var(--pc-dark); font-size: 0.75rem; margin:0;">${item.nama_produk}</p>
                <p style="font-size: 0.625rem; color: var(--text-muted); margin:0;">Target Digoreng: <strong>${item.target_qty} Pcs</strong></p>
            </div>
            <div class="flex items-center gap-1">
                <input type="number" name="grade_a_qty[${item.detail_id}]" min="0" required placeholder="Hasil Utuh" class="form-input font-black text-center" style="width: 6.5rem; margin:0; padding: 0.35rem 0.5rem; border-color: #a7f3d0;">
                <span style="font-size: 0.6875rem; font-weight: bold; color: #059669;">Pcs</span>
            </div>
        `;
        container.appendChild(row);
    });

    document.getElementById('modal-qc-selesai').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    if (initialSelectedPo) {
        document.getElementById('input-mode-produksi').value = 'po';
        document.getElementById('section-mode-po').classList.remove('hidden');
        document.getElementById('section-mode-stok').classList.add('hidden');
        const selectPo = document.getElementById('select-po-produk');
        if (selectPo) {
            selectPo.setAttribute('required', 'required');
            selectPo.value = initialSelectedPo;
        }
        onPoChanged();
    }
});
</script>

@endsection