@extends('layouts.app')

@section('content')

<!-- Banner Header Dual-Status -->
<div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-pc-cream flex items-center justify-center text-pc-maroon border border-pc-orange/20">
            <i data-lucide="layers" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-base font-extrabold text-pc-dark">Pusat Operasional Fisik & Kontrol Pembayaran</h2>
                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-emerald-200">● 5 Tahap Operasional Bersih</span>
            </div>
            <p class="text-xs text-pc-maroon font-bold mt-0.5">Dual-Status: Fisik vs Finansial</p>
        </div>
    </div>
    <div class="flex items-center space-x-2">
        <button class="px-3.5 py-2 bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-100 transition flex items-center gap-1.5">
            <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i> Perbarui Data
        </button>
        <button class="px-4 py-2 bg-pc-maroon text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-sm flex items-center gap-1.5">
            <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Buat Surat Jalan
        </button>
    </div>
</div>

<!-- 5 Pipeline Operasional Gudang -->
<div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-4">
    <div class="flex justify-between items-center text-xs">
        <h3 class="font-extrabold text-pc-dark uppercase tracking-wider flex items-center gap-2">
            <span class="bg-pc-maroon text-white w-5 h-5 rounded flex items-center justify-center text-[10px] font-black">5</span> 
            PIPELINE OPERASIONAL & DISTRIBUSI FISIK (5 TAHAP GUDANG)
        </h3>
        <span class="text-gray-400 font-medium text-[11px]">Alur Fisik Selesai saat Barang Diterima • <span class="text-pc-orange font-bold cursor-pointer">Flag Bayar Mandiri</span></span>
    </div>

    <!-- Alert Dual Status Principle -->
    <div class="bg-amber-50/60 border border-amber-200/70 rounded-xl p-3 text-[11px] text-amber-900 flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
        <p class="flex items-center gap-2">
            <i data-lucide="shield-alert" class="w-4 h-4 text-pc-orange shrink-0"></i>
            <span><strong>Prinsip Dual-Status:</strong> Tim Gudang & Kurir bertanggung jawab atas kelancaran fisik hingga barang <strong>Diterima Pelanggan</strong>. Tim Kasir/Owner mengelola status bayar terpisah tanpa menghambat pergerakan stok.</span>
        </p>
        <div class="flex space-x-1 font-bold text-[10px] shrink-0">
            <span class="px-2 py-1 bg-white rounded border border-amber-200 text-gray-600 cursor-pointer">[Semua]</span>
            <span class="px-2 py-1 bg-white rounded border border-amber-200 text-emerald-600 cursor-pointer">[Lunas]</span>
            <span class="px-2 py-1 bg-white rounded border border-amber-200 text-amber-600 cursor-pointer">[DP 50%]</span>
            <span class="px-2 py-1 bg-white rounded border border-amber-200 text-red-600 cursor-pointer">[Belum Bayar]</span>
        </div>
    </div>

    <!-- 5 Cards Pipeline -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <div class="flex justify-between items-center text-gray-400 mb-1">
                <span class="font-bold text-gray-700">1</span>
                <i data-lucide="inbox" class="w-3.5 h-3.5"></i>
            </div>
            <p class="font-bold text-gray-800">PO Diterima & Alokasi</p>
            <p class="text-[11px] font-bold text-pc-maroon mt-2">12 Pesanan <span class="text-[9px] text-emerald-600 font-normal">Stok Dialokasikan</span></p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <div class="flex justify-between items-center text-gray-400 mb-1">
                <span class="font-bold text-gray-700">2</span>
                <i data-lucide="factory" class="w-3.5 h-3.5"></i>
            </div>
            <p class="font-bold text-gray-800">Diproses SPK Pabrik</p>
            <p class="text-[11px] font-bold text-gray-700 mt-2">4 SPK Aktif <span class="text-[9px] text-gray-400 font-normal">Dapur Condet</span></p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <div class="flex justify-between items-center text-gray-400 mb-1">
                <span class="font-bold text-gray-700">3</span>
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>
            </div>
            <p class="font-bold text-gray-800">Produksi & QC Pass</p>
            <p class="text-[11px] font-bold text-emerald-600 mt-2">QC 100% Pass <span class="text-[9px] text-gray-400 font-normal">Siap Packing</span></p>
        </div>

        <div class="p-3.5 rounded-xl border-2 border-pc-maroon bg-red-50/30 relative shadow-sm">
            <span class="absolute -top-2.5 right-2 bg-pc-maroon text-white text-[8px] font-extrabold px-2 py-0.5 rounded-full uppercase">FOKUS KIRIM</span>
            <div class="flex justify-between items-center text-pc-maroon mb-1">
                <span class="font-bold">4</span>
                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
            </div>
            <p class="font-bold text-pc-maroon">Dikirim (Surat Jalan)</p>
            <p class="text-[11px] font-bold text-pc-maroon mt-2">1 PO Berangkat <span class="underline text-pc-maroon font-extrabold cursor-pointer ml-1">Cetak SJ ></span></p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <div class="flex justify-between items-center text-gray-400 mb-1">
                <span class="font-bold text-gray-700">5</span>
                <i data-lucide="package-check" class="w-3.5 h-3.5 text-emerald-500"></i>
            </div>
            <p class="font-bold text-gray-800">Diterima Pelanggan</p>
            <p class="text-[11px] font-bold text-emerald-600 mt-2">operasional selesai 100% Klir</p>
        </div>
    </div>
</div>

