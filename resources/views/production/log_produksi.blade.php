@extends('layouts.app')

@section('content')

<!-- Header Modul -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Log Produksi Dapur & Quality Control (QC)</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pencatatan batch penggorengan kuali, konversi bahan ke produk jadi, serta verifikasi hasil QC Dapur Condet.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="openLogProduksiModal()" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Input Batch Produksi Baru</span>
        </button>

        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span class="text-xs font-bold text-gray-700">Cetak Laporan Log</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar Konsisten -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-emerald-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">TOTAL GRADE A (LOLOS QC)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($logProduksi->sum('jml_grade_A')) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Masuk Stok Ready Pengiriman</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">GRADE B (REMAHAN / PECAH)</p>
            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg"><i data-lucide="alert-triangle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($logProduksi->sum('jml_grade_B')) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-amber-600 font-bold mt-2">⚠️ Dijual Eceran / Non-Grade</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-orange">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">TOTAL BATCH DIGORENG</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="flame" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $logProduksi->count() }} <span class="text-xs font-semibold text-gray-400">Batch</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">🔥 Dapur Aktif</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">STATUS APPROVAL</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="shield-check" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $logProduksi->where('status_approval', 'APPROVED')->count() }} <span class="text-xs font-semibold text-gray-400">Batch</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Terverifikasi Head Kitchen</p>
    </div>
</div>

