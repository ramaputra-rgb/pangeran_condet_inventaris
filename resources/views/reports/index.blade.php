@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Pusat Laporan & Audit Cetak</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Filter dan cetak laporan operasional pabrik (Stok, Pengadaan, Produksi, dan Penjualan) lengkap dengan riwayat log cetak.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button onclick="document.getElementById('modal-filter-laporan').classList.remove('hidden')" class="btn btn-primary">
            📊 + Filter & Cetak Laporan Baru
        </button>
    </div>
</div>

<!-- Kartu Ringkasan Top-Bar Konsisten -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 no-print">
    <div class="card-summary" style="border-left: 4px solid var(--pc-maroon);">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: var(--pc-maroon);">TOTAL AUDIT CETAK</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-dark);">
            {{ number_format($logCetakList->count() ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Dokumen</span>
        </h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Tercatat di Sistem</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #2563eb;">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #2563eb;">STATUS AUDIT TRAIL</p>
        </div>
        <h3 class="card-value" style="color: #2563eb;">AKTIF</h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">Perekaman Riwayat Cetak Real-Time</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #10b981;">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #059669;">STANDAR OPERASIONAL</p>
        </div>
        <h3 class="card-value" style="color: #059669;">VALID</h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Sesuai Activity Diagram</p>
    </div>
</div>

<!-- TABEL RIWAYAT LOG CETAK LAPORAN -->
<div class="table-container mt-6">
    <div class="table-header-bar">
        <span class="font-bold" style="color: var(--pc-dark);">📜 Log Riwayat Pencetakan Laporan (Audit Trail)</span>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID LOG & WAKTU CETAK</th>
                    <th>JENIS LAPORAN</th>
                    <th>KRITERIA / FILTERING</th>
                    <th class="text-center">STATUS</th>
                    <th class="text-center no-print">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logCetakList as $log)
                <tr>
                    <td class="font-bold">
                        LOG-00{{ $log->id_log_cetak }}
                        <p style="font-size: 0.625rem; color: var(--text-muted); font-weight: normal; margin: 0.125rem 0 0 0;">
                            📅 {{ \Carbon\Carbon::parse($log->tgl_cetak)->format('d M Y H:i') }}
                        </p>
                    </td>
                    <td class="uppercase font-black" style="color: var(--pc-dark);">
                        {{ $log->jenis_laporan }}
                    </td>
                    <td style="color: #4b5563; font-size: 0.75rem;">
                        {{ $log->kriteria_filter }}
                    </td>
                    <td class="text-center">
                        <span class="badge badge-emerald">
                            ✓ {{ $log->status_cetak }}
                        </span>
                    </td>
                    <td class="text-center no-print">
                        <button onclick="window.print()" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                            🖨️ Cetak Ulang
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada riwayat log cetak laporan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up Filter & Cetak Laporan (Sesuai Activity Diagram Hal. 11) -->
<div id="modal-filter-laporan" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 30rem;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Filter & Pratinjau Cetak Laporan</h3>
            <button onclick="document.getElementById('modal-filter-laporan').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('reports.preview') }}" method="POST" target="_blank" style="margin-top: 1rem;">
            @csrf
            <div>
                <label class="font-bold">Pilih Jenis Sub-Laporan</label>
                <select name="jenis_laporan" required class="form-input font-bold" style="background:#fff; color: var(--pc-dark);">
                    <option value="stok">Laporan Stok & Varian Produk Jadi</option>
                    <option value="pengadaan">Laporan Pengadaan / Pembelian Bahan</option>
                    <option value="produksi">Laporan Log Produksi & QC Dapur</option>
                    <option value="penjualan">Laporan PO & Penjualan Pelanggan</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="font-bold">Periode Mulai</label>
                    <input type="date" name="periode_mulai" class="form-input">
                </div>
                <div>
                    <label class="font-bold">Periode Selesai</label>
                    <input type="date" name="periode_selesai" class="form-input">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-filter-laporan').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">🖨️ Proses & Cetak Laporan</button>
            </div>
        </form>
    </div>
</div>

@endsection