<!-- 4 Metrik Kartu Utama (Stok Realtime) -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">TOTAL STOK FISIK</p>
            <span class="p-1.5 bg-pc-cream rounded-lg text-pc-maroon"><i data-lucide="archive" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-dark mt-2">{{ number_format($totalPhysical) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] font-bold text-emerald-600 mt-3 flex items-center gap-1">
            <i data-lucide="trending-up" class="w-3 h-3"></i> +650 Pcs serah terima batch ini
        </p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">TERKUNCI PESANAN</p>
            <span class="p-1.5 bg-amber-50 rounded-lg text-pc-orange"><i data-lucide="lock" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-orange mt-2">{{ number_format($totalReserved) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-3">B2B: 2.100 | Web: 1.420 | Toko: 600</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">BEBAS JUAL (ATP)</p>
            <span class="p-1.5 bg-emerald-50 rounded-lg text-emerald-600"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($totalATP) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-3">✓ Siap dialokasikan seketika</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">BARANG KELUAR HARI INI</p>
            <span class="p-1.5 bg-red-50 rounded-lg text-pc-maroon"><i data-lucide="truck" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-3xl font-black text-pc-maroon mt-2">940 <span class="text-xs font-semibold text-gray-400">Pcs Keluar (Fisik)</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-3">✓ Semua SJ Terbit • <strong>+2 PO Menunggu Lunas</strong></p>
    </div>
</div>

<!-- 3 Grid Bagian Bawah -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Grafik Tren Arus Barang -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h4 class="text-xs font-extrabold text-pc-dark uppercase tracking-wider">TREN ARUS BARANG 7 HARI</h4>
            <div class="flex items-center space-x-3 text-[10px] font-bold">
                <span class="text-emerald-600">● Masuk</span>
                <span class="text-pc-maroon">● Keluar</span>
            </div>
        </div>
        <div class="h-44 flex items-end justify-between px-2 pt-6 border-b border-gray-50 pb-2">
            @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
            <div class="flex flex-col items-center space-y-1">
                <div class="flex items-end space-x-1">
                    <div class="w-2.5 bg-emerald-500 rounded-t h-16"></div>
                    <div class="w-2.5 bg-pc-maroon rounded-t h-24"></div>
                </div>
                <span class="text-[10px] text-gray-400 font-bold">{{ $day }}</span>
            </div>
            @endforeach
        </div>
        <div class="flex justify-between text-[10px] text-gray-400 font-bold pt-1">
            <span>↑ Masuk: 6.950</span>
            <span>↓ Keluar: 7.190</span>
        </div>
    </div>

    <!-- Alokasi Multi-Kanal -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h4 class="text-xs font-extrabold text-pc-dark uppercase tracking-wider">ALOKASI MULTI-KANAL</h4>
            <span class="text-[10px] bg-amber-50 text-pc-orange border border-amber-200 px-2 py-0.5 rounded-full font-bold">100% Saluran</span>
        </div>
        <div class="space-y-4 text-xs pt-2">
            <div>
                <div class="flex justify-between font-bold text-gray-700 mb-1">
                    <span class="text-[11px]">PO B2B Grosir</span>
                    <span class="text-pc-maroon">2.100 Pcs <span class="text-gray-400 font-normal">(51%)</span></span>
                </div>
                <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-pc-maroon h-full w-[51%]"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between font-bold text-gray-700 mb-1">
                    <span class="text-[11px]">Webstore Resmi D2C</span>
                    <span class="text-pc-orange">1.420 Pcs <span class="text-gray-400 font-normal">(34%)</span></span>
                </div>
                <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-pc-orange h-full w-[34%]"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between font-bold text-gray-700 mb-1">
                    <span class="text-[11px]">Kasir Toko Condet</span>
                    <span class="text-pc-yellow">600 Pcs <span class="text-gray-400 font-normal">(15%)</span></span>
                </div>
                <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-pc-yellow h-full w-[15%]"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktivitas Transaksi Operasional (SOP List) -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h4 class="text-xs font-extrabold text-pc-dark uppercase tracking-wider">AKTIVITAS TRANSAKSI OPERASIONAL (SOP)</h4>
            <span class="text-[9px] bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-bold">Realtime</span>
        </div>
        
        <div class="space-y-3">
            <!-- Transaksi Item 1 -->
            <div class="p-3 bg-gray-50/70 rounded-xl border border-gray-200/80 space-y-1.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-extrabold text-pc-maroon text-[11px]">PO B2B Grosir</span>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] font-extrabold rounded">DP 50%</span>
                </div>
                <p class="text-xs font-bold text-gray-800">Toko Oleh-Oleh Haji Kramat Jati</p>
                <div class="flex justify-between items-center pt-1">
                    <span class="text-[10px] text-gray-500 font-semibold">450 Pcs (Ori 250g)</span>
                    <button class="bg-pc-maroon text-white text-[10px] px-3 py-1 rounded-lg font-bold hover:bg-red-800 transition">
                        Cetak Surat Jalan
                    </button>
                </div>
            </div>

            <!-- Transaksi Item 2 -->
            <div class="p-3 bg-gray-50/70 rounded-xl border border-gray-200/80 space-y-1.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-extrabold text-pc-maroon text-[11px]">PO B2B Grosir</span>
                    <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[9px] font-extrabold rounded">Belum Bayar</span>
                </div>
                <p class="text-xs font-bold text-gray-800">Grosir Berkah Pasar Minggu</p>
                <div class="flex justify-between items-center pt-1">
                    <span class="text-[10px] text-gray-500 font-semibold">600 Pcs (Mix)</span>
                    <button class="bg-pc-orange text-white text-[10px] px-3 py-1 rounded-lg font-bold hover:bg-orange-600 transition">
                        Kirim Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection