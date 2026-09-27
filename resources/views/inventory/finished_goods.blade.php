@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Stok Produk Jadi (Finished Goods)</h2>
        <p class="text-xs text-gray-400 mt-0.5">Monitoring ketersediaan varian Rengginang & Emping siap jual. Otomatis ter-update dari hasil QC Dapur dan teralokasi untuk PO Pelanggan.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-product').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Tambah Varian Produk</span>
        </button>

        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span class="text-xs font-bold text-gray-700">Cetak Laporan Stok</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">TOTAL STOK FISIK FG</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="boxes" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($totalStokFisik ?? 0) }} <span class="text-xs font-semibold text-gray-400">Pcs/Pack</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Produk Siap Edar Dapur Condet</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">ALOKASI TERKUNCI PO</p>
            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg"><i data-lucide="lock" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-amber-600 mt-2">{{ number_format($totalAlokasiPo ?? 0) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Direservasi untuk Pesanan Pelanggan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">ESTIMASI NILAI ASET</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="coins" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">Rp {{ number_format($totalNilaiAset ?? 0, 0, ',', '.') }}</h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Berdasarkan Harga Jual Produk</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">VARIAN KATALOG</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="tag" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $totalVarian ?? 0 }} <span class="text-xs font-semibold text-gray-400">SKU Varian</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Termasuk Varian Remahan</p>
    </div>
</div>

<!-- Tabel Stok Produk Jadi -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">Katalog Produk Jadi & Ketersediaan Stok</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">ID / SKU</th>
                <th class="p-4">NAMA PRODUK & VARIAN</th>
                <th class="p-4">NETTO</th>
                <th class="p-4">HARGA JUAL</th>
                <th class="p-4 font-black">STOK AKTUAL</th>
                <th class="p-4 text-center">STATUS KATEGORI</th>
                <th class="p-4 text-center no-print">AKSI</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($products as $prod)
            @php
                // Hitung total alokasi PO khusus untuk produk ini
                $stokTerkunci = $prod->detailAlokasiPo->sum('jml_alokasi');
                $stokBebasJual = max(0, $prod->stok - $stokTerkunci);
            @endphp
            <tr class="hover:bg-gray-50/50">
                <!-- 1. ID SKU -->
                <td class="p-4 font-bold text-pc-dark">FG-00{{ $prod->id_produk_jadi }}</td>
                
                <!-- 2. NAMA PRODUK & VARIAN (Dibersihkan dari badge stok) -->
                <td class="p-4">
                    <p class="font-black text-pc-dark text-sm">{{ $prod->nama_produk }}</p>
                    <p class="text-[10px] text-gray-400 font-medium mt-0.5">Varian: {{ $prod->varian }}</p>
                </td>
                
                <!-- 3. NETTO -->
                <td class="p-4 font-semibold text-gray-500">{{ $prod->netto }}</td>
                
                <!-- 4. HARGA JUAL -->
                <td class="p-4 font-bold text-pc-dark">
                    Rp {{ number_format($prod->harga_jual ?? 0, 0, ',', '.') }}
                </td>
                
                <!-- 5. STOK AKTUAL & ALOKASI PO (Badge stok ditaruh di sini secara rapi) -->
                <td class="p-4">
                    <p class="font-black text-emerald-600 text-sm">
                        {{ number_format($prod->stok) }} <span class="text-[10px] text-gray-400 font-normal">Pcs (Fisik)</span>
                    </p>
                    <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                        <span class="font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                            🔒 Terkunci PO: {{ number_format($stokTerkunci) }}
                        </span>
                        <span class="font-extrabold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                            ✅ ATP: {{ number_format($stokBebasJual) }}
                        </span>
                    </div>
                </td>
                
                <!-- 6. STATUS KATEGORI -->
                <td class="p-4 text-center">
                    @if(str_contains(strtolower($prod->nama_produk), 'remahan') || str_contains(strtolower($prod->varian), 'remahan'))
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                            Grade B (Remahan)
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Grade A (Utama)
                        </span>
                    @endif
                </td>
                
                <!-- 7. AKSI -->
                <td class="p-4 text-center no-print">
                    <button onclick="openAdjustModal('{{ $prod->id_produk_jadi }}', '{{ $prod->nama_produk }}')" 
                            class="px-3 py-1.5 bg-pc-cream text-pc-maroon border border-pc-orange/20 rounded-lg text-[10px] font-bold hover:bg-amber-100 inline-flex items-center gap-1 cursor-pointer">
                        <i data-lucide="sliders" class="w-3 h-3"></i> Adjust Stok
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-6 text-center text-gray-400">Belum ada data produk jadi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Pop-up Tambah Master Produk Jadi Baru -->
<div id="modal-product" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Tambah Master Produk Jadi Baru</h3>
            <button onclick="document.getElementById('modal-product').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('finished-goods.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama Produk</label>
                <input type="text" name="nama_produk" required placeholder="Misal: Rengginang Udang 100g" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Varian Rasa / Jenis</label>
                    <input type="text" name="varian" required placeholder="Misal: Udang 100g" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Netto / Berat Gram</label>
                    <input type="text" name="netto" required placeholder="100g" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" required placeholder="12500" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Stok Awal Fisik</label>
                    <input type="number" name="stok" value="0" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-product').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan Master Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Manual Adjust Stok -->
<div id="modal-adjust" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Penyesuaian (Adjust) Stok</h3>
            <button onclick="document.getElementById('modal-adjust').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form id="form-adjust" method="POST" class="space-y-3 text-xs">
            @csrf
            <p id="adjust-product-name" class="font-extrabold text-pc-maroon text-sm"></p>
            
            <div>
                <label class="font-bold text-gray-700 block mb-1">Jumlah Penyesuaian Stok</label>
                <input type="number" name="stok_tambahan" required placeholder="Gunakan nilai negatif (-) jika pengurangan" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                <p class="text-[10px] text-gray-400 mt-1">*Contoh: masukan +50 untuk penambahan, atau -10 untuk opnam/retur.</p>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-adjust').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Update Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdjustModal(id, name) {
    document.getElementById('adjust-product-name').innerText = name;
    document.getElementById('form-adjust').action = '/finished-goods/update-stock/' + id;
    document.getElementById('modal-adjust').classList.remove('hidden');
}
</script>

@endsection