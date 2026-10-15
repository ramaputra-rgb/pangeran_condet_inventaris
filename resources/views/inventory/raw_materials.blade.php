@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Pengadaan & Stok Bahan Produksi</h2>
        <p class="text-xs text-gray-400 mt-0.5">Kelola stok bahan baku, catat nota pembelian multi-item dari supplier, dan rekapitulasi pengadaan.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-purchase').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Catat Nota Pembelian Bahan</span>
        </button>

        <button onclick="document.getElementById('modal-material').classList.remove('hidden')" 
                class="bg-white border border-gray-200 text-pc-dark font-extrabold text-xs px-4 py-3 rounded-2xl hover:bg-gray-50 transition cursor-pointer">
            + Master Bahan Baru
        </button>
    </div>
</div>

<!-- 3 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 no-print">
    <!-- Kartu 1: Total Master Bahan -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">TOTAL MASTER BAHAN</p>
            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                <i data-lucide="package" class="w-4 h-4"></i>
            </span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">
            {{ number_format($totalBahanCount ?? 0) }} <span class="text-xs font-semibold text-gray-400">Jenis</span>
        </h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Terdaftar di Sistem</p>
    </div>

    <!-- Kartu 2: Stok Kritis / Restock -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm {{ ($stokKritisCount ?? 0) > 0 ? 'border-l-4 border-l-red-500' : '' }}">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-red-600 uppercase tracking-wider">STOK KRITIS / RESTOCK</p>
            <span class="p-1.5 bg-red-50 text-red-600 rounded-lg">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">
            {{ number_format($stokKritisCount ?? 0) }} <span class="text-xs font-semibold text-gray-400">Bahan</span>
        </h3>
        @if(($stokKritisCount ?? 0) > 0)
            <p class="text-[10px] text-red-500 font-extrabold mt-2 animate-pulse">⚠️ Butuh Pembelian Ulang</p>
        @else
            <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Semua Stok Aman</p>
        @endif
    </div>

    <!-- Kartu 3: Total Pengadaan Bulan Ini -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">BELANJA BAHAN (BULAN INI)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                <i data-lucide="wallet" class="w-4 h-4"></i>
            </span>
        </div>
        <h3 class="text-2xl font-black text-pc-maroon mt-2">
            Rp {{ number_format($totalPengadaanBulanIni ?? 0, 0, ',', '.') }}
        </h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Akumulasi Pembelian Supplier</p>
    </div>
</div>

<!-- Tab Navigation -->
<div class="flex border-b border-gray-200 mt-6 no-print">
    <button onclick="switchTab('stok')" id="tab-btn-stok" class="py-2.5 px-5 font-black text-xs border-b-2 border-pc-maroon text-pc-maroon cursor-pointer">
        📦 Stok Bahan Baku
    </button>
    <button onclick="switchTab('riwayat')" id="tab-btn-riwayat" class="py-2.5 px-5 font-bold text-xs text-gray-400 border-b-2 border-transparent hover:text-pc-dark cursor-pointer">
        📜 Riwayat Transaksi Pembelian
    </button>
</div>

<!-- TAB 1: STOK BAHAN BAKU -->
<div id="tab-content-stok" class="mt-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                    <th class="p-4">ID / NAMA BAHAN</th>
                    <th class="p-4">TIPE BAHAN</th>
                    <th class="p-4">SATUAN UKURAN</th>
                    <th class="p-4 font-black">STOK AKTUAL</th>
                    <th class="p-4 text-center">STATUS KECUKUPAN</th>
                    <th class="p-4 text-center no-print">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium">
                @forelse($materials as $mat)
                <tr class="hover:bg-gray-50/50">
                    <td class="p-4">
                        <p class="font-black text-pc-dark">{{ $mat->nama_bahan }}</p>
                        <p class="text-[10px] text-gray-400">ID: BAHAN-00{{ $mat->id_bahan }}</p>
                    </td>
                    <td class="p-4 uppercase text-gray-600 font-bold">{{ $mat->tipe }}</td>
                    <td class="p-4 font-semibold text-gray-500">{{ $mat->ukuran }}</td>
                    <td class="p-4 font-black text-emerald-600 text-sm">
                        {{ number_format($mat->qty_stok) }} {{$mat->ukuran }}
                    </td>
                    <td class="p-4 text-center">
                        @if($mat->qty_stok <= 5)
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-red-100 text-red-700 border border-red-200">
                                ⚠️ Stok Kritis
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                ✓ Stok Aman
                            </span>
                        @endif
                    </td>
                    <td class="p-4 text-center no-print">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="editMaterial('{{ $mat->id_bahan }}', '{{ $mat->nama_bahan }}', '{{$mat->tipe }}', '{{ $mat->ukuran }}', '{{$mat->qty_stok }}')" 
                                    title="Edit Master Bahan" class="p-2 bg-amber-50 text-amber-700 rounded-xl border border-amber-200 hover:bg-amber-100 cursor-pointer">
                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                            </button>

                            <form action="{{ route('raw-materials.material.destroy', $mat->id_bahan) }}" method="POST" onsubmit="return confirm('Hapus master bahan baku ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus Master Bahan" class="p-2 bg-red-50 text-red-600 rounded-xl border border-red-200 hover:bg-red-100 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-400">Belum ada master bahan baku.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- TAB 2: RIWAYAT TRANSAKSI PEMBELIAN -->
