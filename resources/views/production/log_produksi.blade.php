@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Log Produksi & Quality Control Dapur</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pencatatan penggorengan per batch, pemotongan stok resep otomatis, dan pemisahan hasil QC (Grade A & Grade B).</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-log').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Input Batch Produksi Baru</span>
        </button>

        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span class="text-xs font-bold text-gray-700">Cetak Log Produksi</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">TOTAL BATCH DIPROSES</p>
            <span class="p-1.5 bg-pc-cream text-pc-maroon rounded-lg"><i data-lucide="flame" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $logs->count() }} <span class="text-xs font-semibold text-gray-400">Batch</span></h3>
        <p class="text-[10px] font-bold text-emerald-600 mt-2">✓ Rantai Pasok Terintegrasi</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-emerald-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">QC GRADE A (PUNCH UTAMA)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($logs->sum('jml_grade_A')) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Lolos standar mutu Condet</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-orange">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">QC GRADE B (REMAHAN)</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="alert-circle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($logs->sum('jml_grade_B')) }} <span class="text-xs font-semibold text-gray-400">Pack</span></h3>
        <p class="text-[10px] text-amber-600 font-bold mt-2">Kategori kemasan remahan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">PO MENUNGGU DAPUR</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $poActive->count() }} <span class="text-xs font-semibold text-gray-400">PO Antrean</span></h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2">⚡ Siap Dijadwalkan Masuk</p>
    </div>
</div>

<!-- Kartu Target & Progress Penggorengan Dapur -->
<div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm mt-6">
    <div class="flex justify-between items-center mb-3">
        <div>
            <h3 class="text-xs font-black text-pc-dark uppercase tracking-wider">🍳 Target Penggorengan PO Aktif Dapur</h3>
            <p class="text-[10px] text-gray-400">Monitoring progres goreng vs target pesanan pelanggan secara *real-time*.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs mt-2">
        @forelse($poActive as $po)
        @php
            $target = $po->jumlah_po;
            $gorengBaru = $po->total_digoreng ?? 0;
            
            // Ambil stok fisik gudang riil saat ini
            $stokGudang = $po->detailAlokasi->first()->produkJadi->stok ?? 0;
            
            // Cukup patok ketersediaan dari stok gudang riil saat ini
            $totalTersedia = $stokGudang; 
            $persen = min(100, round(($totalTersedia / max(1, $target)) * 100));
            $isSelesai = $totalTersedia >= $target;
        @endphp

        <div class="p-3.5 border rounded-2xl bg-gray-50/60 flex flex-col justify-between space-y-2">
            <div class="flex justify-between items-start">
                <div>
                    <span class="font-black text-pc-dark text-xs block">PO-00{{ $po->no_po_produk }}</span>
                    <span class="text-[10px] text-gray-500 font-medium">{{ $po->nama_pelanggan }}</span>
                </div>
                @if($isSelesai)
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[9px] font-black rounded-full border border-emerald-200">
                        ✅ Ready Siap Antar (Cukup Stok)
                    </span>
                @else
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] font-black rounded-full border border-amber-200">
                        🍳 Kurang {{ $target - $totalTersedia }} Pcs
                    </span>
                @endif
            </div>

            <div>
                <div class="flex justify-between text-[10px] font-bold text-gray-500 mb-1">
                    <span>Total Goreng Batch: {{ $gorengBaru }} | Total Stok Fisik: {{ $stokGudang }}</span>
                    <span class="text-pc-dark font-black">{{ $totalTersedia }} / {{ $target }} Pcs</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500 {{ $isSelesai ? 'bg-emerald-500' : 'bg-pc-orange' }}" style="width: {{ $persen }}%"></div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full p-4 text-center bg-gray-50 rounded-xl text-gray-400 text-xs italic">
            Tidak ada antrean PO aktif.
        </div>
    @endforelse
    </div>
</div>

