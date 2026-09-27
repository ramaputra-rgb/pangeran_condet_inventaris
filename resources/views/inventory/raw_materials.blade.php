@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Pengadaan & Stok Bahan Produksi</h2>
        <p class="text-xs text-gray-400 mt-0.5">Kendali rantai pasok terpadu. Transaksi masuk bahan baku/pembantu langsung menambah stok fisik gudang.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-purchase').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Input Transaksi Masuk</span>
        </button>

        <button onclick="document.getElementById('modal-material').classList.remove('hidden')" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="package-plus" class="w-4 h-4 text-pc-orange"></i>
            <span class="text-xs font-bold text-gray-700">+ Tambah Bahan</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">PENGELUARAN BULAN INI</p>
            <span class="p-1.5 bg-pc-cream text-pc-maroon rounded-lg"><i data-lucide="wallet" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">Rp {{ number_format($pengeluaranBulanIni ?? 0, 0, ',', '.') }}</h3>
        <p class="text-[10px] font-bold text-gray-400 mt-2">▲ Total transaksi pengadaan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">TOTAL BAHAN DITERIMA</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="package-check" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($totalBahanDiterima ?? 0) }} <span class="text-xs font-semibold text-gray-400">Unit/Kg</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">{{ $purchases->count() ?? 0 }} Transaksi Pembelian</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-maroon">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">STOK KRITIS (ALERT)</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="alert-octagon" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-maroon mt-2">{{ $stokKritisCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">Bahan Kritis</span></h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2 truncate">{{ $stokKritisItems ?: 'Stok aman tercukupi' }}</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">MASTER MATERIAL</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="boxes" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $rawMaterials->count() ?? 0 }} <span class="text-xs font-semibold text-gray-400">Jenis Bahan</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Kering & Basah Terdaftar</p>
    </div>
</div>

<!-- Tabel Stok Bahan Produksi -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">Katalog Stok Bahan Produksi</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">ID BAHAN</th>
                <th class="p-4">NAMA BAHAN</th>
                <th class="p-4">TIPE</th>
                <th class="p-4">SATUAN / UKURAN</th>
                <th class="p-4 font-black">QTY STOK</th>
                <th class="p-4 text-center">STATUS</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($rawMaterials as $mat)
            <tr class="hover:bg-gray-50/50">
                <td class="p-4 font-bold text-pc-dark">MAT-00{{ $mat->id_bahan }}</td>
                <td class="p-4 font-black text-pc-dark">{{ $mat->nama_bahan }}</td>
                <td class="p-4">
                    <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold {{ $mat->tipe === 'kering' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ strtoupper($mat->tipe) }}
                    </span>
                </td>
                <td class="p-4 font-semibold text-gray-500">{{ $mat->ukuran }}</td>
                <td class="p-4 font-black text-pc-maroon text-sm">{{ number_format($mat->qty_stok, 2) }} {{ $mat->ukuran }}</td>
                <td class="p-4 text-center">
                    @if($mat->qty_stok <= 50)
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-red-100 text-red-800 border border-red-200 animate-pulse">Kritis</span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Aman</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-gray-400">Belum ada data bahan produksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Pop-up Input Transaksi Pembelian -->
<div id="modal-purchase" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Input Transaksi Pembelian Bahan</h3>
            <button onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('raw-materials.purchase.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama Supplier / Toko</label>
                <input type="text" name="nama_supplier" required placeholder="Misal: Toko Bahan Pokok Condet" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Bahan Produksi</label>
                <select name="id_bahan" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    @foreach($rawMaterials as $mat)
                        <option value="{{ $mat->id_bahan }}">{{ $mat->nama_bahan }} (Ukuran: {{ $mat->ukuran }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Total Qty Masuk</label>
                    <input type="number" step="0.01" name="total_qty" required placeholder="100" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Total Harga (Rp)</label>
                    <input type="number" name="total_harga" required placeholder="1500000" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Total Ongkir (Rp, Opsional)</label>
                <input type="number" name="total_ongkir" value="0" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan & Update Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Tambah Bahan Produksi Baru -->
<div id="modal-material" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Tambah Master Bahan Produksi</h3>
            <button onclick="document.getElementById('modal-material').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('raw-materials.material.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama Bahan</label>
                <input type="text" name="nama_bahan" required placeholder="Misal: Garam Halus Industri" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tipe Bahan</label>
                    <select name="tipe" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                        <option value="kering">Kering</option>
                        <option value="basah">Basah</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Ukuran / Satuan</label>
                    <input type="text" name="ukuran" required placeholder="kg / liter / pcs" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Stok Awal</label>
                <input type="number" step="0.01" name="qty_stok" value="0" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-material').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan Master Bahan</button>
            </div>
        </form>
    </div>
</div>

@endsection