<div id="tab-content-riwayat" class="mt-4 hidden">
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-4 flex flex-col md:flex-row justify-between items-center gap-3 no-print">
        <form action="{{ route('raw-materials.index') }}" method="GET" class="flex items-center gap-2 text-xs">
            <span class="font-bold text-gray-600">Filter Periode:</span>
            <select name="periode" class="border border-gray-200 rounded-xl px-3 py-1.5 focus:outline-none focus:border-pc-orange bg-white font-bold">
                <option value="">Semua Riwayat</option>
                <option value="minggu_ini" {{ request('periode') == 'minggu_ini' ? 'selected' : '' }}>Minggu Ini</option>
                <option value="bulan_ini" {{ request('periode') == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
            </select>
            <button type="submit" class="px-3 py-1.5 bg-pc-cream text-pc-maroon font-bold rounded-xl border border-pc-orange/20">Filter</button>
        </form>

        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs flex items-center gap-2 cursor-pointer">
            <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan Transaksi Beli
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                    <th class="p-4">SUPPLIER & TANGGAL</th>
                    <th class="p-4 text-center">JUMLAH ITEM</th>
                    <th class="p-4 font-black">TOTAL BELANJA</th>
                    <th class="p-4 text-center no-print">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium">
                @if(count($groupedTransactions) > 0)
                    @foreach($groupedTransactions as $groupKey =>$items)
                        @php
                            $firstItem =$items->first();
                            $totalHargaGroup =$items->sum('total_harga');
                            $totalQtyGroup =$items->sum('total_qty');
                            $txIds =$items->pluck('no_transaksi_beli')->toArray();
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4">
                                <p class="font-black text-pc-dark">{{ $firstItem->nama_supplier }}</p>
                                <p class="text-[10px] text-gray-400 font-normal">{{ \Carbon\Carbon::parse($firstItem->tanggal)->format('d M Y H:i') }}</p>
                            </td>
                            <td class="p-4 text-center font-bold text-gray-600">
                                {{ $items->count() }} Jenis Bahan ({{ number_format($totalQtyGroup) }} Qty)
                            </td>
                            <td class="p-4 font-black text-pc-maroon text-sm">
                                Rp {{ number_format($totalHargaGroup, 0, ',', '.') }}
                            </td>
                            <td class="p-4 text-center no-print">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="showDetailGroup('{{ $firstItem->nama_supplier }}', '{{ \Carbon\Carbon::parse($firstItem->tanggal)->format('d M Y H:i') }}', '{{ json_encode($items) }}')" 
                                            class="p-2 bg-pc-cream text-pc-maroon rounded-xl border border-pc-orange/20 hover:bg-amber-100 cursor-pointer inline-flex items-center gap-1 font-bold text-[10px]">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                                    </button>

                                    <form action="{{ route('raw-materials.purchase.destroy-group') }}" method="POST" onsubmit="return confirm('Hapus seluruh nota pembelian ini? Stok bahan akan disesuaikan.')" class="inline">
                                        @csrf
                                        @foreach($txIds as $id)
                                            <input type="hidden" name="ids[]" value="{{ $id }}">
                                        @endforeach
                                        <button type="submit" title="Hapus Transaksi Nota Ini" class="p-2 bg-red-50 text-red-600 rounded-xl border border-red-200 hover:bg-red-100 cursor-pointer inline-flex items-center gap-1 font-bold text-[10px]">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-400">Belum ada riwayat transaksi pembelian.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up Multi-Item Pembelian Bahan -->
<div id="modal-purchase" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Catat Nota Pembelian Bahan Baru (Multi-Item)</h3>
            <button onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('raw-materials.purchase.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Nama Supplier / Toko</label>
                    <input type="text" name="nama_supplier" required placeholder="Misal: Agen Condet Utama" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Total Ongkos Kirim (Rp)</label>
                    <input type="number" name="total_ongkir" value="0" min="0" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div class="border border-gray-100 rounded-2xl p-3 bg-gray-50/50 space-y-2">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-black text-pc-dark text-xs">Daftar Bahan Yang Dibeli:</span>
                    <button type="button" onclick="addRow()" class="px-2.5 py-1 bg-pc-maroon text-white text-[10px] font-bold rounded-lg cursor-pointer">+ Tambah Baris Bahan</button>
                </div>

                <div id="row-container" class="space-y-2">
                    <div class="flex items-center gap-2 row-item">
                        <select name="bahan_ids[]" required class="w-2/5 border border-gray-200 rounded-xl px-2 py-1.5 bg-white font-bold text-gray-700">
                            @foreach($materials as $m)
                                <option value="{{ $m->id_bahan }}">{{ $m->nama_bahan }} ({{$m->ukuran }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="qty[]" step="0.1" required placeholder="Qty" class="w-1/4 border border-gray-200 rounded-xl px-2 py-1.5">
                        <input type="number" name="harga_total[]" required placeholder="Subtotal Harga (Rp)" class="w-1/3 border border-gray-200 rounded-xl px-2 py-1.5">
                        <button type="button" onclick="removeRow(this)" class="text-red-500 font-bold px-1">✕</button>
                    </div>
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan Nota Pembelian</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Pembelian -->
<div id="modal-detail" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm" id="detail-supplier-title">Detail Transaksi Pembelian</h3>
            <button onclick="document.getElementById('modal-detail').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-bold text-[10px] uppercase">
                    <th class="py-2">NAMA BAHAN</th>
                    <th class="py-2 text-center">QTY</th>
                    <th class="py-2 text-right">TOTAL HARGA</th>
                </tr>
            </thead>
            <tbody id="detail-tbody" class="divide-y divide-gray-100 font-medium">
                <!-- Injected via Javascript -->
            </tbody>
        </table>

        <div class="pt-3 flex justify-end">
            <button type="button" onclick="document.getElementById('modal-detail').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Pop-up Master Bahan Baru -->
<div id="modal-material" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Tambah Master Bahan Baku Baru</h3>
            <button onclick="document.getElementById('modal-material').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('raw-materials.material.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama Bahan Baku</label>
                <input type="text" name="nama_bahan" required placeholder="Misal: Tepung Tapioka Super" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tipe Bahan</label>
                    <select name="tipe" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold">
                        <option value="kering">Kering</option>
                        <option value="basah">Basah</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Satuan Ukuran</label>
                    <input type="text" name="ukuran" required placeholder="kg / Liter / pack" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Stok Awal</label>
                <input type="number" name="qty_stok" value="0" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-material').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan Master Bahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Master Bahan Baku -->
<div id="modal-edit-material" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Edit Master Bahan Baku</h3>
            <button onclick="document.getElementById('modal-edit-material').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form id="form-edit-material" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama Bahan Baku</label>
                <input type="text" id="edit-nama-bahan" name="nama_bahan" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tipe Bahan</label>
                    <select id="edit-tipe" name="tipe" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold">
                        <option value="kering">Kering</option>
                        <option value="basah">Basah</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Satuan Ukuran</label>
                    <input type="text" id="edit-ukuran" name="ukuran" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Stok Aktual</label>
                <input type="number" id="edit-qty-stok" name="qty_stok" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-edit-material').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Perbarui Master Bahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    if (tab === 'stok') {
        document.getElementById('tab-content-stok').classList.remove('hidden');
        document.getElementById('tab-content-riwayat').classList.add('hidden');
        document.getElementById('tab-btn-stok').classList.add('border-pc-maroon', 'text-pc-maroon');
        document.getElementById('tab-btn-riwayat').classList.remove('border-pc-maroon', 'text-pc-maroon');
    } else {
        document.getElementById('tab-content-stok').classList.add('hidden');
        document.getElementById('tab-content-riwayat').classList.remove('hidden');
        document.getElementById('tab-btn-riwayat').classList.add('border-pc-maroon', 'text-pc-maroon');
        document.getElementById('tab-btn-stok').classList.remove('border-pc-maroon', 'text-pc-maroon');
    }
}

function addRow() {
    const container = document.getElementById('row-container');
    const firstRow = container.children[0];
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('input[name="qty[]"]').value = '';
    newRow.querySelector('input[name="harga_total[]"]').value = '';
    container.appendChild(newRow);
}

function removeRow(btn) {
    const container = document.getElementById('row-container');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    }
}

function showDetailGroup(supplier, tgl, itemsJson) {
    document.getElementById('detail-supplier-title').innerText = 'Nota ' + supplier + ' (' + tgl + ')';
    const items = JSON.parse(itemsJson);
    const tbody = document.getElementById('detail-tbody');
    tbody.innerHTML = '';

    items.forEach(i => {
        const namaBahan = i.bahan_produksi ? i.bahan_produksi.nama_bahan : 'Bahan';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-2 font-bold text-pc-dark">${namaBahan}</td>
            <td class="py-2 text-center font-bold">${i.total_qty}</td>
            <td class="py-2 text-right font-black text-pc-maroon">Rp ${new Intl.NumberFormat('id-ID').format(i.total_harga)}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('modal-detail').classList.remove('hidden');
}

function editMaterial(id, nama, tipe, ukuran, stok) {
    document.getElementById('form-edit-material').action = '/raw-materials/material/' + id;
    document.getElementById('edit-nama-bahan').value = nama;
    document.getElementById('edit-tipe').value = tipe;
    document.getElementById('edit-ukuran').value = ukuran;
    document.getElementById('edit-qty-stok').value = stok;
    document.getElementById('modal-edit-material').classList.remove('hidden');
}
</script>

@endsection