<!-- Tabel Riwayat Batch Produksi -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">Riwayat Log Penggorengan & Hasil Quality Control</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">NO. BATCH / WAKTU</th>
                <th class="p-4">ACUAN PO PELANGGAN</th>
                <th class="p-4">BAHAN DUGAN / REVISI BAKU</th>
                <th class="p-4 text-center">HASIL QC GRADE A</th>
                <th class="p-4 text-center">HASIL QC GRADE B</th>
                <th class="p-4">TGL KEDALUWARSA</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($logs as $log)
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
                    <p class="font-black text-pc-dark">PO-00{{ $log->no_po_produk }}</p>
                    <p class="text-[10px] text-gray-400">{{ $log->poProduk->nama_pelanggan ?? 'Pelanggan' }}</p>
                </td>
                <td class="p-4">
                    @forelse($log->bahanProduksi as $bahan)
                        <p class="text-[11px] text-gray-600">• {{ $bahan->nama_bahan }}: <span class="font-bold text-pc-dark">{{ $bahan->pivot->qty_dipakai }} {{ $bahan->satuan_ukuran }}</span></p>
                    @empty
                        <p class="text-gray-400 italic text-[10px]">Resep tidak terinci</p>
                    @endforelse
                </td>
                <td class="p-4 text-center font-black text-emerald-600 text-sm">
                    +{{ number_format($log->jml_grade_A) }} Pcs
                </td>
                <td class="p-4 text-center font-black text-pc-orange text-sm">
                    +{{ number_format($log->jml_grade_B) }} Pack
                </td>
                <td class="p-4 font-bold text-red-600">
                    {{ \Carbon\Carbon::parse($log->rentang_tgl_exp)->format('d M Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-gray-400">Belum ada riwayat batch produksi yang tercatat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Pop-up Input Batch Produksi Baru -->
<div id="modal-log" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Input Log Penggorengan & QC Dapur</h3>
            <button onclick="document.getElementById('modal-log').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('log-produksi.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih PO Pelanggan Acuan</label>
                <select name="no_po_produk" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold text-pc-dark">
                    @foreach($poActive as $po)
                        <option value="{{ $po->no_po_produk }}">PO-00{{ $po->no_po_produk }} - {{ $po->nama_pelanggan }} (Total PO: {{ $po->jumlah_po }} Pcs)</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tgl Produksi</label>
                    <input type="date" name="tgl_produksi" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tgl Kedaluwarsa</label>
                    <input type="date" name="rentang_tgl_exp" value="{{ date('Y-m-d', strtotime('+6 months')) }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Waktu / Shift</label>
                    <select name="waktu_produksi" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                        <option value="08:00:00">Shift Pagi (08:00)</option>
                        <option value="13:00:00">Shift Siang (13:00)</option>
                        <option value="19:00:00">Lembur Malam (19:00)</option>
                    </select>
                </div>
            </div>

            <!-- Pemisahan Produk Hasil QC Grade A & Grade B -->
            <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-2">
                <span class="font-black text-emerald-800 text-[11px] block">✓ Hasil Quality Control (QC)</span>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Target Produk Grade A</label>
                        <select name="id_produk_grade_A" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id_produk_jadi }}">{{ $prod->nama_produk }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="jml_grade_A" placeholder="Jumlah Pcs Grade A" required class="w-full border border-gray-200 rounded-xl px-3 py-2 mt-1 focus:outline-none focus:border-pc-orange">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Produk Grade B (Remahan)</label>
                        <select name="id_produk_grade_B" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                            <option value="">-- Tanpa Grade B --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id_produk_jadi }}">{{ $prod->nama_produk }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="jml_grade_B" value="0" placeholder="Jumlah Pack Grade B" class="w-full border border-gray-200 rounded-xl px-3 py-2 mt-1 focus:outline-none focus:border-pc-orange">
                    </div>
                </div>
            </div>

            <!-- Pemotongan Bahan Baku Resep Dapur -->
            <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100 space-y-2">
                <span class="font-black text-amber-800 text-[11px] block">📦 Pemotongan Bahan Baku Resep</span>
                <div class="space-y-2">
                    @foreach($bahanList as $idx => $bhn)
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="bahan_ids[]" value="{{ $bhn->id_bahan }}">
                        <span class="w-1/2 font-bold text-gray-700 truncate">{{ $bhn->nama_bahan }} (Stok: {{ $bhn->qty_stok }} {{ $bhn->satuan_ukuran }})</span>
                        <input type="number" name="bahan_qty[]" value="0" step="0.1" min="0" placeholder="0" class="w-1/2 border border-gray-200 rounded-xl px-3 py-1.5 focus:outline-none focus:border-pc-orange text-right font-bold">
                        <span class="text-gray-400 font-bold text-[10px] w-12">{{ $bhn->satuan_ukuran }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Catatan / Kendala Batch (Opsional)</label>
                <textarea name="ket" rows="2" placeholder="Misal: Kendala alat penggorengan / Adonan renyah" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange"></textarea>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-log').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan Log & Update Stok</button>
            </div>
        </form>
    </div>
</div>

@endsection