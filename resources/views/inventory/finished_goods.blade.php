@extends('layouts.app')

@section('content')

<!-- Alert Flash Message -->
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold mb-4 flex justify-between items-center no-print">
    <span>✓ {{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="text-emerald-600">✕</button>
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-xs font-bold mb-4 flex justify-between items-center no-print">
    <span>✕ {{ session('error') }}</span>
    <button onclick="this.parentElement.remove()" class="text-red-600">✕</button>
</div>
@endif

<!-- Header Modul & Tombol Utama -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div>
        <span class="text-[10px] bg-pc-cream text-pc-maroon border border-pc-orange/20 px-2.5 py-0.5 rounded-md font-bold uppercase tracking-wider">MODUL GUDANG FG</span>
        <h2 class="text-xl font-black text-pc-dark mt-1">Manajemen Stok Produk Jadi (Finished Goods)</h2>
    </div>
    <div class="flex items-center space-x-2">
        <button onclick="document.getElementById('modal-spk').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm hover:bg-red-800 transition flex items-center gap-2 cursor-pointer no-print">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> + Serah Terima dari Produksi (SPK)
        </button>
        <button id="btn-export-csv" title="Download Rekap Stok (CSV)" 
                class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 cursor-pointer no-print">
            <i data-lucide="download" class="w-4 h-4"></i>
        </button>
        <button id="btn-print-page" title="Cetak Laporan Stok" 
                class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 cursor-pointer no-print">
            <i data-lucide="printer" class="w-4 h-4"></i>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Stok -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">TOTAL STOK FISIK GUDANG</p>
            <span class="p-1.5 bg-pc-cream text-pc-maroon rounded-lg"><i data-lucide="archive" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-dark mt-2">{{ number_format($products->sum(fn($p) => $p->inventory->total_physical_stock ?? 0)) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] font-bold text-emerald-600 mt-2">▲ Real-time terintegrasi</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">STOK TERKUNCI PESANAN</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="lock" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-orange mt-2">{{ number_format($products->sum(fn($p) => $p->inventory->reserved_stock ?? 0)) }} <span class="text-xs font-semibold text-gray-400">Pcs Reserved</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">PO Grosir & Booking Stok</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">BEBAS JUAL (ATP)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($products->sum(fn($p) => $p->inventory->atp_stock ?? 0)) }} <span class="text-xs font-semibold text-gray-400">Pcs Siap Jual</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Aman untuk pesanan baru</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-maroon">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">BATCH QC WATCH / FIFO</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="alert-triangle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-maroon mt-2">{{ $expiringBatches->count() }} <span class="text-xs font-semibold text-gray-400">Batch Terpantau</span></h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2">! Prioritas kirim FIFO (&lt; 30 Hari)</p>
    </div>
</div>

