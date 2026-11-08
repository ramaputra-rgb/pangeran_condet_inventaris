@extends('layouts.app')

@section('content')

<!-- Header Modul -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Log Pengiriman & Surat Jalan</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Rekapitulasi riwayat pengiriman barang, status ekspedisi, dan penerbitan Surat Jalan ke pelanggan.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button onclick="window.print()" class="btn btn-outline">
            🖨️ Cetak Laporan Pengiriman
        </button>
    </div>
</div>

<!-- 3 Kartu Ringkasan Top-Bar Konsisten (Grid 3 Kolom) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 no-print">
    <div class="card-summary" style="border-left: 4px solid #2563eb;">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #2563eb;">TOTAL SURAT JALAN</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($totalSj ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Dokumen</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Terkait PO Pelanggan</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #a855f7;">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #9333ea;">PENGIRIMAN DIPROSES</p>
        </div>
        <h3 class="card-value" style="color: #9333ea;">{{ number_format($pengirimanProses ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Dalam Jalan</span></h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">Sedang Diantar Kurir</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #10b981;">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #059669;">PENGIRIMAN SELESAI</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($pengirimanSelesai ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Selesai</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Diterima & Stok Dipotong</p>
    </div>
</div>

<!-- TABEL 1: LOG STATUS PENGIRIMAN -->
<div class="table-container mt-6">
    <div class="table-header-bar">
        <span class="font-bold" style="color: var(--pc-dark);">🚚 Monitoring Status Pengiriman Aktif</span>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>TANGGAL & ACUAN PO</th>
                    <th>PELANGGAN & PENERIMA</th>
                    <th>ALAMAT PENGIRIMAN</th>
                    <th>SURAT JALAN & KURIR</th>
                    <th class="text-center">STATUS PENGIRIMAN</th>
                    <th class="text-center no-print">AKSI STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $ship)
                @php
                    $po = $ship->poProduk;
                    $sj = $po ? $po->suratJalan : null;
                @endphp
                <tr>
                    <td class="font-bold">
                        <p style="margin:0; font-size: 0.75rem;">{{ \Carbon\Carbon::parse($ship->Tanggal ?? $ship->created_at)->format('d M Y') }}</p>
                        <p class="font-black" style="color: var(--pc-maroon); font-size: 0.625rem; margin: 0.125rem 0 0 0;">PO-00{{ $ship->no_po_produk }}</p>
                    </td>
                    <td>
                        <p class="font-black" style="color: var(--pc-dark); margin:0;">{{ $po->nama_pelanggan ?? '-' }}</p>
                        <p class="font-bold" style="font-size: 0.625rem; color: #4b5563; margin: 0.125rem 0 0 0;">👤 Penerima: {{ $po->nama_penerima ?? '-' }}</p>
                        <span class="badge" style="background-color:#f3f4f6; color:#4b5563; margin-top: 0.25rem;">
                            {{ $po->tipe_jual ?? 'Eceran' }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-start gap-1">
                            <span style="font-size: 0.75rem;">📍</span>
                            <p style="font-size: 0.6875rem; color: #4b5563; line-height: 1.4; max-width: 16rem; margin:0;">
                                {{ $po->alamat_pelanggan ?? 'Alamat belum diisi' }}
                            </p>
                        </div>
                    </td>
                    <td>
                        <p class="font-bold" style="color: #2563eb; margin:0;">SJ Ref: #{{ $sj->no_ref ?? '-' }}</p>
                        <p style="font-size: 0.625rem; color: var(--text-muted); font-weight: 600; margin: 0.125rem 0 0 0;">🛵 Kurir: {{ $sj->nama_kurir ?? '-' }}</p>
                    </td>
                    <td class="text-center">
                        @if($ship->status_pengiriman === 'SELESAI')
                            <span class="badge badge-emerald">
                                ✅ SELESAI (DITERIMA)
                            </span>
                        @elseif($ship->status_pengiriman === 'DIKIRIM')
                            <span class="badge badge-purple">
                                🚚 DALAM PERJALANAN
                            </span>
                        @else
                            <span class="badge badge-amber">
                                ⏳ DIPROSES / PERSIAPAN
                            </span>
                        @endif
                    </td>
                    <td class="text-center no-print">
                        <button onclick="openShipmentStatusModal('{{ $ship->id_log_pengiriman }}', '{{ $ship->status_pengiriman }}')" class="btn btn-outline">
                            ✏️ Update Status
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada aktivitas pengiriman.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- TABEL 2: RIWAYAT ARSIP SURAT JALAN -->
<div class="table-container mt-6">
    <div class="table-header-bar">
        <span class="font-bold" style="color: var(--pc-dark);">📄 Arsip Surat Jalan Diterbitkan</span>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>NO. SURAT JALAN</th>
                    <th>TGL TERBIT</th>
                    <th>PENERIMA</th>
                    <th>KURIR / PENGIRIM</th>
                    <th>CATATAN / KETERANGAN</th>
                    <th class="text-center no-print">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratJalanList as $sj)
                <tr>
                    <td class="font-black" style="color: #2563eb;">
                        #SJ-00{{ $sj->no_ref }}
                    </td>
                    <td class="font-bold" style="color: var(--pc-dark);">
                        {{ \Carbon\Carbon::parse($sj->tgl_terbit)->format('d M Y') }}
                    </td>
                    <td class="font-bold" style="color: var(--pc-dark);">
                        {{ $sj->nama_penerima }}
                    </td>
                    <td class="font-bold" style="color: #4b5563;">
                        {{ $sj->nama_kurir ?? '-' }}
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.6875rem;">
                        {{ $sj->ket ?? '-' }}
                    </td>
                    <td class="text-center no-print">
                        <button onclick="window.print()" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                            🖨️ Cetak
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada arsip Surat Jalan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up Update Status Pengiriman -->
<div id="modal-update-shipment" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Update Status Pengiriman</h3>
            <button onclick="document.getElementById('modal-update-shipment').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-shipment-status" method="POST" style="margin-top: 1rem;">
            @csrf
            <div>
                <label class="font-bold">Status Pengiriman Baru</label>
                <select name="status_pengiriman" id="select-shipment-status" required class="form-input font-bold" style="background:#fff; color: var(--pc-dark);">
                    <option value="DIPROSES">DIPROSES (Persiapan Pengiriman)</option>
                    <option value="DIKIRIM">DIKIRIM (Kurir Dalam Perjalanan)</option>
                    <option value="SELESAI">SELESAI (Sudah Diterima Pelanggan)</option>
                </select>
            </div>

            <div class="mt-2">
                <label class="font-bold">Catatan Serah Terima (Opsional)</label>
                <input type="text" name="bukti_serah_terima" placeholder="Misal: Diterima oleh Pak Budi (Security)" class="form-input">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-update-shipment').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Perbarui Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openShipmentStatusModal(id, status) {
    document.getElementById('select-shipment-status').value = status;
    document.getElementById('form-shipment-status').action = '/shipments/update-status/' + id;
    document.getElementById('modal-update-shipment').classList.remove('hidden');
}
</script>

@endsection