<!-- Tabel Riwayat Log Produksi UI/UX Lama -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">Daftar Batch Produksi & Hasil Quality Control (QC)</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">NO. BATCH & TGL</th>
                <th class="p-4">ACUAN PO PELANGGAN</th>
                <th class="p-4">PRODUK YANG DIGORENG</th>
                <th class="p-4 text-center">GRADE A (UTUH)</th>
                <th class="p-4 text-center">GRADE B (REMAHAN)</th>
                <th class="p-4 text-center">STATUS QC</th>
                <th class="p-4">KETERANGAN</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($logProduksi as $log)
            <tr class="hover:bg-gray-50/50">
                <td class="p-4 font-bold text-pc-dark">
                    BATCH-00{{ $log->no_batch }}
                    <p class="text-[10px] text-gray-400 font-normal">
                        📅 {{ \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') }}
                    </p>
                </td>
                <td class="p-4">
                    <span class="font-extrabold text-pc-maroon">PO-00{{ $log->no_po_produk }}</span>
                    <p class="text-[10px] text-gray-500 font-bold">{{ $log->poProduk->nama_pelanggan ?? '-' }}</p>
                </td>
                <td class="p-4">
                    @forelse($log->detailProduksi as $detail)
                        <div class="flex items-center justify-between gap-2 mb-1 p-1.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-bold text-pc-dark">{{ $detail->produkJadi->nama_produk ?? 'Produk' }}</span>
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-extrabold text-[10px] rounded-lg">
                                {{ number_format($detail->jml_produk_jadi) }} Pcs
                            </span>
                        </div>
                    @empty
                        <span class="text-gray-400 italic text-[10px]">Belum ada detail produk</span>
                    @endforelse
                </td>
                <td class="p-4 text-center font-black text-emerald-600 text-sm">
                    {{ number_format($log->jml_grade_A) }} Pcs
                </td>
                <td class="p-4 text-center font-black text-amber-600 text-sm">
                    {{ number_format($log->jml_grade_B) }} Pcs
                </td>
                <td class="p-4 text-center">
                    @if($log->status_approval === 'APPROVED' || $log->status_approval === 'Approved')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                            ✓ APPROVED
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1">
                            ⏳ PENDING
                        </span>
                    @endif
                </td>
                <td class="p-4 text-gray-500 italic text-[11px]">
                    {{ $log->ket ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-6 text-center text-gray-400">Belum ada catatan log produksi dapur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Pop-up Input Batch Produksi Baru UI/UX Terintegrasi Otomatis -->
<div id="modal-log-produksi" class="{{ isset($selectedPoNo) ? '' : 'hidden' }} fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-xl max-h-[92vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <div>
                <h3 class="font-extrabold text-pc-dark text-sm flex items-center gap-2">
                    <span class="p-1 bg-amber-100 text-pc-maroon rounded-lg"><i data-lucide="flame" class="w-4 h-4"></i></span>
                    Input Batch Produksi & QC Dapur
                </h3>
                <p class="text-[10px] text-gray-400 mt-0.5">Otomatis sinkron dengan pesanan barang rengginang pelanggan untuk konsistensi data.</p>
            </div>
            <button type="button" onclick="closeLogProduksiModal()" class="text-gray-400 hover:text-gray-600 font-bold p-1">✕</button>
        </div>

        <form action="{{ route('log-produksi.store') }}" method="POST" id="form-log-produksi" class="space-y-4 text-xs">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Acuan PO Pelanggan <span class="text-red-500">*</span></label>
                    <select id="select-po-produk" name="no_po_produk" required onchange="onPoChanged()" 
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold text-pc-dark">
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
                    <label class="font-bold text-gray-700 block mb-1">Tanggal Produksi <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_produksi" value="{{ date('Y-m-d') }}" required 
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange font-bold text-pc-dark">
                </div>
            </div>

            <!-- CARD DETAIL PO TERPILIH (INFO PELANGGAN & STATUS SINKRONISASI) -->
            <div id="po-info-banner" class="p-3 bg-gradient-to-r from-amber-50/70 to-orange-50/50 border border-amber-200/60 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span id="po-badge-tipe" class="text-[9px] font-black uppercase px-2 py-0.5 bg-pc-maroon text-white rounded-md tracking-wider">Grosir</span>
                        <span id="po-nama-pelanggan" class="font-extrabold text-pc-dark text-xs">Memuat data PO...</span>
                    </div>
                    <p class="text-[10px] text-gray-500 font-medium">
                        Target Total PO: <span id="po-target-total" class="font-bold text-pc-maroon">0 Pcs</span> 
                        • Penerima: <span id="po-nama-penerima" class="font-bold text-gray-700">-</span>
                    </p>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 bg-emerald-100/80 border border-emerald-200 px-2.5 py-1 rounded-xl self-start sm:self-center">
                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                    <span>Tersinkron Otomatis</span>
                </div>
            </div>

            <!-- DAFTAR VARIAN PRODUK DARI PO (PLAIN TEXT) -->
            <div class="border border-gray-200/80 rounded-2xl p-3.5 bg-gray-50/60 space-y-2.5">
                <div class="flex justify-between items-center border-b border-gray-200/60 pb-2">
                    <div>
                        <span class="font-black text-pc-dark text-xs block">Daftar Barang Rengginang Pesanan (Acuan PO):</span>
                        <p class="text-[10px] text-gray-400">Otomatis terkunci dari pesanan PO pelanggan agar data konsisten.</p>
                    </div>
                    <span class="text-[9px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                        ✓ Otomatis Tersinkron
                    </span>
                </div>

                <div id="goreng-row-container" class="space-y-2">
                    <!-- Dynamic Rows populated by JS as Plain Text -->
                </div>
            </div>

            <!-- QC CONTAINER (GRADE A & GRADE B REMAHAN) -->
            <div class="grid grid-cols-2 gap-3 p-3.5 bg-amber-50/40 border border-amber-200/60 rounded-2xl">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="font-bold text-emerald-800">✓ Grade A (Lolos QC / Utuh)</label>
                        <span class="text-[9px] text-emerald-600 font-bold bg-white px-1.5 py-0.5 rounded border border-emerald-200">Plain Text</span>
                    </div>
                    <div class="w-full bg-emerald-50/80 border border-emerald-300 rounded-xl px-3 py-2 flex items-center justify-between">
                        <span id="text-grade-a" class="font-black text-emerald-800 text-sm">0 Pcs</span>
                        <span class="text-[9px] text-emerald-700 font-extrabold bg-emerald-100 px-2 py-0.5 rounded">✓ Lolos QC</span>
                    </div>
                    <input type="hidden" id="input-grade-a" name="jml_grade_A" value="0">
                    <p class="text-[9px] text-gray-400 mt-1">Total produk dikurangi remahan Grade B</p>
                </div>
                <div>
                    <label class="font-bold text-amber-800 block mb-1">⚠️ Grade B (Remahan / Pecah)</label>
                    <input type="number" id="input-grade-b" name="jml_grade_B" value="0" min="0" placeholder="0" 
                           oninput="onGradeBChanged()"
                           class="w-full border border-amber-300 rounded-xl px-3 py-2 font-black text-amber-800 bg-white shadow-sm focus:outline-none focus:border-amber-500 text-xs">
                    <p class="text-[9px] text-gray-400 mt-1">Opsional: isi jika ada remahan/pecah</p>
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Keterangan / Catatan QC</label>
                <textarea id="input-ket" name="ket" rows="2" placeholder="Catatan kuali, shift penggorengan, dll" 
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange"></textarea>
            </div>

            <div class="pt-2 flex justify-end space-x-2 border-t border-gray-100">
                <button type="button" onclick="closeLogProduksiModal()" 
                        class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" 
                        class="px-5 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Log Produksi
                </button>
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
    if (window.lucide) {
        lucide.createIcons();
    }
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
            container.innerHTML = '<p class="text-xs text-gray-400 italic text-center py-2">Silakan pilih PO Pelanggan di atas.</p>';
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
            emptyNotice.className = 'p-3 bg-amber-50 text-amber-800 rounded-xl text-xs font-bold border border-amber-200 text-center';
            emptyNotice.innerHTML = '⚠️ PO ini belum memiliki rincian barang rengginang yang tercatat.';
            container.appendChild(emptyNotice);
        }
    }

    recalculateGradeA();
    if (window.lucide) {
        lucide.createIcons();
    }
}

function createProductRowElement({ idProduk = '', namaProduk = '', qty = 0 }) {
    const row = document.createElement('div');
    row.className = 'p-3 bg-white rounded-xl border border-gray-100 flex items-center justify-between shadow-xs goreng-item';

    row.innerHTML = `
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-pc-maroon shrink-0"></span>
            <div>
                <p class="font-extrabold text-pc-dark text-xs">${namaProduk}</p>
                <p class="text-[10px] text-gray-400 font-medium">Varian Pesanan PO Pelanggan</p>
            </div>
        </div>
        <div class="text-right flex items-center gap-2">
            <span class="px-3 py-1 bg-pc-cream text-pc-maroon font-black text-xs rounded-xl border border-pc-orange/20 inline-block">
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

// Inisialisasi otomatis saat DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    onPoChanged();
});
</script>

@endsection