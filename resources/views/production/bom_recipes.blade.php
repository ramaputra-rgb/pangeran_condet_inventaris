@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Master Formula & Standar BoM</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pengaturan standar resep produksi (Bill of Materials) & overhead biaya dapur Condet untuk kalkulasi HPP dan pemotongan stok otomatis.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <!-- Tombol Utama (+ Buat / Edit Formula BoM) -->
        <button onclick="document.getElementById('modal-bom').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Buat / Edit Formula BoM</span>
        </button>

        <!-- Tombol Cetak Resep Master -->
        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan BoM -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">FORMULA MASTER TERDAFTAR</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="scroll-text" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $totalFormula ?? 0 }} <span class="text-xs font-semibold text-gray-400">Formula</span></h3>
        <p class="text-[10px] font-bold text-emerald-600 mt-2">✓ Terverifikasi Dapur Condet</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">STANDARD BATCH PENGGORENGAN</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="box" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">100 <span class="text-xs font-semibold text-gray-400">Pcs / Batch</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Acuan Standar HPP & Bahan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">BAHAN BAKU INTEGRATED</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="database" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $totalBahanBaku ?? 0 }} <span class="text-xs font-semibold text-gray-400">Material</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Terhubung ke Modul Gudang</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-maroon">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">OVERHEAD COMPONENT</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="receipt" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-maroon mt-2">Gas & Tenaga</h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2">Termasuk dalam Hitungan HPP</p>
    </div>
</div>

<!-- Katalog Master Formula BoM per Produk -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3 bg-gray-50/50">
        <div class="flex items-center space-x-4 text-xs font-bold text-pc-dark">
            <span class="border-b-2 border-pc-maroon text-pc-maroon pb-1">Katalog Formula Resep BoM produk <span class="bg-red-100 text-pc-maroon text-[9px] px-2 py-0.5 rounded-full ml-1">{{ $products->count() ?? 0 }} Produk</span></span>
        </div>
    </div>

    <div class="p-5 space-y-6">
        @forelse($products as $produk)
        <div class="border border-gray-200/80 rounded-2xl p-5 bg-white shadow-sm space-y-4">
            <!-- Product Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                <div>
                    <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full">Kode: {{ $produk->kode_produk }}</span>
                    <h3 class="text-base font-black text-pc-dark mt-1">{{ $produk->nama_produk }}</h3>
                    <p class="text-xs text-gray-400">{{ $produk->formulaBom->nama_formula ?? 'Belum ada nama formula acuan' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-extrabold text-gray-400 uppercase">STANDAR BATCH</p>
                    <p class="text-sm font-black text-pc-maroon">{{ $produk->formulaBom->porsi_batch_pcs ?? 100 }} Pcs / Batch</p>
                </div>
            </div>

            <!-- Tabel Rincian Material & Overhead -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-gray-400 font-extrabold text-[10px] uppercase border-b border-gray-100 bg-gray-50/50">
                            <th class="p-2.5">JENIS KOMPONEN</th>
                            <th class="p-2.5">NAMA KOMPONEN / MATERIAL</th>
                            <th class="p-2.5 text-center">JUMLAH DIBUTUHKAN PER BATCH</th>
                            <th class="p-2.5 text-center">SATUAN ACUAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @if($produk->formulaBom && $produk->formulaBom->detailFormula->count() > 0)
                            @foreach($produk->formulaBom->detailFormula as $detail)
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-2.5 font-bold">
                                    @if($detail->jenis_komponen === 'BAHAN_BAKU')
                                        <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-extrabold text-[10px]">Bahan Baku</span>
                                    @elseif($detail->jenis_komponen === 'BIAYA_GAS')
                                        <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-extrabold text-[10px]">Overhead Gas</span>
                                    @else
                                        <span class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded font-extrabold text-[10px]">Tenaga Kerja</span>
                                    @endif
                                </td>
                                <td class="p-2.5 font-bold text-pc-dark">
                                    {{ $detail->bahanBaku->nama_bahan ?? $detail->bahanBaku->nama_bahan_baku ?? $detail->jenis_komponen }}
                                </td>
                                <td class="p-2.5 text-center font-black text-pc-dark">
                                    @if($detail->satuan_dibutuhkan === 'Rupiah')
                                        Rp {{ number_format($detail->jumlah_dibutuhkan, 0, ',', '.') }}
                                    @else
                                        {{ number_format($detail->jumlah_dibutuhkan, 2) }}
                                    @endif
                                </td>
                                <td class="p-2.5 text-center text-gray-500 font-semibold">
                                    {{ $detail->satuan_dibutuhkan }}
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-400 italic">Belum ada rincian formula BoM yang dikonfigurasi untuk produk ini.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-gray-400">Belum ada data produk aktif di sistem.</div>
        @endforelse
    </div>
</div>

<!-- MODAL POP-UP KONFIGURASI FORMULA BOM -->
<div id="modal-bom" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Konfigurasi Header Formula BoM</h3>
            <button onclick="document.getElementById('modal-bom').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('bom-recipes.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Produk Jadi</label>
                <select name="id_produk" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    @foreach($products as $prod)
                        <option value="{{ $prod->id_produk ?? $prod->id }}">{{ $prod->nama_produk }} ({{ $prod->kode_produk }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama / Judul Formula Resep</label>
                <input type="text" name="nama_formula" required placeholder="Misal: Resep Standard Penggorengan 100 Pcs 250g" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Porsi Batch Acuan (Pcs)</label>
                <input type="number" name="porsi_batch_pcs" value="100" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Keterangan Catatan Dapur</label>
                <textarea name="keterangan" rows="2" placeholder="Formula acuan HPP penggorengan dapur Condet..." class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange"></textarea>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-bom').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">
                    Simpan Formula Master
                </button>
            </div>
        </form>
    </div>
</div>

@endsection