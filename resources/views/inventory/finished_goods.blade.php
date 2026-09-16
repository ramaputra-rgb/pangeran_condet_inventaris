@extends('layouts.app')

@section('content')

<!-- Alert Flash Message Sukses -->
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold mb-4 flex justify-between items-center">
    <span>✓ {{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="text-emerald-600">✕</button>
</div>
@endif

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <span class="text-[10px] bg-pc-cream text-pc-maroon border border-pc-orange/20 px-2.5 py-0.5 rounded-md font-bold uppercase tracking-wider">MODUL GUDANG FG</span>
        <h2 class="text-xl font-black text-pc-dark mt-1">Manajemen Stok Produk Jadi (Finished Goods)</h2>
    </div>
    <div class="flex items-center space-x-2">
        <!-- TOMBOL HEADER: MEMBUKA MODAL POP-UP SPK BARU -->
        <button onclick="document.getElementById('modal-spk').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm hover:bg-red-800 transition flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> + Serah Terima dari Produksi (SPK)
        </button>
        <button class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50">
            <i data-lucide="download" class="w-4 h-4"></i>
        </button>
        <button class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50">
            <i data-lucide="printer" class="w-4 h-4"></i>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Stok & FIFO Alert -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">TOTAL STOK FISIK GUDANG</p>
            <span class="p-1.5 bg-pc-cream text-pc-maroon rounded-lg"><i data-lucide="archive" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-dark mt-2">{{ number_format($products->sum(fn($p) => $p->inventory->total_physical_stock ?? 0)) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] font-bold text-emerald-600 mt-2">▲ +650 pcs masuk dari SPK hari ini</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">STOK TERKUNCI PESANAN</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="lock" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-orange mt-2">{{ number_format($products->sum(fn($p) => $p->inventory->reserved_stock ?? 0)) }} <span class="text-xs font-semibold text-gray-400">Pcs Reserved</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">PO Grosir 2.100 • Web 1.420 • Toko...</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">BEBAS JUAL (ATP)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($products->sum(fn($p) => $p->inventory->atp_stock ?? 0)) }} <span class="text-xs font-semibold text-gray-400">Pcs Siap Jual</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Aman untuk pesanan baru/instan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-maroon">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">BATCH QC WATCH / FIFO</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="alert-triangle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-maroon mt-2">2 <span class="text-xs font-semibold text-gray-400">Batch Terpantau</span></h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2">! Perlu prioritas kirim FIFO (&lt; 30 Hari)</p>
    </div>
</div>

