@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Surat Jalan & Logistik Pengiriman</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pengendalian dokumen jalan & pelacakan ekspedisi/kurir terintegrasi. Konfirmasi selesai otomatis menyelesaikan status PO.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-sj').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Terbitkan Surat Jalan</span>
        </button>

        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span class="text-xs font-bold text-gray-700">Cetak Manifes Jalan</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">TOTAL SURAT JALAN</p>
            <span class="p-1.5 bg-pc-cream text-pc-maroon rounded-lg"><i data-lucide="scroll-text" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $totalSj ?? 0 }} <span class="text-xs font-semibold text-gray-400">Dokumen</span></h3>
        <p class="text-[10px] font-bold text-gray-400 mt-2">Diterbitkan oleh Dapur Condet</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-purple-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-purple-600 uppercase tracking-wider">DALAM PERJALANAN (EXPEDITION)</p>
            <span class="p-1.5 bg-purple-50 text-purple-600 rounded-lg"><i data-lucide="truck" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-purple-700 mt-2">{{ $pengirimanProses ?? 0 }} <span class="text-xs font-semibold text-gray-400">Armada</span></h3>
        <p class="text-[10px] text-purple-600 font-bold mt-2">🚚 Kurir Sedang Menuju Lokasi</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-emerald-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">PENGIRIMAN SUKSES (DONE)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-check" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ $pengirimanSelesai ?? 0 }} <span class="text-xs font-semibold text-gray-400">Selesai</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">✓ Serah Terima & BAST Lengkap</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">PO READY DIKIRIM</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="package-check" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $poActive->count() ?? 0 }} <span class="text-xs font-semibold text-gray-400">PO Siap Kirim</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Alokasi Siap Muat</p>
    </div>
</div>

<!-- Grid 2 Kolom: Tabel Surat Jalan & Tabel Tracking Log Pengiriman -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    
    <!-- Kolom Kiri: Tabel Surat Jalan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <span class="text-xs font-bold text-pc-dark">Daftar Surat Jalan Terbit</span>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                    <th class="p-3">NO. REF / TGL</th>
                    <th class="p-3">PENERIMA</th>
                    <th class="p-3">KURIR / DRIVER</th>
                    <th class="p-3">KETERANGAN</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium">
                @forelse($suratJalanList as $sj)
                <tr class="hover:bg-gray-50/50">
                    <td class="p-3">
                        <p class="font-bold text-pc-dark">SJ-00{{ $sj->no_ref }}</p>
                        <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($sj->tgl_terbit)->format('d M Y') }}</p>
                    </td>
                    <td class="p-3 font-black text-pc-dark">{{ $sj->nama_penerima }}</td>
                    <td class="p-3 text-gray-600 font-semibold">🚚 {{ $sj->nama_kurir }}</td>
                    <td class="p-3 text-gray-400 text-[11px]">{{ $sj->ket ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-400">Belum ada surat jalan terbit.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Kolom Kanan: Tabel Tracking Log Pengiriman -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <span class="text-xs font-bold text-pc-dark">Log Pelacakan Pengiriman Ekspedisi</span>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                    <th class="p-3">PO & TANGGAL</th>
                    <th class="p-3">BUKTI RESI / FOTO</th>
                    <th class="p-3 text-center">STATUS EKSPEDISI</th>
                    <th class="p-3 text-center no-print">UPDATE</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium">
                @forelse($shipments as $ship)
                <tr class="hover:bg-gray-50/50">
                    <td class="p-3">
                        <p class="font-bold text-pc-dark">PO #{{ $ship->no_po_produk }}</p>
                        <p class="text-[10px] text-gray-400">{{ $ship->poProduk->nama_pelanggan ?? 'Pelanggan' }}</p>
                    </td>
                    <td class="p-3">
                        @if($ship->bukti_serah_terima)
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[10px] inline-flex items-center gap-1">
                                <i data-lucide="file-check" class="w-3 h-3"></i> {{ $ship->bukti_serah_terima }}
                            </span>
                        @else
                            <span class="text-gray-400 italic text-[10px]">Belum diunggah</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        @if($ship->status_pengiriman === 'DIPROSES')
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800">DIPROSES</span>
                        @elseif($ship->status_pengiriman === 'DIKIRIM')
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-purple-100 text-purple-800 animate-pulse">DIKIRIM</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700">SELESAI</span>
                        @endif
                    </td>
                    <td class="p-3 text-center no-print">
                        <button onclick="openShipmentModal('{{ $ship->id_log_pengiriman }}', '{{ $ship->status_pengiriman }}', '{{ $ship->bukti_serah_terima }}')" 
                                class="px-2.5 py-1 bg-pc-cream text-pc-maroon border border-pc-orange/20 rounded-lg text-[10px] font-bold hover:bg-amber-100 inline-flex items-center gap-1 cursor-pointer">
                            <i data-lucide="truck" class="w-3 h-3"></i> Status
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-400">Belum ada log pengiriman aktif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<!-- Modal Pop-up Terbit Surat Jalan Baru -->
<div id="modal-sj" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Terbitkan Surat Jalan Baru</h3>
            <button onclick="document.getElementById('modal-sj').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('shipments.surat-jalan.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Tautkan ke PO Pelanggan</label>
                <select name="no_po_produk" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    <option value="">-- Hanya Surat Jalan Polosan --</option>
                    @foreach($poActive as $po)
                        <option value="{{ $po->no_po_produk }}">PO #{{ $po->no_po_produk }} - {{ $po->nama_pelanggan }} ({{ $po->jumlah_po }} Pcs)</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Nama Penerima / SPV</label>
                    <input type="text" name="nama_penerima" required placeholder="Misal: Yanto" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Nama Kurir / Driver</label>
                    <input type="text" name="nama_kurir" required placeholder="Misal: Iman" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Tanggal Terbit</label>
                <input type="date" name="tgl_terbit" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Keterangan / Instruksi Pengiriman</label>
                <textarea name="ket" rows="2" placeholder="Serahkan Ke SPV / Hati-hati barang pecah belah" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange"></textarea>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-sj').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Terbitkan & Mulai Pengiriman</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Update Log Pengiriman -->
<div id="modal-shipment" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Update Status & Bukti Pengiriman</h3>
            <button onclick="document.getElementById('modal-shipment').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form id="form-shipment" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Status Pengiriman</label>
                <select name="status_pengiriman" id="select-status-pengiriman" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold text-pc-dark">
                    <option value="DIPROSES">DIPROSES (Persiapan Muat)</option>
                    <option value="DIKIRIM">DIKIRIM (Dalam Perjalanan Kurir)</option>
                    <option value="SELESAI">SELESAI (Sampai & Diserahkan)</option>
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama File Bukti BAST / Foto (.jpg/.png)</label>
                <input type="text" name="bukti_serah_terima" id="input-bukti" placeholder="Misal: serah_terima_001.jpeg" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-shipment').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Update Log</button>
            </div>
        </form>
    </div>
</div>

<script>
function openShipmentModal(id, currentStatus, currentBukti) {
    document.getElementById('select-status-pengiriman').value = currentStatus;
    document.getElementById('input-bukti').value = currentBukti !== 'null' ? currentBukti : '';
    document.getElementById('form-shipment').action = '/shipments/update-status/' + id;
    document.getElementById('modal-shipment').classList.remove('hidden');
}
</script>

@endsection