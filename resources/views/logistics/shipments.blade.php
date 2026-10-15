@extends('layouts.app')

@section('content')

<!-- Header Modul -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Log Pengiriman & Surat Jalan</h2>
        <p class="text-xs text-gray-400 mt-0.5">Rekapitulasi riwayat pengiriman barang, status ekspedisi, dan penerbitan Surat Jalan ke pelanggan.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span class="text-xs font-bold text-gray-700">Cetak Laporan Pengiriman</span>
        </button>
    </div>
</div>

<!-- 3 Kartu Ringkasan Top-Bar Konsisten -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 no-print">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider">TOTAL SURAT JALAN</p>
            <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg"><i data-lucide="file-check" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($totalSj ?? 0) }} <span class="text-xs font-semibold text-gray-400">Dokumen</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Terkait PO Pelanggan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-purple-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-purple-600 uppercase tracking-wider">PENGIRIMAN DIPROSES</p>
            <span class="p-1.5 bg-purple-50 text-purple-600 rounded-lg"><i data-lucide="truck" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-purple-600 mt-2">{{ number_format($pengirimanProses ?? 0) }} <span class="text-xs font-semibold text-gray-400">Dalam Jalan</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Sedang Diantar Kurir</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">PENGIRIMAN SELESAI</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ number_format($pengirimanSelesai ?? 0) }} <span class="text-xs font-semibold text-gray-400">Selesai</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Diterima & Stok Dipotong</p>
    </div>
</div>

<!-- TABEL 1: LOG STATUS PENGIRIMAN -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">🚚 Monitoring Status Pengiriman Aktif</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">TANGGAL & ACUAN PO</th>
                <th class="p-4">PELANGGAN & PENERIMA</th>
                <th class="p-4">ALAMAT PENGIRIMAN</th>
                <th class="p-4">SURAT JALAN & KURIR</th>
                <th class="p-4 text-center">STATUS PENGIRIMAN</th>
                <th class="p-4 text-center no-print">AKSI STATUS</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($shipments as $ship)
            @php
                $po = $ship->poProduk;
                $sj = $po ? $po->suratJalan : null;
            @endphp
            <tr class="hover:bg-gray-50/50">
                <td class="p-4 font-bold text-pc-dark">
                    <p class="text-xs">{{ \Carbon\Carbon::parse($ship->Tanggal ?? $ship->created_at)->format('d M Y') }}</p>
                    <p class="text-[10px] text-pc-maroon font-black mt-0.5">PO-00{{ $ship->no_po_produk }}</p>
                </td>
                <td class="p-4">
                    <p class="font-black text-pc-dark">{{ $po->nama_pelanggan ?? '-' }}</p>
                    <p class="text-[10px] text-gray-600 font-bold">👤 Penerima: {{ $po->nama_penerima ?? '-' }}</p>
                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-gray-100 text-gray-600 mt-1 inline-block">
                        {{ $po->tipe_jual ?? 'Eceran' }}
                    </span>
                </td>
                <td class="p-4">
                    <div class="flex items-start gap-1">
                        <span class="text-xs">📍</span>
                        <p class="text-[11px] text-gray-600 font-medium leading-relaxed max-w-xs">
                            {{ $po->alamat_pelanggan ?? 'Alamat belum diisi' }}
                        </p>
                    </div>
                </td>
                <td class="p-4">
                    <p class="font-bold text-blue-600">SJ Ref: #{{ $sj->no_ref ?? '-' }}</p>
                    <p class="text-[10px] text-gray-500 font-semibold">🛵 Kurir: {{ $sj->nama_kurir ?? '-' }}</p>
                </td>
                <td class="p-4 text-center">
                    @if($ship->status_pengiriman === 'SELESAI')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                            ✅ SELESAI (DITERIMA)
                        </span>
                    @elseif($ship->status_pengiriman === 'DIKIRIM')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                            🚚 DALAM PERJALANAN
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                            ⏳ DIPROSES / PERSIAPAN
                        </span>
                    @endif
                </td>
                <td class="p-4 text-center no-print">
                    <button onclick="openShipmentStatusModal('{{ $ship->id_log_pengiriman }}', '{{ $ship->status_pengiriman }}')" 
                            class="px-3 py-1.5 bg-pc-cream text-pc-maroon border border-pc-orange/20 rounded-xl text-[10px] font-extrabold hover:bg-amber-100 cursor-pointer">
                        ✏️ Update Status
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-gray-400">Belum ada aktivitas pengiriman.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- TABEL 2: RIWAYAT ARSIP SURAT JALAN -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">📄 Arsip Surat Jalan Diterbitkan</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">NO. SURAT JALAN</th>
                <th class="p-4">TGL TERBIT</th>
                <th class="p-4">PENERIMA</th>
                <th class="p-4">KURIR / PENGIRIM</th>
                <th class="p-4">CATATAN / KETERANGAN</th>
                <th class="p-4 text-center no-print">AKSI</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($suratJalanList as $sj)
            <tr class="hover:bg-gray-50/50">
                <td class="p-4 font-black text-blue-600">
                    #SJ-00{{ $sj->no_ref }}
                </td>
                <td class="p-4 font-bold text-pc-dark">
                    {{ \Carbon\Carbon::parse($sj->tgl_terbit)->format('d M Y') }}
                </td>
                <td class="p-4 font-bold text-pc-dark">
                    {{ $sj->nama_penerima }}
                </td>
                <td class="p-4 font-semibold text-gray-600">
                    {{ $sj->nama_kurir ?? '-' }}
                </td>
                <td class="p-4 text-gray-400 text-[11px]">
                    {{ $sj->ket ?? '-' }}
                </td>
                <td class="p-4 text-center no-print">
                    <button onclick="window.print()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[10px] font-bold cursor-pointer">
                        🖨️ Cetak
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-gray-400">Belum ada arsip Surat Jalan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Pop-up Update Status Pengiriman -->
<div id="modal-update-shipment" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Update Status Pengiriman</h3>
            <button onclick="document.getElementById('modal-update-shipment').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form id="form-shipment-status" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Status Pengiriman Baru</label>
                <select name="status_pengiriman" id="select-shipment-status" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold text-pc-dark">
                    <option value="DIPROSES">DIPROSES (Persiapan Pengiriman)</option>
                    <option value="DIKIRIM">DIKIRIM (Kurir Dalam Perjalanan)</option>
                    <option value="SELESAI">SELESAI (Sudah Diterima Pelanggan)</option>
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Catatan Serah Terima (Opsional)</label>
                <input type="text" name="bukti_serah_terima" placeholder="Misal: Diterima oleh Pak Budi (Security)" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-update-shipment').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Perbarui Status</button>
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