<!-- Tabel Katalog & Saldo Stok Real-Time -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3 bg-gray-50/50">
        <div class="flex items-center space-x-4 text-xs font-bold text-pc-dark">
            <span class="border-b-2 border-pc-maroon text-pc-maroon pb-1">Katalog & Saldo Stok Real-Time</span>
            <span class="text-gray-400 cursor-pointer hover:text-pc-dark">Riwayat Penerimaan dari Produksi (SPK)</span>
            <span class="text-gray-400 cursor-pointer hover:text-pc-dark">Alokasi Kanal & Booking Stok</span>
        </div>
        <div class="flex items-center space-x-2 text-xs">
            <input type="text" placeholder="Cari Nama Produk / SKU..." class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs w-48 focus:outline-none focus:border-pc-orange">
            <button class="bg-gray-100 px-3 py-1.5 rounded-xl font-bold text-gray-600 border border-gray-200">Reset</button>
        </div>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider">
                <th class="p-4">PRODUK & SKU</th>
                <th class="p-4">STOK FISIK</th>
                <th class="p-4">TERKUNCI PO</th>
                <th class="p-4">BEBAS JUAL (ATP)</th>
                <th class="p-4">HARGA JUAL</th>
                <th class="p-4">STATUS & VERIFIKASI GUDANG</th>
                <th class="p-4 text-center">TINDAKAN CEPAT</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @foreach($products as $product)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="p-4">
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded font-black text-[10px]">{{ $product->gramature }}g</span>
                        <div>
                            <p class="font-bold text-pc-dark">{{ $product->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ $product->sku }}</p>
                        </div>
                    </div>
                </td>
                <td class="p-4 font-extrabold text-pc-dark">{{ number_format($product->inventory->total_physical_stock ?? 0) }} <span class="text-[10px] font-normal text-gray-400">Pcs</span></td>
                <td class="p-4 font-bold text-pc-orange">{{ number_format($product->inventory->reserved_stock ?? 0) }} <span class="text-[10px] font-normal text-gray-400">Pcs</span></td>
                <td class="p-4 font-extrabold text-emerald-600">{{ number_format($product->inventory->atp_stock ?? 0) }} <span class="text-[10px] font-normal text-gray-400">Pcs</span></td>
                <td class="p-4 font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                <td class="p-4">
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        ● Stok Aman
                    </span>
                </td>
                <td class="p-4 text-center">
                    <div class="flex items-center justify-center space-x-1">
                        <button class="px-3 py-1 border border-gray-200 rounded-lg text-gray-600 font-bold hover:bg-gray-50">Detail</button>
                        <button class="px-3 py-1 bg-amber-100 text-amber-800 rounded-lg font-bold hover:bg-amber-200">Alokasi PO</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Bottom Section: Penerimaan Dapur & Alokasi Omnichannel -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Panel Penerimaan Terkini dari Dapur Produksi -->
    <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <div>
                <h4 class="text-xs font-extrabold text-pc-dark uppercase tracking-wider flex items-center gap-2">
                    📄 Penerimaan Terkini dari Dapur Produksi
                </h4>
                <p class="text-[10px] text-gray-400 mt-0.5">Serah terima hasil penggorengan & pengemasan SPK dapur ke fisik gudang</p>
            </div>
            <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-1 rounded-full">{{ $pendingBatches->count() }} Antrean Gudang</span>
        </div>

        <!-- Cards Batch Antrean Dinamis -->
        <div class="space-y-3">
            @forelse($pendingBatches as $batch)
            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/40 space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="bg-red-100 text-pc-maroon font-bold text-[10px] px-2 py-0.5 rounded">
                            {{ $batch->workOrder->spk_number ?? 'SPK-2026-001' }}
                        </span>
                        <h5 class="font-bold text-pc-dark text-xs mt-1">
                            {{ $batch->product->name ?? ($batch->workOrder->product->name ?? 'Rengginang Ikan Tenggiri') }}
                        </h5>
                        <p class="text-[10px] text-gray-400">
                            Batch: <strong>{{ $batch->batch_code }}</strong> • Dapur: <strong>Dapur Condet Utama</strong>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-emerald-600">
                            {{ number_format($batch->actual_net_good_qty ?? ($batch->net_good_qty ?? $batch->total_dapur_qty)) }} Pcs
                        </span>
                        <p class="text-[9px] text-emerald-700 font-bold">✓ QC 100% Lolos Siap Jual</p>
                    </div>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200/60 pt-2 text-[10px]">
                    <span class="text-amber-800 font-semibold">● Status: Menunggu Tanda Tangan Masuk Gudang</span>
                    
                    <form action="{{ route('production-batch.accept', $batch->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-pc-maroon text-white font-bold px-3 py-1.5 rounded-lg hover:bg-red-800 transition shadow-sm">
                            + Terima ke Fisik Gudang
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-6 border border-dashed border-gray-200 rounded-xl text-center space-y-2">
                <p class="text-xs text-gray-400">Tidak ada antrean serah terima batch dari dapur saat ini.</p>
                <button onclick="document.getElementById('modal-spk').classList.remove('hidden')" class="text-xs font-bold text-pc-maroon underline">
                    + Buat Antrean SPK Produksi Baru
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Panel Alokasi Kanal (Omnichannel) -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <h4 class="text-xs font-extrabold text-pc-dark uppercase tracking-wider border-b border-gray-100 pb-3">
            Alokasi Kanal (Omnichannel)
        </h4>
        <div class="space-y-4 text-xs">
            <div>
                <div class="flex justify-between font-bold text-gray-700 mb-1">
                    <span>PO B2B Grosir & Mitra Supermarket</span>
                    <span class="text-pc-maroon">2.100 Pcs</span>
                </div>
                <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-pc-maroon h-full w-[51%]"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between font-bold text-gray-700 mb-1">
                    <span>Webstore Resmi D2C & E-Commerce</span>
                    <span class="text-pc-orange">1.420 Pcs</span>
                </div>
                <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-pc-orange h-full w-[34%]"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between font-bold text-gray-700 mb-1">
                    <span>Kasir Toko Oleh-oleh Condet</span>
                    <span class="text-pc-yellow">600 Pcs</span>
                </div>
                <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-pc-yellow h-full w-[15%]"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL POP-UP BUAT ANTREAN SERAH TERIMA SPK DAPUR -->
<div id="modal-spk" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Serah Terima Hasil Produksi (SPK Dapur)</h3>
            <button onclick="document.getElementById('modal-spk').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('production-batch.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Produk Hasil Olahan</label>
                <select name="product_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->gramature }}g)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Jumlah Hasil Bersih / Pass QC (Pcs)</label>
                <input type="number" name="actual_net_good_qty" required placeholder="Misal: 500" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-spk').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm">
                    Kirim Antrean ke Gudang
                </button>
            </div>
        </form>
    </div>
</div>

@endsection