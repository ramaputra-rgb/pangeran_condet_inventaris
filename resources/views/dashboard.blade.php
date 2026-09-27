@extends('layouts.app')

@section('content')

<!-- Header Dashboard -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-pc-dark">Dashboard Eksekutif ERP</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pusat kendali dan pemantauan real-time alur rantai pasok UD Pangeran Condet.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <span class="px-4 py-2 bg-pc-cream text-pc-maroon font-extrabold text-xs rounded-2xl border border-pc-orange/20 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            Sistem ERP Aktif Terintegrasi
        </span>
    </div>
</div>

<!-- 4 Kartu Metrics Utama -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-maroon">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">PESANAN PO AKTIF</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $poActiveCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">PO Active</span></h3>
        <p class="text-[10px] text-pc-maroon font-bold mt-2">⚡ Menunggu & Dalam Proses Dapur</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-emerald-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">STOK FISIK READY (FG)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="boxes" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($totalStokFg ?? 0) }} <span class="text-xs font-semibold text-gray-400">Pcs/Pack</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Nilai Aset: Rp {{ number_format($totalNilaiAsetFg ?? 0, 0, ',', '.') }}</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">TOTAL QC GRADE A (PUNCH)</p>
            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($totalGradeA ?? 0) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-amber-600 font-bold mt-2">Grade B Remahan: {{ number_format($totalGradeB ?? 0) }} Pack</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-purple-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-purple-600 uppercase tracking-wider">ARMADA PENGIRIMAN</p>
            <span class="p-1.5 bg-purple-50 text-purple-600 rounded-lg"><i data-lucide="truck" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-purple-700 mt-2">{{ $pengirimanProses ?? 0 }} <span class="text-xs font-semibold text-gray-400">Dalam Perjalanan</span></h3>
        <p class="text-[10px] text-purple-600 font-bold mt-2">🚚 Kurir Sedang Menuju Lokasi</p>
    </div>
</div>

<!-- Ringkasan Alert & Quick Actions -->
@if(($stokKritisCount ?? 0) > 0)
<div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="p-2 bg-red-100 text-pc-maroon rounded-xl"><i data-lucide="alert-triangle" class="w-5 h-5"></i></span>
        <div>
            <h4 class="text-xs font-extrabold text-pc-maroon">Peringatan Stok Bahan Produksi Kritis!</h4>
            <p class="text-[11px] text-red-700">Terdapat {{ $stokKritisCount }} bahan baku yang stoknya berada di bawah batas minimum (<= 10 unit).</p>
        </div>
    </div>
    <a href="{{ route('raw-materials.index') }}" class="px-4 py-2 bg-pc-maroon text-white font-extrabold text-xs rounded-xl hover:bg-red-800 transition">
        Belanja Bahan Baku Now
    </a>
</div>
@endif

<!-- Grid 2 Kolom: Aktivitas Batch Produksi & PO Pelanggan Terbaru -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    
    <!-- Kolom Kiri: Batch Produksi QC Terbaru -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <span class="text-xs font-bold text-pc-dark">Batch Produksi Dapur Terbaru</span>
            <a href="{{ route('log-produksi.index') }}" class="text-[10px] font-extrabold text-pc-maroon hover:underline">Lihat Semua →</a>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                    <th class="p-3">NO. BATCH</th>
                    <th class="p-3">PO ACUAN</th>
                    <th class="p-3 text-center">GRADE A</th>
                    <th class="p-3 text-center">GRADE B</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium">
                @forelse($recentLogProduksi as $log)
                <tr class="hover:bg-gray-50/50">
                    <td class="p-3 font-bold text-pc-dark">BATCH-00{{ $log->no_batch }}</td>
                    <td class="p-3 text-gray-600 font-semibold">{{ $log->poProduk->nama_pelanggan ?? 'PO #'.$log->no_po_produk }}</td>
                    <td class="p-3 text-center font-black text-emerald-600">+{{ number_format($log->jml_grade_A) }} Pcs</td>
                    <td class="p-3 text-center font-black text-pc-orange">+{{ number_format($log->jml_grade_B) }} Pack</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-400">Belum ada aktivitas produksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Kolom Kanan: PO Pelanggan Terbaru -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <span class="text-xs font-bold text-pc-dark">Pesanan PO Pelanggan Masuk Terbaru</span>
            <a href="{{ route('po-produk.index') }}" class="text-[10px] font-extrabold text-pc-maroon hover:underline">Lihat Semua →</a>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                    <th class="p-3">NO. PO</th>
                    <th class="p-3">PELANGGAN</th>
                    <th class="p-3 text-center">QTY PO</th>
                    <th class="p-3 text-center">STATUS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium">
                @forelse($recentPoList as $po)
                <tr class="hover:bg-gray-50/50">
                    <td class="p-3 font-bold text-pc-dark">PO-00{{ $po->no_po_produk }}</td>
                    <td class="p-3 font-bold text-pc-dark">{{ $po->nama_pelanggan }}</td>
                    <td class="p-3 text-center font-black text-pc-maroon">{{ number_format($po->jumlah_po) }} Pcs</td>
                    <td class="p-3 text-center">
                        @if($po->status_po === 'PENDING')
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800">PENDING</span>
                        @elseif($po->status_po === 'PROSES')
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-blue-100 text-blue-800">PROSES</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700">SELESAI</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-400">Belum ada pesanan PO masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection