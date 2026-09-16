@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Pengadaan & Manajemen Bahan Baku</h2>
        <p class="text-xs text-gray-400 mt-0.5">Kendali rantai pasok terpadu. Transaksi pembelian bahan baku langsung diinput saat barang diterima dan otomatis menambah stok fisik gudang.</p>
    </div>
    <div class="flex items-center space-x-2">
        <!-- PERUBAHAN 1: TOMBOL MEMBUKA MODAL POP-UP -->
        <button onclick="document.getElementById('modal-purchase').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm hover:bg-red-800 transition flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> + Input Transaksi Masuk
        </button>
        <button class="px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 flex items-center gap-1.5">
            <i data-lucide="download" class="w-4 h-4"></i> Export CSV
        </button>
        <button class="px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 flex items-center gap-1.5">
            <i data-lucide="printer" class="w-4 h-4"></i> Cetak Buku Pembelian
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Pengadaan & MSS Alert -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">PENGELUARAN BULAN INI</p>
            <span class="p-1.5 bg-pc-cream text-pc-maroon rounded-lg"><i data-lucide="wallet" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">Rp 48.650.000</h3>
        <p class="text-[10px] font-bold text-emerald-600 mt-2">▲ +12.4% efisiensi dari bulan lalu</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">TOTAL BAHAN DITERIMA BULAN INI</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="package-check" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">1.450 <span class="text-xs font-semibold text-gray-400">Kg / Ltr</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">4 Transaksi Langsung Masuk Stok</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-maroon">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">STOK KRITIS (MSS ALERT)</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="alert-octagon" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-maroon mt-2">2 <span class="text-xs font-semibold text-gray-400">Bahan Kritis</span></h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2">Ikan Tenggiri Segar & Minyak Sawit</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">SUPPLIER REKANAN</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="store" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $suppliers->count() }} <span class="text-xs font-semibold text-gray-400">Rekanan Utama</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ 98.2% On-Time Delivery Rate</p>
    </div>
</div>

