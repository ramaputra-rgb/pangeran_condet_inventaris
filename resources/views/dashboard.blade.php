@extends('layouts.app')

@section('content')

<!-- Header Dashboard -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Dashboard Eksekutif ERP</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Pusat kendali dan pemantauan real-time alur rantai pasok UD Pangeran Condet.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <span class="status-pill">
            <span class="pulse-dot"></span>
            Sistem ERP Aktif Terintegrasi
        </span>
    </div>
</div>

<!-- 4 Kartu Metrics Utama (Grid 4 Kolom di Desktop, 1 Kolom di HP) -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="card-summary" style="border-left: 4px solid var(--pc-maroon);">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: var(--pc-maroon);">PESANAN PO AKTIF</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $poActiveCount ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">PO Active</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: var(--pc-maroon);">⚡ Menunggu & Dalam Proses Dapur</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #10b981;">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #059669;">STOK FISIK READY (FG)</p>
        </div>
        <h3 class="card-value" style="color: #059669;">{{ number_format($totalStokFg ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs/Pack</span></h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">Nilai Aset: Rp {{ number_format($totalNilaiAsetFg ?? 0, 0, ',', '.') }}</p>
    </div>

    <div class="card-summary border-l-amber">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #d97706;">TOTAL QC GRADE A (PUNCH)</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($totalGradeA ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #d97706;">Grade B Remahan: {{ number_format($totalGradeB ?? 0) }} Pack</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #a855f7;">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #9333ea;">ARMADA PENGIRIMAN</p>
        </div>
        <h3 class="card-value" style="color: #9333ea;">{{ $pengirimanProses ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">Dalam Perjalanan</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #9333ea;">🚚 Kurir Sedang Menuju Lokasi</p>
    </div>
</div>

<!-- Ringkasan Alert & Quick Actions -->
@if(($stokKritisCount ?? 0) > 0)
<div class="alert alert-danger mt-4" style="border-radius: 1rem; padding: 1rem;">
    <div class="flex items-center gap-3">
        <span style="font-size: 1.25rem;">⚠️</span>
        <div>
            <h4 class="font-black" style="color: #991b1b; margin:0; font-size: 0.75rem;">Peringatan Stok Bahan Produksi Kritis!</h4>
            <p style="color: #b91c1c; font-size: 0.6875rem; margin: 0.125rem 0 0 0;">Terdapat {{ $stokKritisCount }} bahan baku yang stoknya berada di bawah batas minimum (<= 10 unit).</p>
        </div>
    </div>
    <a href="{{ route('raw-materials.index') }}" class="btn btn-primary" style="font-size: 0.6875rem;">
        Belanja Bahan Baku Now
    </a>
</div>
@endif

<!-- Grid 2 Kolom: Aktivitas Batch Produksi & PO Pelanggan Terbaru -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
    
    <!-- Kolom Kiri: Batch Produksi QC Terbaru -->
    <div class="table-container">
        <div class="table-header-bar flex justify-between items-center">
            <span class="font-bold" style="color: var(--pc-dark);">Batch Produksi Dapur Terbaru</span>
            <a href="{{ route('log-produksi.index') }}" class="font-black" style="font-size: 0.625rem; color: var(--pc-maroon); text-decoration: none;">Lihat Semua →</a>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NO. BATCH</th>
                        <th>PO ACUAN</th>
                        <th class="text-center">GRADE A</th>
                        <th class="text-center">GRADE B</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogProduksi as $log)
                    <tr>
                        <td class="font-bold" style="color: var(--pc-dark);">BATCH-00{{ $log->no_batch }}</td>
                        <td class="font-bold" style="color: #4b5563;">{{ $log->poProduk->nama_pelanggan ?? 'PO #'.$log->no_po_produk }}</td>
                        <td class="text-center font-black" style="color: #059669;">+{{ number_format($log->jml_grade_A) }} Pcs</td>
                        <td class="text-center font-black" style="color: var(--pc-orange);">+{{ number_format($log->jml_grade_B) }} Pack</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada aktivitas produksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kolom Kanan: PO Pelanggan Terbaru -->
    <div class="table-container">
        <div class="table-header-bar flex justify-between items-center">
            <span class="font-bold" style="color: var(--pc-dark);">Pesanan PO Pelanggan Masuk Terbaru</span>
            <a href="{{ route('po-produk.index') }}" class="font-black" style="font-size: 0.625rem; color: var(--pc-maroon); text-decoration: none;">Lihat Semua →</a>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NO. PO</th>
                        <th>PELANGGAN</th>
                        <th class="text-center">QTY PO</th>
                        <th class="text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPoList as $po)
                    <tr>
                        <td class="font-bold" style="color: var(--pc-dark);">PO-00{{ $po->no_po_produk }}</td>
                        <td class="font-black" style="color: var(--pc-dark);">{{ $po->nama_pelanggan }}</td>
                        <td class="text-center font-black" style="color: var(--pc-maroon);">{{ number_format($po->jumlah_po) }} Pcs</td>
                        <td class="text-center">
                            @if($po->status_po === 'PENDING')
                                <span class="badge badge-amber">PENDING</span>
                            @elseif($po->status_po === 'PROSES')
                                <span class="badge badge-blue">PROSES</span>
                            @else
                                <span class="badge badge-emerald">SELESAI</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada pesanan PO masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection