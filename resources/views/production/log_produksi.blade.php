@extends('layouts.app')

@section('content')

<!-- Header Modul -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Log Produksi Dapur & Quality Control (QC)</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Pencatatan batch penggorengan kuali, konversi bahan ke produk jadi, serta verifikasi hasil QC Dapur Condet.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button onclick="openLogProduksiModal()" class="btn btn-primary">
            + Input Batch Produksi Baru
        </button>

        <button onclick="window.print()" class="btn btn-outline">
            🖨️ Cetak Laporan Log
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar Konsisten -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="card-summary" style="border-left: 4px solid #10b981;">
        <p class="card-title uppercase" style="color: #059669;">TOTAL GRADE A (LOLOS QC)</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($logProduksi->sum('jml_grade_A')) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Masuk Stok Ready Pengiriman</p>
    </div>

    <div class="card-summary border-l-amber">
        <p class="card-title uppercase" style="color: #d97706;">GRADE B (REMAHAN / PECAH)</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($logProduksi->sum('jml_grade_B')) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #d97706;">⚠️ Dijual Eceran / Non-Grade</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid var(--pc-orange);">
        <p class="card-title uppercase" style="color: var(--pc-orange);">TOTAL BATCH DIGORENG</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $logProduksi->count() }} <span style="font-size:0.75rem; color: var(--text-muted);">Batch</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">🔥 Dapur Aktif</p>
    </div>

    <div class="card-summary">
        <p class="card-title uppercase" style="color: var(--pc-maroon);">STATUS APPROVAL</p>
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
                    <th>NO. BATCH & TGL</th>
                    <th>ACUAN PO PELANGGAN</th>
                    <th>PRODUK YANG DIGORENG</th>
                    <th class="text-center">GRADE A (UTUH)</th>
                    <th class="text-center">GRADE B (REMAHAN)</th>
                    <th class="text-center">STATUS QC</th>
                    <th>KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logProduksi as $log)
                <tr>
                    <td class="font-bold">
                        BATCH-00{{ $log->no_batch }}
                        <p style="font-size: 0.625rem; color: var(--text-muted); font-weight: normal; margin: 0.125rem 0 0 0;">
                            📅 {{ \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') }}
                        </p>
                    </td>
                    <td>
                        <span class="font-black" style="color: var(--pc-maroon);">PO-00{{ $log->no_po_produk }}</span>
                        <p class="font-bold" style="font-size: 0.625rem; color: #6b7280; margin: 0.125rem 0 0 0;">{{ $log->poProduk->nama_pelanggan ?? '-' }}</p>
                    </td>
                    <td>
                        @forelse($log->detailProduksi as $detail)
                            <div class="flex items-center justify-between gap-2 mb-1 p-1" style="background-color: #f9fafb; border-radius: 0.5rem; border: 1px solid #f3f4f6;">
                                <span class="font-bold" style="color: var(--pc-dark);">{{ $detail->produkJadi->nama_produk ?? 'Produk' }}</span>
                                <span class="badge badge-amber">
                                    {{ number_format($detail->jml_produk_jadi) }} Pcs
                                </span>
                            </div>
                        @empty
                            <span style="color: var(--text-muted); font-style: italic; font-size: 0.625rem;">Belum ada detail produk</span>
                        @endforelse
                    </td>
                    <td class="text-center font-black" style="color: #059669; font-size: 0.875rem;">
                        {{ number_format($log->jml_grade_A) }} Pcs
                    </td>
                    <td class="text-center font-black" style="color: #d97706; font-size: 0.875rem;">
                        {{ number_format($log->jml_grade_B) }} Pcs
                    </td>
                    <td class="text-center">
                        @if(strtolower($log->status_approval) === 'approved')
                            <span class="badge badge-emerald">
                                ✓ APPROVED
                            </span>
                        @else
                            <span class="badge badge-amber">
                                ⏳ PENDING
                            </span>
                        @endif
                    </td>
                    <td style="color: var(--text-muted); font-style: italic; font-size: 0.6875rem;">
                        {{ $log->ket ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada catatan log produksi dapur.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up Input Batch Produksi Baru UI/UX Terintegrasi Otomatis -->
<div id="modal-log-produksi" class="modal-overlay {{ isset($selectedPoNo) ? '' : 'hidden' }}">
    <div class="modal-box" style="max-width: 34rem; max-height: 92vh; overflow-y: auto;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <div>
                <h3 class="font-black text-sm" style="margin:0; color: var(--pc-dark);">
                    🔥 Input Batch Produksi & QC Dapur
                </h3>
                <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0;">Otomatis sinkron dengan pesanan barang rengginang pelanggan untuk konsistensi data.</p>
            </div>
            <button type="button" onclick="closeLogProduksiModal()" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('log-produksi.store') }}" method="POST" id="form-log-produksi" style="margin-top: 1rem;">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div>
                    <label class="font-bold">Acuan PO Pelanggan <span style="color: #ef4444;">*</span></label>
                    <select id="select-po-produk" name="no_po_produk" required onchange="onPoChanged()" class="form-input font-bold" style="background:#fff; color: var(--pc-dark);">
                        @if($poList->isEmpty())
                            <option value="">-- Tidak ada PO Aktif --</option>
                        @else
                            @foreach($poList as $po)
                                <option value="{{ $po->no_po_produk }}" 
                                    {{ ((isset($selectedPoNo) && $selectedPoNo == $po->no_po_produk) || (!isset($selectedPoNo) && $loop->first)) ? 'selected' : '' }}>
                                    PO-00{{ $po->no_po_produk }} - {{ $po->nama_pelanggan }} ({{ $po->jumlah_po }} Pcs)
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="font-bold">Tanggal Produksi <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="tgl_produksi" value="{{ date('Y-m-d') }}" required class="form-input font-bold" style="color: var(--pc-dark);">
                </div>
            </div>

            <!-- CARD DETAIL PO TERPILIH (INFO PELANGGAN & STATUS SINKRONISASI) -->
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
                    <span>✓ Tersinkron Otomatis</span>
                </div>
            </div>

            <!-- DAFTAR VARIAN PRODUK DARI PO -->
            <div class="mt-2" style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 0.75rem; background-color: rgba(249, 250, 251, 0.6);">
                <div class="flex justify-between items-center" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                    <div>
                        <span class="font-black" style="color: var(--pc-dark); font-size: 0.75rem;">Daftar Barang Rengginang Pesanan (Acuan PO):</span>
                        <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0;">Otomatis terkunci dari pesanan PO pelanggan agar data konsisten.</p>
                    </div>
                    <span class="badge badge-emerald">
                        ✓ Otomatis Tersinkron
                    </span>
                </div>

                <div id="goreng-row-container" class="flex flex-col gap-2">
                    <!-- Dynamic Rows populated by JS -->
                </div>
            </div>

            <!-- QC CONTAINER (GRADE A & GRADE B REMAHAN) -->
            <div class="grid grid-cols-2 gap-2 mt-2" style="padding: 0.75rem; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem;">
                <div>
                    <div class="flex justify-between items-center" style="margin-bottom: 0.25rem;">
                        <label class="font-bold" style="color: #065f46;">✓ Grade A (Lolos QC / Utuh)</label>
                    </div>
                    <div class="flex items-center justify-between" style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem; padding: 0.5rem 0.75rem;">
                        <span id="text-grade-a" class="font-black" style="color: #065f46; font-size: 0.875rem;">0 Pcs</span>
                        <span class="badge badge-emerald">✓ Lolos QC</span>
                    </div>
                    <input type="hidden" id="input-grade-a" name="jml_grade_A" value="0">
                    <p style="font-size: 0.5625rem; color: var(--text-muted); margin-top: 0.25rem;">Total produk dikurangi remahan Grade B</p>
                </div>
                <div>
                    <label class="font-bold block" style="color: #92400e; margin-bottom: 0.25rem;">⚠️ Grade B (Remahan / Pecah)</label>
                    <input type="number" id="input-grade-b" name="jml_grade_B" value="0" min="0" placeholder="0" 
                           oninput="onGradeBChanged()"
                           class="form-input font-black" style="color: #92400e; background: #fff; border-color: #fde68a;">
                    <p style="font-size: 0.5625rem; color: var(--text-muted); margin-top: 0.25rem;">Opsional: isi jika ada remahan/pecah</p>
                </div>
            </div>

            <div class="mt-2">
                <label class="font-bold">Keterangan / Catatan QC</label>
                <textarea id="input-ket" name="ket" rows="2" placeholder="Catatan kuali, shift penggorengan, dll" class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4" style="border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                <button type="button" onclick="closeLogProduksiModal()" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Log Produksi</button>
            </div>
        </form>
    </div>
</div>

<script>
const poDataMap = @json($poDataMap ?? []);

function closeLogProduksiModal() {
    const modal = document.getElementById('modal-log-produksi');
    if (modal) modal.classList.add('hidden');
}

function openLogProduksiModal(poId = null) {
    const modal = document.getElementById('modal-log-produksi');
    if (!modal) return;
    
    modal.classList.remove('hidden');
    if (poId) {
        const poSelect = document.getElementById('select-po-produk');
        if (poSelect) {
            poSelect.value = poId;
        }
    }
    onPoChanged();
}

function onPoChanged() {
    const select = document.getElementById('select-po-produk');
    if (!select) return;
    
    const selectedPoId = select.value;
    const po = poDataMap[selectedPoId];
    const container = document.getElementById('goreng-row-container');
    const infoBanner = document.getElementById('po-info-banner');
    const ketInput = document.getElementById('input-ket');

    if (!po) {
        if (infoBanner) infoBanner.classList.add('hidden');
        if (container) {
            container.innerHTML = '<p style="font-size: 0.75rem; color: var(--text-muted); font-style: italic; text-align: center; padding: 0.5rem;">Silakan pilih PO Pelanggan di atas.</p>';
        }
        recalculateGradeA();
        return;
    }

    if (infoBanner) {
        infoBanner.classList.remove('hidden');
        const badgeTipe = document.getElementById('po-badge-tipe');
        const namaPelanggan = document.getElementById('po-nama-pelanggan');
        const targetTotal = document.getElementById('po-target-total');
        const namaPenerima = document.getElementById('po-nama-penerima');

        if (badgeTipe) badgeTipe.textContent = po.tipe_jual || 'Pesanan';
        if (namaPelanggan) namaPelanggan.textContent = po.nama_pelanggan || ('PO-00' + po.no_po_produk);
        if (targetTotal) targetTotal.textContent = (po.jumlah_po || 0) + ' Pcs';
        if (namaPenerima) namaPenerima.textContent = po.nama_penerima || po.nama_pelanggan || '-';
    }

    if (ketInput && (!ketInput.value || ketInput.value.startsWith('Batch penggorengan dapur untuk PO-'))) {
        ketInput.value = 'Batch penggorengan dapur untuk PO-00' + po.no_po_produk + ' (' + po.nama_pelanggan + ')';
    }

    if (container) {
        container.innerHTML = '';

        if (po.items && po.items.length > 0) {
            po.items.forEach(item => {
                const row = createProductRowElement({
                    idProduk: item.id_produk_jadi,
                    namaProduk: item.nama_produk,
                    qty: item.qty_default
                });
                container.appendChild(row);
            });
        } else {
            const emptyNotice = document.createElement('div');
            emptyNotice.style.cssText = 'padding: 0.75rem; background-color: #fffbeb; color: #92400e; border-radius: 0.75rem; font-size: 0.75rem; font-weight: bold; border: 1px solid #fde68a; text-align: center;';
            emptyNotice.innerHTML = '⚠️ PO ini belum memiliki rincian barang rengginang yang tercatat.';
            container.appendChild(emptyNotice);
        }
    }

    recalculateGradeA();
}

function createProductRowElement({ idProduk = '', namaProduk = '', qty = 0 }) {
    const row = document.createElement('div');
    row.style.cssText = 'padding: 0.75rem; background-color: #ffffff; border-radius: 0.75rem; border: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between;';

    row.innerHTML = `
        <div class="flex items-center gap-2">
            <span style="width: 0.625rem; height: 0.625rem; border-radius: 9999px; background-color: var(--pc-maroon); flex-shrink: 0;"></span>
            <div>
                <p class="font-black" style="color: var(--pc-dark); font-size: 0.75rem; margin:0;">${namaProduk}</p>
                <p style="font-size: 0.625rem; color: var(--text-muted); font-weight: 500; margin:0;">Varian Pesanan PO Pelanggan</p>
            </div>
        </div>
        <div class="text-right flex items-center gap-2">
            <span class="badge badge-amber" style="font-size: 0.75rem;">
                ${qty} Pcs
            </span>
            <input type="hidden" name="produk_ids[]" value="${idProduk}">
            <input type="hidden" name="produk_qty[]" value="${qty}">
        </div>
    `;

    return row;
}

function recalculateGradeA() {
    const qtyInputs = document.querySelectorAll('input[name="produk_qty[]"]');
    let totalQty = 0;
    qtyInputs.forEach(input => {
        totalQty += parseInt(input.value) || 0;
    });

    const gradeBInput = document.getElementById('input-grade-b');
    const gradeAInput = document.getElementById('input-grade-a');
    const textGradeA = document.getElementById('text-grade-a');
    
    const gradeB = gradeBInput ? (parseInt(gradeBInput.value) || 0) : 0;
    const gradeA = Math.max(0, totalQty - gradeB);

    if (gradeAInput) gradeAInput.value = gradeA;
    if (textGradeA) textGradeA.textContent = gradeA + ' Pcs';
}

function onGradeBChanged() {
    recalculateGradeA();
}

document.addEventListener('DOMContentLoaded', () => {
    onPoChanged();
});
</script>

@endsection