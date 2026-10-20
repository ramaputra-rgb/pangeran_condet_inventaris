@extends('layouts.app')

@section('content')

<!-- Header Modul -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Log Produksi Dapur & Quality Control (QC)</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pencatatan batch penggorengan kuali, konversi bahan ke produk jadi, serta verifikasi hasil QC Dapur Condet.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-log-produksi').classList.remove('hidden')" 
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

<!-- Modal Pop-up Input Batch Produksi Baru UI/UX Lama -->
<div id="modal-log-produksi" class="{{ isset($selectedPoNo) ? '' : 'hidden' }} fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Input Batch Produksi & QC Dapur</h3>
            <button onclick="document.getElementById('modal-log-produksi').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('log-produksi.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Acuan PO Pelanggan</label>
                    <select name="no_po_produk" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold text-pc-dark">
                        @foreach($poList as $po)
                            <option value="{{ $po->no_po_produk }}" {{ (isset($selectedPoNo) && $selectedPoNo == $po->no_po_produk) ? 'selected' : '' }}>
                                PO-00{{ $po->no_po_produk }} - {{ $po->nama_pelanggan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tanggal Produksi</label>
                    <input type="date" name="tgl_produksi" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange font-bold">
                </div>
            </div>

            <!-- MULTI-ITEM PRODUK CONTAINER -->
            <div class="border border-gray-100 rounded-2xl p-3 bg-gray-50/50 space-y-2">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-black text-pc-dark text-xs">Varian Produksi Digoreng:</span>
                    <button type="button" onclick="addGorengRow()" class="px-2.5 py-1 bg-pc-maroon text-white text-[10px] font-bold rounded-lg cursor-pointer hover:bg-red-800 transition">+ Tambah Varian</button>
                </div>

                <div id="goreng-row-container" class="space-y-2">
                    <div class="flex items-center gap-2 goreng-item">
                        <select name="produk_ids[]" required class="w-2/3 border border-gray-200 rounded-xl px-2 py-1.5 bg-white font-bold text-gray-700">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id_produk_jadi }}">{{ $prod->nama_produk }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="produk_qty[]" required min="1" placeholder="Qty (Pcs)" class="w-1/3 border border-gray-200 rounded-xl px-2 py-1.5 font-bold">
                        <button type="button" onclick="removeGorengRow(this)" class="text-red-500 font-bold px-1 hover:text-red-700">✕</button>
                    </div>
                </div>
            </div>

            <!-- QC CONTAINER (GRADE A & GRADE B REMAHAN) -->
            <div class="grid grid-cols-2 gap-3 p-3 bg-amber-50/40 border border-amber-200/60 rounded-2xl">
                <div>
                    <label class="font-bold text-emerald-800 block mb-1">✓ Grade A (Lolos QC / Utuh)</label>
                    <input type="number" name="jml_grade_A" required min="0" placeholder="Total Pcs Grade A" class="w-full border border-emerald-300 rounded-xl px-3 py-2 font-bold bg-white text-emerald-800">
                </div>
                <div>
                    <label class="font-bold text-amber-800 block mb-1">⚠️ Grade B (Remahan / Pecah)</label>
                    <input type="number" name="jml_grade_B" value="0" min="0" placeholder="Total Pcs Remahan" class="w-full border border-amber-300 rounded-xl px-3 py-2 font-bold bg-white text-amber-800">
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Keterangan / Catatan QC</label>
                <textarea name="ket" rows="2" placeholder="Catatan kuali, remahan, dll" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange"></textarea>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-log-produksi').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan Log Produksi</button>
            </div>
        </form>
    </div>
</div>

<script>
function addGorengRow() {
    const container = document.getElementById('goreng-row-container');
    const firstRow = container.children[0];
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('input[name="produk_qty[]"]').value = '';
    container.appendChild(newRow);
}

function removeGorengRow(btn) {
    const container = document.getElementById('goreng-row-container');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    }
}
</script>

@endsection