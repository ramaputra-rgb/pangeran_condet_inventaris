@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Log Produksi & Quality Control (Dapur)</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pengendalian batch penggorengan dapur Condet berdasarkan PO. Pemotongan bahan manual & pemisahan hasil QC Grade A vs Grade B (Remahan).</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-log').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Input Batch Produksi</span>
        </button>

        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span class="text-xs font-bold text-gray-700">Cetak Laporan Produksi</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">TOTAL BATCH PENGGORENGAN</p>
            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg"><i data-lucide="flame" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $totalBatch ?? 0 }} <span class="text-xs font-semibold text-gray-400">Batch</span></h3>
        <p class="text-[10px] text-amber-600 font-bold mt-2">⚡ Terhubung ke PO Pelanggan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">HASIL QC GRADE A (UTAMA)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($totalGradeA ?? 0) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Kemasan Siap Jual (Pouch)</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">HASIL QC GRADE B (REMAHAN)</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="box" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($totalGradeB ?? 0) }} <span class="text-xs font-semibold text-gray-400">Pcs/Pack</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Masuk Stok Varian Remahan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">PO DALAM PROSES</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-maroon mt-2">{{ $poProsesCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">PO Active</span></h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2">Target Make-to-Order</p>
    </div>
</div>

<!-- Tabel Daftar Log Produksi -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">Riwayat Log Batch Produksi Dapur</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">NO. BATCH / WAKTU</th>
                <th class="p-4">ACUAN PO PELANGGAN</th>
                <th class="p-4">BAHAN BAKU DIPAKAI</th>
                <th class="p-4 text-center">QC GRADE A (UTAMA)</th>
                <th class="p-4 text-center">QC GRADE B (REMAHAN)</th>
                <th class="p-4">TGL EXPIRATION</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($logProduksi as $log)
            <tr class="hover:bg-gray-50/50">
                <td class="p-4">
                    <p class="font-black text-pc-dark">BATCH-00{{ $log->no_batch }}</p>
                    <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') }} • {{ $log->waktu_produksi }}</p>
                    
                    @if($log->ket)
                        <p class="text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200 rounded px-2 py-0.5 mt-1 inline-block">
                            📝 Catatan: {{ $log->ket }}
                        </p>
                    @endif
                </td>
                <td class="p-4">
                    <p class="font-bold text-pc-dark">{{ $log->poProduk->nama_pelanggan ?? '-' }}</p>
                    <span class="bg-amber-100 text-amber-800 text-[9px] font-extrabold px-2 py-0.5 rounded">
                        PO #{{ $log->no_po_produk }}
                    </span>
                </td>
                <td class="p-4 space-y-1">
                    @foreach($log->bahanProduksi as $bahan)
                        <div class="text-[11px]">
                            <span class="font-bold text-gray-700">• {{ $bahan->nama_bahan }}:</span> 
                            <span class="font-black text-pc-maroon">{{ $bahan->pivot->qty_dipakai }} {{ $bahan->ukuran }}</span>
                        </div>
                    @endforeach
                </td>
                <td class="p-4 text-center font-black text-emerald-600 text-sm">
                    +{{ number_format($log->jml_grade_A) }} Pcs
                </td>
                <td class="p-4 text-center font-black text-pc-orange text-sm">
                    +{{ number_format($log->jml_grade_B) }} Pack
                </td>
                <td class="p-4 font-bold text-gray-500">
                    {{ \Carbon\Carbon::parse($log->rentang_tgl_exp)->format('d M Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-gray-400">Belum ada log batch produksi yang dicatat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- MODAL POP-UP INPUT BATCH PRODUKSI -->
<div id="modal-log" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Input Batch Produksi & Output QC</h3>
            <button onclick="document.getElementById('modal-log').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('log-produksi.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Acuan PO Pelanggan (Make-to-Order)</label>
                <select name="no_po_produk" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    <option value="" disabled selected>-- Pilih PO Pelanggan --</option>
                    @foreach($poActive as $po)
                        <option value="{{ $po->no_po_produk }}">PO #{{ $po->no_po_produk }} - {{ $po->nama_pelanggan }} (Target: {{ $po->jumlah_po }} Pcs)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Catatan / Kendala Batch (Opsional)</label>
                <textarea name="ket" rows="2" placeholder="Misal: Kendala alat penggorengan / Adonan renyah" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange"></textarea>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tgl Produksi</label>
                    <input type="date" name="tgl_produksi" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Waktu</label>
                    <input type="time" name="waktu_produksi" value="08:00" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tgl Expired</label>
                    <input type="date" name="rentang_tgl_exp" value="{{ date('Y-m-d', strtotime('+6 months')) }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <!-- Pemakaian Bahan Baku Manual -->
            <div class="border border-gray-200 rounded-xl p-3 bg-gray-50/50 space-y-2">
                <label class="font-extrabold text-pc-dark block">Input Pemakaian Bahan Baku (Pemotongan Manual)</label>
                @foreach($rawMaterials as $mat)
                <div class="flex items-center justify-between gap-2 bg-white p-2 rounded-lg border border-gray-100">
                    <span class="font-bold text-gray-700 w-1/2">{{ $mat->nama_bahan }} ({{ $mat->ukuran }})</span>
                    <input type="hidden" name="bahan_ids[]" value="{{ $mat->id_bahan }}">
                    <input type="number" step="0.01" name="bahan_qty[]" value="0" placeholder="0" class="w-1/2 border border-gray-200 rounded-lg px-2 py-1 text-right focus:outline-none focus:border-pc-orange">
                </div>
                @endforeach
            </div>

            <!-- Hasil QC & Alokasi Produk Jadi -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <div class="p-3 bg-emerald-50/50 border border-emerald-200 rounded-xl space-y-2">
                    <label class="font-extrabold text-emerald-800 block">QC Grade A (Lolos Pouch)</label>
                    <div>
                        <label class="text-[10px] font-bold text-gray-600 block">Pilih Produk Varian</label>
                        <select name="id_produk_grade_A" required class="w-full border border-gray-200 rounded-lg px-2 py-1 bg-white">
                            @foreach($products as $p)
                                @if(!str_contains(strtolower($p->nama_produk), 'remahan') && !str_contains(strtolower($p->varian), 'remahan'))
                                    <option value="{{ $p->id_produk_jadi }}">{{ $p->nama_produk }} - {{ $p->varian }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-600 block">Jumlah Grade A (Pcs)</label>
                        <input type="number" name="jml_grade_A" required placeholder="250" class="w-full border border-gray-200 rounded-lg px-2 py-1 bg-white font-bold text-emerald-700">
                    </div>
                </div>

                <div class="p-3 bg-amber-50/50 border border-amber-200 rounded-xl space-y-2">
                    <label class="font-extrabold text-amber-800 block">QC Grade B (Remahan)</label>
                    <div>
                        <label class="text-[10px] font-bold text-gray-600 block">Pilih Produk Remahan</label>
                        <select name="id_produk_grade_B" class="w-full border border-gray-200 rounded-lg px-2 py-1 bg-white">
                            <option value="">-- Tanpa Remahan --</option>
                            @foreach($products as $p)
                                @if(str_contains(strtolower($p->nama_produk), 'remahan') || str_contains(strtolower($p->varian), 'remahan'))
                                    <option value="{{ $p->id_produk_jadi }}">{{ $p->nama_produk }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-600 block">Jumlah Grade B (Pack)</label>
                        <input type="number" name="jml_grade_B" value="0" placeholder="20" class="w-full border border-gray-200 rounded-lg px-2 py-1 bg-white font-bold text-amber-700">
                    </div>
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-log').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan Batch & Update Stok</button>
            </div>
        </form>
    </div>
</div>

@endsection