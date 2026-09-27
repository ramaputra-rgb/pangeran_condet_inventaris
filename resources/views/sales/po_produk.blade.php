@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Pesanan PO Pelanggan (Make-to-Order)</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pencatatan Purchase Order dari Toko, Supermarket, & Agen. Menjadi acuan otomatis batch produksi dapur Condet.</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <button onclick="document.getElementById('modal-po').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Buat PO Pelanggan Baru</span>
        </button>

        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span class="text-xs font-bold text-gray-700">Cetak Laporan PO</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">PO PENDING (MENUNGGU)</p>
            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg"><i data-lucide="clock" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $poPendingCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">Pesanan</span></h3>
        <p class="text-[10px] text-amber-600 font-bold mt-2">⏳ Belum Dijadwalkan Dapur</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pc-orange">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">PO DALAM PRODUKSI</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="flame" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $poProsesCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">PO Aktif</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">🔥 Sedang Digoreng Dapur</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-emerald-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">PO SELESAI / TERKIRIM</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ $poSelesaiCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">Selesai</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">✓ Sudah Diterima Pelanggan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-maroon uppercase tracking-wider">TOTAL PESANAN PO</p>
            <span class="p-1.5 bg-red-50 text-pc-maroon rounded-lg"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $totalPo ?? 0 }} <span class="text-xs font-semibold text-gray-400">Total PO</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Rantai Pasok Terintegrasi</p>
    </div>
</div>

<!-- Tabel Daftar PO Produk Pelanggan -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <span class="text-xs font-bold text-pc-dark">Daftar Purchase Order (PO) Pelanggan</span>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">NO. PO</th>
                <th class="p-4">PELANGGAN & ALAMAT</th>
                <th class="p-4">PRODUK YANG DIPESAN</th>
                <th class="p-4 text-center">JUMLAH PO</th>
                <th class="p-4">JATUH TEMPO</th>
                <th class="p-4 text-center">STATUS PO</th>
                <th class="p-4 text-center no-print">AKSI STATUS</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($poList as $po)
            <tr class="hover:bg-gray-50/50">
                <td class="p-4 font-bold text-pc-dark">
                    PO-00{{ $po->no_po_produk }}
                    @if($po->suratJalan)
                        <p class="text-[10px] text-gray-400 font-normal">SJ Ref: #{{ $po->suratJalan->no_ref }}</p>
                    @endif
                </td>
                <td class="p-4">
                    <p class="font-black text-pc-dark">{{ $po->nama_pelanggan }}</p>
                    <p class="text-[10px] text-gray-400 truncate max-w-xs">{{ $po->alamat_pelanggan }}</p>
                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-gray-100 text-gray-600 mt-1 inline-block">
                        {{ $po->tipe_jual }}
                    </span>
                </td>
                <td class="p-4">
                    @forelse($po->detailAlokasi as $alokasi)
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="font-bold text-pc-dark">• {{ $alokasi->produkJadi->nama_produk ?? 'Produk' }}</span>
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-extrabold text-[10px] rounded-full border border-amber-200">
                                Alokasi: {{ number_format($alokasi->jml_alokasi) }} Pcs
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-400 italic text-[10px]">Belum ada alokasi produk</p>
                    @endforelse
                </td>
                <td class="p-4 text-center font-black text-pc-maroon text-sm">
                    {{ number_format($po->jumlah_po) }} Pcs
                </td>
                <td class="p-4 font-bold text-gray-500">
                    {{ \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->format('d M Y') }}
                </td>
                <td class="p-4 text-center">
                    @if($po->status_po === 'PENDING')
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">PENDING</span>
                    @elseif($po->status_po === 'PROSES')
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-blue-100 text-blue-800 border border-blue-200 animate-pulse">PROSES PRODUKSI</span>
                    @elseif($po->status_po === 'DIKIRIM')
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-purple-100 text-purple-800 border border-purple-200">DALAM PENGIRIMAN</span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">SELESAI</span>
                    @endif
                </td>
                <td class="p-4 text-center no-print">
                    <button onclick="openStatusModal('{{ $po->no_po_produk }}', '{{ $po->status_po }}')" 
                            class="px-3 py-1.5 bg-pc-cream text-pc-maroon border border-pc-orange/20 rounded-lg text-[10px] font-bold hover:bg-amber-100 inline-flex items-center gap-1 cursor-pointer">
                        <i data-lucide="edit-3" class="w-3 h-3"></i> Ubah Status
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-6 text-center text-gray-400">Belum ada pesanan PO pelanggan yang tercatat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Pop-up Buat PO Pelanggan Baru -->
<div id="modal-po" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Input Pesanan PO Pelanggan Baru</h3>
            <button onclick="document.getElementById('modal-po').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('po-produk.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Nama Pelanggan / Toko / Supermarket</label>
                <input type="text" name="nama_pelanggan" required placeholder="Misal: HERO Super Market / Toko Jaya" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tipe Penjualan</label>
                    <select name="tipe_jual" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                        <option value="Grosir">Grosir</option>
                        <option value="Eceran">Eceran</option>
                        <option value="Toko Oleh-Oleh">Toko Oleh-Oleh</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Pilih Produk Dipesan</label>
                    <select name="id_produk_jadi" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                        @foreach($products as $prod)
                            <option value="{{ $prod->id_produk_jadi }}">{{ $prod->nama_produk }} (Stok: {{ $prod->stok }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Jumlah Pesanan (Pcs)</label>
                    <input type="number" name="jumlah_po" required placeholder="100" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tgl Jatuh Tempo PO</label>
                    <input type="date" name="tgl_jatuh_tempo_po" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                </div>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Alamat Pengiriman</label>
                <textarea name="alamat_pelanggan" required rows="2" placeholder="Jl. Raya Condet No. XX, Jakarta" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange"></textarea>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Tautkan Surat Jalan (Opsional)</label>
                <select name="no_ref" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    <option value="">-- Belum Diterbitkan Surat Jalan --</option>
                    @foreach($suratJalan as $sj)
                        <option value="{{ $sj->no_ref }}">Surat Jalan #{{ $sj->no_ref }} - {{ $sj->nama_penerima }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-po').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Simpan PO Pelanggan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Update Status PO -->
<div id="modal-status" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Perbarui Status PO</h3>
            <button onclick="document.getElementById('modal-status').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form id="form-status" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Status Baru</label>
                <select name="status_po" id="select-status-po" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white font-bold text-pc-dark">
                    <option value="PENDING">PENDING (Menunggu)</option>
                    <option value="PROSES">PROSES (Dalam Penggorengan Dapur)</option>
                    <option value="DIKIRIM">DIKIRIM (Dalam Perjalanan)</option>
                    <option value="SELESAI">SELESAI (Diterima Pelanggan)</option>
                </select>
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-status').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(id, currentStatus) {
    document.getElementById('select-status-po').value = currentStatus;
    document.getElementById('form-status').action = '/po-produk/update-status/' + id;
    document.getElementById('modal-status').classList.remove('hidden');
}
</script>

@endsection