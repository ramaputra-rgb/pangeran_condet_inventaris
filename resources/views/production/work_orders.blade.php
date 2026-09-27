@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black text-pc-dark">Manajemen Work Order & SPK Dapur</h2>
        <p class="text-xs text-gray-400 mt-0.5">Pengendalian penggorengan dapur Condet. Penerbitan SPK otomatis memotong stok bahan baku berdasarkan resep standar (BoM).</p>
    </div>
    <div class="flex items-center space-x-3 no-print">
        <!-- Tombol Utama (+ Terbitkan SPK Baru) -->
        <button onclick="document.getElementById('modal-spk').classList.remove('hidden')" 
                class="bg-pc-maroon text-white font-extrabold text-xs px-5 py-3 rounded-2xl shadow-sm hover:bg-red-800 transition flex items-center gap-2.5 cursor-pointer">
            <span class="p-1 bg-white/20 rounded-full flex items-center justify-center">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-white"></i>
            </span>
            <span>+ Terbitkan SPK Dapur</span>
        </button>

        <!-- Tombol Cetak Antrean SPK -->
        <button onclick="window.print()" 
                class="p-3 bg-white border border-gray-200 rounded-2xl text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition flex items-center gap-2 cursor-pointer shadow-sm">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Status Produksi Dapur -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">SPK DALAM PROSES DAPUR</p>
            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg"><i data-lucide="flame" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $spkProsesCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">SPK Aktif</span></h3>
        <p class="text-[10px] text-amber-600 font-bold mt-2">⚡ Penggorengan Sedang Berjalan</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">TARGET PRODUKSI (BATCH)</p>
            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="layers" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($totalTargetBatch ?? 0) }} <span class="text-xs font-semibold text-gray-400">Pcs</span></h3>
        <p class="text-[10px] text-gray-400 font-medium mt-2">Target Siap Bungkus / Pouch</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">SPK SELESAI (SERAH TERIMA)</p>
            <span class="p-1.5 bg-pc-cream text-pc-maroon rounded-lg"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $spkSelesaiCount ?? 0 }} <span class="text-xs font-semibold text-gray-400">SPK Selesai</span></h3>
        <p class="text-[10px] font-bold text-gray-400 mt-2">✓ Masuk Stok Produk Jadi (FG)</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-extrabold text-pc-orange uppercase tracking-wider">ACUAN FORMULA BOM</p>
            <span class="p-1.5 bg-amber-50 text-pc-orange rounded-lg"><i data-lucide="chef-hat" class="w-4 h-4"></i></span>
        </div>
        <h3 class="text-2xl font-black text-pc-dark mt-2">{{ $products->count() ?? 0 }} <span class="text-xs font-semibold text-gray-400">Resep Standard</span></h3>
        <p class="text-[10px] text-emerald-600 font-bold mt-2">✓ Auto Deduct Bahan Baku</p>
    </div>
</div>

<!-- Tabel Daftar Work Order SPK Dapur -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3 bg-gray-50/50">
        <div class="flex items-center space-x-4 text-xs font-bold text-pc-dark">
            <span class="border-b-2 border-pc-maroon text-pc-maroon pb-1">Daftar SPK Dapur Aktif <span class="bg-red-100 text-pc-maroon text-[9px] px-2 py-0.5 rounded-full ml-1">{{ $workOrders->count() ?? 0 }} SPK</span></span>
        </div>
    </div>

    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-gray-100 text-gray-400 font-extrabold text-[10px] uppercase tracking-wider bg-gray-50/30">
                <th class="p-4">NO. SPK DAPUR</th>
                <th class="p-4">PRODUK YANG DIGORENG</th>
                <th class="p-4 text-center">TARGET BATCH</th>
                <th class="p-4">TANGGAL SPK</th>
                <th class="p-4 text-center">STATUS PRODUKSI</th>
                <th class="p-4 text-center no-print">AKSI / SERAH TERIMA</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-medium">
            @forelse($workOrders as $spk)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="p-4 font-bold text-pc-dark">
                    {{ $spk->no_spk }}
                </td>
                <td class="p-4">
                    <p class="font-bold text-pc-dark">{{ $spk->produk->nama_produk ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">Kode: {{ $spk->produk->kode_produk ?? '-' }}</p>
                </td>
                <td class="p-4 text-center font-black text-pc-maroon text-sm">
                    {{ number_format($spk->target_produksi_pcs) }} Pcs
                </td>
                <td class="p-4 text-gray-500 font-semibold">
                    {{ \Carbon\Carbon::parse($spk->tanggal_spk)->format('d M Y') }}
                </td>
                <td class="p-4 text-center">
                    @if($spk->status_spk === 'PROSES')
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-1 rounded-full border border-amber-200 animate-pulse">
                            🔥 Dalam Penggorengan
                        </span>
                    @elseif($spk->status_spk === 'SELESAI')
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-extrabold px-2.5 py-1 rounded-full border border-emerald-200">
                            ✓ Selesai & Diserahkan
                        </span>
                    @else
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                            Draft
                        </span>
                    @endif
                </td>
                <td class="p-4 text-center no-print">
                    <a href="{{ route('finished-goods.index') }}" class="px-3 py-1.5 bg-pc-cream text-pc-maroon border border-pc-orange/20 rounded-lg text-[10px] font-bold hover:bg-amber-100 inline-flex items-center gap-1 cursor-pointer">
                        <i data-lucide="arrow-right-circle" class="w-3 h-3"></i> Serah Terima FG
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-6 text-center text-gray-400">Belum ada SPK dapur yang diterbitkan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- MODAL POP-UP TERBITKAN SPK DAPUR BARU -->
<div id="modal-spk" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-pc-dark text-sm">Terbitkan SPK Penggorengan Dapur</h3>
            <button onclick="document.getElementById('modal-spk').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form action="{{ route('work-orders.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold text-gray-700 block mb-1">Pilih Produk Varian Rengginang</label>
                <select name="id_produk" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange bg-white">
                    <option value="" disabled selected>-- Pilih Produk --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id_produk ?? $prod->id }}">{{ $prod->nama_produk }} (Kode: {{ $prod->kode_produk }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Target Jumlah Produksi (Pcs)</label>
                <input type="number" name="target_produksi_pcs" required placeholder="Misal: 500" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
                <p class="text-[10px] text-gray-400 mt-1">*Stok beras ketan, minyak, dan pouch otomatis terpotong dari gudang sesuai resep BoM.</p>
            </div>

            <div>
                <label class="font-bold text-gray-700 block mb-1">Tanggal SPK Dapur</label>
                <input type="date" name="tanggal_spk" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-pc-orange">
            </div>

            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-spk').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-gray-600 font-bold hover:bg-gray-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-pc-maroon text-white rounded-xl font-bold hover:bg-red-800 transition shadow-sm cursor-pointer">
                    Terbitkan SPK & Potong Bahan Baku
                </button>
            </div>
        </form>
    </div>
</div>

@endsection