<!-- Tabel Transaksi Pembelian Bahan Baku -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3 bg-gray-50/50">
        <div class="flex items-center space-x-4 text-xs font-bold text-pc-dark">
            <span class="border-b-2 border-pc-maroon text-pc-maroon pb-1">Transaksi Pembelian Bahan <span class="bg-red-100 text-pc-maroon text-[9px] px-2 py-0.5 rounded-full ml-1">4 Aktif</span></span>
            <span class="text-gray-400 cursor-pointer hover:text-pc-dark">Katalog & Master Bahan Baku <span class="bg-gray-100 text-gray-600 text-[9px] px-2 py-0.5 rounded-full ml-1">{{ $rawMaterials->count() }} Item</span></span>
            <span class="text-gray-400 cursor-pointer hover:text-pc-dark">Daftar Supplier Rekanan <span class="bg-gray-100 text-gray-600 text-[9px] px-2 py-0.5 rounded-full ml-1">{{ $suppliers->count() }} Rekanan</span></span>
        </div>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">NO. TRANSAKSI (PO)</th>
                <th class="p-4">SUPPLIER (ERD: ID_SUPPLIER)</th>
                <th class="p-4">RINCIAN BAHAN & QTY MASUK</th>
                <th class="p-4">HARGA SATUAN</th>
                <th class="p-4">TOTAL TRANSAKSI</th>
                <th class="p-4 text-center">BUKTI MASUK & AKSI ERD</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($purchases as $purchase)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="p-4">
                    <p class="font-bold text-pc-dark">{{ $purchase->po_number }}</p>
                    <p class="text-[10px] text-gray-400">Tgl: {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</p>
                </td>
                <td class="p-4">
                    <p class="font-bold text-pc-dark">{{ $purchase->supplier->name ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">{{ $purchase->supplier->supplier_code ?? 'SUP-001' }}</p>
                </td>
                <td class="p-4">
                    <p class="font-bold text-pc-dark">{{ $purchase->rawMaterial->name ?? '-' }}</p>
                    <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200">
                        +{{ $purchase->qty_received }} {{ $purchase->rawMaterial->unit ?? 'Kg' }} Masuk Stok
                    </span>
                </td>
                <td class="p-4 font-bold">Rp {{ number_format($purchase->unit_price, 0, ',', '.') }} / {{ $purchase->rawMaterial->unit ?? 'Kg' }}</td>
                <td class="p-4 font-extrabold text-pc-maroon">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                <td class="p-4 text-center">
                    <button class="px-3 py-1.5 bg-pc-cream text-pc-maroon border border-pc-orange/20 rounded-lg text-[10px] font-bold hover:bg-amber-100 flex items-center gap-1 mx-auto">
                        <i data-lucide="file-text" class="w-3 h-3"></i> Cetak Bukti Masuk
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-gray-400">Belum ada transaksi pembelian bahan baku.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Bottom Section: Katalog MSS vs Master Supplier -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Katalog Stok Aktual vs Stok Aman (MSS) -->
    <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h4 class="text-xs font-extrabold text-pc-dark uppercase tracking-wider flex items-center gap-2">
                📋 Katalog Stok Aktual vs Stok Aman (MSS)
            </h4>
            <span class="text-pc-orange text-[10px] font-bold cursor-pointer hover:underline">+ Tambah Bahan</span>
        </div>

        <!-- Banner MSS Alert Automasi -->
        <div class="p-3 bg-red-50/70 border border-red-200 rounded-xl text-[11px] text-red-900 flex items-start space-x-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-pc-maroon shrink-0 mt-0.5"></i>
            <p><strong>Peringatan Restock Otomatis:</strong> Stok Ikan Tenggiri Giling (120 Kg) berada di bawah ambang batas aman (200 Kg). Mohon segera setujui PO masuk untuk mencegah terhentinya SPK Rengginang Shift Sore.</p>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="text-gray-400 font-extrabold text-[10px] uppercase border-b border-gray-100">
                    <th class="pb-2">ID & NAMA BAHAN</th>
                    <th class="pb-2">STOK AKTUAL</th>
                    <th class="pb-2">BATAS AMAN (MSS)</th>
                    <th class="pb-2">STATUS</th>
                    <th class="pb-2 text-center">ALOKASI SPK</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium">
                @foreach($rawMaterials as $material)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3">
                        <p class="font-bold text-pc-dark">{{ $material->name }}</p>
                        <p class="text-[10px] text-gray-400">{{ $material->code }} • Satuan: {{ $material->unit }}</p>
                    </td>
                    <td class="py-3 font-extrabold text-pc-maroon">{{ $material->stock_qty }} {{ $material->unit }}</td>
                    <td class="py-3 text-gray-500 font-bold">{{ $material->mss_limit }} {{ $material->unit }}</td>
                    <td class="py-3">
                        @if($material->status == 'STOK_KRITIS')
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-red-100 text-red-800 border border-red-200 animate-pulse">
                                Stok Kritis
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Stok Aman
                            </span>
                        @endif
                    </td>
                    <td class="py-3 text-center">
                        @if($material->status == 'STOK_KRITIS')
                            <button onclick="document.getElementById('modal-purchase').classList.remove('hidden')" class="bg-pc-maroon text-white font-bold text-[10px] px-3 py-1 rounded-lg hover:bg-red-800">
                                Restock Cepat
                            </button>
                        @else
                            <span class="text-gray-400 text-[10px]">Tersedia 4 SPK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Master Supplier Rekanan -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h4 class="text-xs font-extrabold text-pc-dark uppercase tracking-wider">Master Supplier Rekanan</h4>
            <span class="text-pc-orange text-[10px] font-bold cursor-pointer hover:underline">+ Baru</span>
        </div>
        
        <div class="space-y-3">
            @foreach($suppliers as $supplier)
            <div class="p-3 bg-gray-50/70 rounded-xl border border-gray-200/80 space-y-1 text-xs">
                <div class="flex justify-between items-center">
                    <p class="font-bold text-pc-dark">{{ $supplier->name }}</p>
                    <span class="bg-emerald-100 text-emerald-800 text-[9px] font-extrabold px-2 py-0.5 rounded">Rating {{ $supplier->rating }}%</span>
                </div>
                <p class="text-[10px] text-gray-500">📞 {{ $supplier->phone }}</p>
                <p class="text-[10px] text-gray-400 font-semibold">Term: {{ $supplier->payment_terms }} Hari (Kredit)</p>
            </div>
            @endforeach
        </div>
    </div>

</div>

<!-- PERUBAHAN 2: MODAL POP-UP FORM INPUT TRANSAKSI -->
<div id="modal-purchase" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Input Transaksi Masuk Bahan Baku</h3>
            <button onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('raw-materials.purchase.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Supplier Rekanan</label>
                <select name="supplier_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Bahan Baku</label>
                <select name="raw_material_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    @foreach($rawMaterials as $material)
                        <option value="{{ $material->id }}">{{ $material->name }} (Satuan: {{ $material->unit }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Jumlah (Qty Masuk)</label>
                    <input type="number" step="0.01" name="qty_received" required placeholder="Misal: 100" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Harga Satuan (Rp)</label>
                    <input type="number" name="unit_price" required placeholder="Misal: 65000" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm">
                    Simpan & Update Stok
                </button>
            </div>
        </form>
    </div>
</div>

@endsection