<!-- CONTAINER CONTAINER TAB NAVIGASI -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
    <!-- BAR NAVIGASI TAB -->
    <div class="p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3 bg-gray-50/50">
        <div class="flex items-center space-x-6 text-xs font-bold text-gray-400">
            <button id="tab-btn-1" onclick="switchTab(1)" class="tab-link text-pc-maroon border-b-2 border-pc-maroon pb-2 focus:outline-none cursor-pointer">
                📦 Katalog & Saldo Stok Real-Time
            </button>
            <button id="tab-btn-2" onclick="switchTab(2)" class="tab-link hover:text-pc-dark pb-2 focus:outline-none cursor-pointer">
                📄 Riwayat Penerimaan dari Produksi (SPK)
            </button>
            <button id="tab-btn-3" onclick="switchTab(3)" class="tab-link hover:text-pc-dark pb-2 focus:outline-none cursor-pointer">
                📊 Alokasi Kanal & Booking Stok
            </button>
            <button id="tab-btn-4" onclick="switchTab(4)" class="tab-link hover:text-pc-dark pb-2 focus:outline-none cursor-pointer">
                ⏳ Tracking Batch & Expired
            </button>
        </div>

        <div id="search-container" class="flex items-center space-x-2 text-xs no-print">
            <input type="text" id="search-input" placeholder="Cari Nama Produk / SKU..." class="border border-gray-200 rounded-xl px-3 py-1.5 text-xs w-48 focus:outline-none focus:border-pc-orange">
            <button type="button" id="btn-reset-search" class="bg-gray-100 px-3 py-1.5 rounded-xl font-bold text-gray-600 border border-gray-200 hover:bg-gray-200 cursor-pointer">Reset</button>
        </div>
    </div>

    <!-- KONTEN TAB 1: KATALOG & SALDO STOK -->
    <div id="tab-content-1" class="tab-content">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider">
                    <th class="p-4">PRODUK & SKU</th>
                    <th class="p-4">STOK FISIK</th>
                    <th class="p-4">TERKUNCI PO</th>
                    <th class="p-4">BEBAS JUAL (ATP)</th>
                    <th class="p-4">HARGA JUAL</th>
                    <th class="p-4">STATUS GUDANG</th>
                    <th class="p-4 text-center no-print">TINDAKAN CEPAT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium" id="product-table-body">
                @foreach($products as $product)
                <tr class="product-row hover:bg-gray-50/50 transition">
                    <td class="p-4">
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded font-black text-[10px]">{{ $product->gramature ?? '250' }}g</span>
                            <div>
                                <p class="font-bold text-pc-dark search-name">{{ $product->name }}</p>
                                <p class="text-[10px] text-gray-400 search-sku">{{ $product->sku }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="p-4 font-extrabold text-pc-dark">{{ number_format($product->inventory->total_physical_stock ?? 0) }} <span class="text-[10px] font-normal text-gray-400">Pcs</span></td>
                    <td class="p-4 font-bold text-pc-orange">{{ number_format($product->inventory->reserved_stock ?? 0) }} <span class="text-[10px] font-normal text-gray-400">Pcs</span></td>
                    <td class="p-4 font-extrabold text-emerald-600">{{ number_format($product->inventory->atp_stock ?? 0) }} <span class="text-[10px] font-normal text-gray-400">Pcs</span></td>
                    <td class="p-4 font-bold">Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</td>
                    <td class="p-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ● Stok Aman
                        </span>
                    </td>
                    <td class="p-4 text-center no-print">
                        <div class="flex items-center justify-center space-x-1">
                            <button type="button" class="btn-detail px-3 py-1 border border-gray-200 rounded-lg text-gray-600 font-bold hover:bg-gray-50 cursor-pointer"
                                    data-name="{{ $product->name }}" data-sku="{{ $product->sku }}"
                                    data-physical="{{ number_format($product->inventory->total_physical_stock ?? 0) }}"
                                    data-reserved="{{ number_format($product->inventory->reserved_stock ?? 0) }}"
                                    data-atp="{{ number_format($product->inventory->atp_stock ?? 0) }}">
                                Detail
                            </button>
                            <button type="button" class="btn-allocate px-3 py-1 bg-amber-100 text-amber-800 rounded-lg font-bold hover:bg-amber-200 cursor-pointer"
                                    data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                    data-atp="{{ $product->inventory->atp_stock ?? 0 }}">
                                Alokasi PO
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- KONTEN TAB 2: RIWAYAT PENERIMAAN SPK -->
    <div id="tab-content-2" class="tab-content hidden p-4">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase">
                    <th class="p-3">NO SPK / BATCH</th>
                    <th class="p-3">PRODUK</th>
                    <th class="p-3">QTY DITERIMA</th>
                    <th class="p-3">TANGGAL MASUK</th>
                    <th class="p-3">STATUS SERAH TERIMA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($completedBatches as $batch)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-bold text-pc-maroon">{{ $batch->workOrder->spk_number ?? '-' }} <br><span class="text-[10px] text-gray-400">{{ $batch->batch_code }}</span></td>
                    <td class="p-3 font-bold">{{ $batch->product->name ?? ($batch->workOrder->product->name ?? 'Produk') }}</td>
                    <td class="p-3 font-extrabold text-emerald-600">{{ number_format($batch->actual_net_good_qty ?? $batch->net_good_qty) }} Pcs</td>
                    <td class="p-3 text-gray-500">{{ $batch->received_at ? \Carbon\Carbon::parse($batch->received_at)->format('d M Y H:i') : '-' }}</td>
                    <td class="p-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">✓ TERMASUK KE STOK FISIK</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-4 text-center text-gray-400">Belum ada riwayat penerimaan SPK yang selesai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- KONTEN TAB 3: ALOKASI KANAL & BOOKING STOK -->
    <div id="tab-content-3" class="tab-content hidden p-6 space-y-4">
        <h4 class="font-extrabold text-xs text-pc-dark uppercase tracking-wider">Rincian Stok Terkunci per Kanal Penjualan</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="p-4 border border-gray-100 rounded-xl bg-gray-50">
                <p class="font-bold text-pc-maroon">PO B2B Grosir & Supermarket</p>
                <p class="text-2xl font-black mt-1">2.100 Pcs</p>
                <p class="text-[10px] text-gray-400 mt-1">Status: Terkunci Otomatis via Alokasi PO</p>
            </div>
            <div class="p-4 border border-gray-100 rounded-xl bg-gray-50">
                <p class="font-bold text-pc-orange">Webstore Resmi D2C & E-Commerce</p>
                <p class="text-2xl font-black mt-1">1.420 Pcs</p>
                <p class="text-[10px] text-gray-400 mt-1">Status: Checkout Aktif Pelanggan</p>
            </div>
            <div class="p-4 border border-gray-100 rounded-xl bg-gray-50">
                <p class="font-bold text-amber-600">Kasir Toko Oleh-oleh Condet</p>
                <p class="text-2xl font-black mt-1">600 Pcs</p>
                <p class="text-[10px] text-gray-400 mt-1">Status: Display Rak Toko Fisik</p>
            </div>
        </div>
    </div>

    <!-- KONTEN TAB 4: TRACKING BATCH & EXPIRED (FIFO) -->
    <div id="tab-content-4" class="tab-content hidden p-4">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase">
                    <th class="p-3">KODE BATCH</th>
                    <th class="p-3">PRODUK</th>
                    <th class="p-3">TANGGAL PRODUKSI</th>
                    <th class="p-3">TANGGAL EXPIRED</th>
                    <th class="p-3">REKOMENDASI PRIORITAS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($expiringBatches as $batch)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-bold text-pc-dark">{{ $batch->batch_code }}</td>
                    <td class="p-3 font-bold">{{ $batch->product->name ?? 'Produk Rengginang' }}</td>
                    <td class="p-3 text-gray-500">{{ $batch->production_date ? \Carbon\Carbon::parse($batch->production_date)->format('d M Y') : '-' }}</td>
                    <td class="p-3 font-bold text-pc-maroon">{{ $batch->expired_date ? \Carbon\Carbon::parse($batch->expired_date)->format('d M Y') : '-' }}</td>
                    <td class="p-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">⚠️ PRIORITAS KIRIM FIFO</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-4 text-center text-gray-400">Tidak ada batch terpantau mendekati kadaluwarsa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Bottom Section: Penerimaan Dapur & Alokasi Omnichannel -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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

        <div class="space-y-3">
            @forelse($pendingBatches as $batch)
            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/40 space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="bg-red-100 text-pc-maroon font-bold text-[10px] px-2 py-0.5 rounded">
                            {{ $batch->workOrder->spk_number ?? 'SPK-2026-001' }}
                        </span>
                        <h5 class="font-bold text-pc-dark text-xs mt-1">
                            {{ $batch->product->name ?? ($batch->workOrder->product->name ?? 'Produk Rengginang') }}
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
                        <button type="submit" class="bg-pc-maroon text-white font-bold px-3 py-1.5 rounded-lg hover:bg-red-800 transition shadow-sm cursor-pointer no-print">
                            + Terima ke Fisik Gudang
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-6 border border-dashed border-gray-200 rounded-xl text-center space-y-2">
                <p class="text-xs text-gray-400">Tidak ada antrean serah terima batch dari dapur saat ini.</p>
                <button onclick="document.getElementById('modal-spk').classList.remove('hidden')" class="text-xs font-bold text-pc-maroon underline cursor-pointer no-print">
                    + Buat Antrean SPK Produksi Baru
                </button>
            </div>
            @endforelse
        </div>
    </div>

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

<!-- ================= MODAL POP-UPS ================= -->

<div id="modal-spk" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Serah Terima Hasil Produksi (SPK Dapur)</h3>
            <button onclick="document.getElementById('modal-spk').classList.add('hidden')" class="text-gray-400 font-bold">✕</button>
        </div>
        <form action="{{ route('production-batch.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Produk Hasil Olahan</label>
                <select name="product_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->gramature ?? '250' }}g)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="font-bold text-gray-700 block mb-1">Jumlah Hasil Bersih / Pass QC (Pcs)</label>
                <input type="number" name="actual_net_good_qty" required placeholder="Misal: 500" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>
            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-spk').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm">Kirim Antrean ke Gudang</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-detail" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <div>
                <h3 id="detail-product-name" class="font-extrabold text-pc-dark text-sm">Nama Produk</h3>
                <p id="detail-product-sku" class="text-[10px] text-gray-400">SKU-0000</p>
            </div>
            <button onclick="closeDetailModal()" class="text-gray-400 font-bold">✕</button>
        </div>
        <div class="space-y-2 text-xs">
            <div class="flex justify-between py-1 border-b border-gray-50">
                <span class="text-gray-500">Total Stok Fisik:</span>
                <span id="detail-physical-stock" class="font-bold text-pc-dark">0 Pcs</span>
            </div>
            <div class="flex justify-between py-1 border-b border-gray-50">
                <span class="text-gray-500">Terkunci Pesanan (PO):</span>
                <span id="detail-reserved-stock" class="font-bold text-pc-orange">0 Pcs</span>
            </div>
            <div class="flex justify-between py-1">
                <span class="text-gray-500">Bebas Jual (ATP):</span>
                <span id="detail-atp-stock" class="font-bold text-emerald-600">0 Pcs</span>
            </div>
        </div>
        <div class="pt-2 flex justify-end">
            <button onclick="closeDetailModal()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-bold cursor-pointer">Tutup</button>
        </div>
    </div>
</div>

<div id="modal-allocate" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Alokasi Stok Booking PO</h3>
            <button onclick="closeAllocateModal()" class="text-gray-400 font-bold">✕</button>
        </div>
        <form id="form-allocate-po" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Produk</label>
                <input type="text" id="allocate-product-name" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-500 font-bold">
            </div>
            <div>
                <label class="font-bold text-gray-700 block mb-1">Stok Bebas Jual (ATP) Tersedia</label>
                <p id="allocate-atp-display" class="font-black text-emerald-600 text-sm">0 Pcs</p>
            </div>
            <div>
                <label class="font-bold text-gray-700 block mb-1">Jumlah Stok yang Ingin Dikunci (Pcs)</label>
                <input type="number" name="allocated_qty" required min="1" placeholder="Masukkan jumlah Pcs" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>
            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="closeAllocateModal()" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-orange text-white rounded-xl font-bold hover:bg-amber-600 transition shadow-sm cursor-pointer">Kunci Stok PO</button>
            </div>
        </form>
    </div>
</div>

<style>
@media print {
    .no-print, header, sidebar, footer { display: none !important; }
    body { background: #ffffff !important; color: #000000 !important; }
    .shadow-sm, .shadow-xl { box-shadow: none !important; }
    .border { border-color: #e5e7eb !important; }
}
</style>

<!-- JAVASCRIPT TAB SWITCHER, SEARCH, PRINT & EXPORT -->
<script>
function switchTab(tabIndex) {
    // Sembunyikan Semua Tab Content
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    
    // Reset Style Semua Tombol Tab
    document.querySelectorAll('.tab-link').forEach(btn => {
        btn.classList.remove('text-pc-maroon', 'border-b-2', 'border-pc-maroon');
        btn.classList.add('hover:text-pc-dark');
    });

    // Tampilkan Tab Content yang Dipilih
    document.getElementById('tab-content-' + tabIndex).classList.remove('hidden');

    // Highlight Tombol Tab Aktif
    const activeBtn = document.getElementById('tab-btn-' + tabIndex);
    activeBtn.classList.add('text-pc-maroon', 'border-b-2', 'border-pc-maroon');
    activeBtn.classList.remove('hover:text-pc-dark');

    // Sembunyikan Kotak Search jika tidak berada di Tab 1
    const searchBox = document.getElementById('search-container');
    if (tabIndex === 1) {
        searchBox.classList.remove('hidden');
    } else {
        searchBox.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const resetBtn = document.getElementById('btn-reset-search');
    const rows = document.querySelectorAll('.product-row');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            rows.forEach(row => {
                const name = row.querySelector('.search-name')?.innerText.toLowerCase() || '';
                const sku = row.querySelector('.search-sku')?.innerText.toLowerCase() || '';
                row.style.display = (name.includes(query) || sku.includes(query)) ? '' : 'none';
            });
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            rows.forEach(row => row.style.display = '');
        });
    }

    const printBtn = document.getElementById('btn-print-page');
    if (printBtn) {
        printBtn.addEventListener('click', function () { window.print(); });
    }

    const exportBtn = document.getElementById('btn-export-csv');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            let csvData = [['Nama Produk', 'SKU', 'Stok Fisik (Pcs)', 'Terkunci PO (Pcs)', 'Bebas Jual ATP (Pcs)', 'Harga Jual (Rp)']];
            const tableRows = document.querySelectorAll('#product-table-body .product-row');
            tableRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const name = row.querySelector('.search-name')?.innerText.trim() || '';
                    const sku = row.querySelector('.search-sku')?.innerText.trim() || '';
                    const physical = row.children[1]?.innerText.replace(/[^0-9]/g, '') || '0';
                    const reserved = row.children[2]?.innerText.replace(/[^0-9]/g, '') || '0';
                    const atp = row.children[3]?.innerText.replace(/[^0-9]/g, '') || '0';
                    const price = row.children[4]?.innerText.replace(/[^0-9]/g, '') || '0';
                    csvData.push([`"${name}"`, `"${sku}"`, physical, reserved, atp, price]);
                }
            });

            let csvContent = "data:text/csv;charset=utf-8," + csvData.map(e => e.join(",")).join("\n");
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "Rekap_Stok_Finished_Goods_" + new Date().toISOString().slice(0, 10) + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    document.querySelectorAll('.btn-detail').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('detail-product-name').innerText = this.dataset.name;
            document.getElementById('detail-product-sku').innerText = this.dataset.sku;
            document.getElementById('detail-physical-stock').innerText = this.dataset.physical + ' Pcs';
            document.getElementById('detail-reserved-stock').innerText = this.dataset.reserved + ' Pcs';
            document.getElementById('detail-atp-stock').innerText = this.dataset.atp + ' Pcs';
            document.getElementById('modal-detail').classList.remove('hidden');
        });
    });

    document.querySelectorAll('.btn-allocate').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            const atpVal = parseInt(this.dataset.atp) || 0;

            document.getElementById('allocate-product-name').value = productName;
            document.getElementById('allocate-atp-display').innerText = atpVal.toLocaleString() + ' Pcs';
            document.getElementById('form-allocate-po').action = '/finished-goods/' + productId + '/allocate-po';
            document.getElementById('modal-allocate').classList.remove('hidden');
        });
    });
});

function closeDetailModal() { document.getElementById('modal-detail').classList.add('hidden'); }
function closeAllocateModal() { document.getElementById('modal-allocate').classList.add('hidden'); }
</script>

@endsection