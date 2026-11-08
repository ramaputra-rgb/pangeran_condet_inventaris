@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Stok Produk Jadi (Finished Goods)</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Monitoring ketersediaan varian Rengginang & Emping siap jual. Otomatis ter-update dari hasil QC Dapur dan teralokasi untuk PO Pelanggan.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button onclick="document.getElementById('modal-product').classList.remove('hidden')" class="btn btn-primary">
            + Tambah Varian Produk
        </button>

        <button onclick="window.print()" class="btn btn-outline">
            🖨️ Cetak Laporan Stok
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="card-summary">
        <p class="card-title uppercase" style="color: #059669;">TOTAL STOK FISIK FG</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ number_format($totalStokFisik ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs/Pack</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Produk Siap Edar Dapur Condet</p>
    </div>

    <div class="card-summary border-l-amber">
        <p class="card-title uppercase" style="color: #d97706;">ALOKASI TERKUNCI PO</p>
        @php
            $totalAlokasiAktif = $products->sum('stok_terkunci_po');
        @endphp
        <h3 class="card-value" style="color: #d97706;">{{ number_format($totalAlokasiAktif) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs</span></h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">Direservasi untuk Pesanan Pelanggan Aktif</p>
    </div>

    <div class="card-summary">
        <p class="card-title uppercase" style="color: var(--pc-orange);">ESTIMASI NILAI ASET</p>
        <h3 class="card-value" style="color: var(--pc-dark);">Rp {{ number_format($totalNilaiAset ?? 0, 0, ',', '.') }}</h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">Berdasarkan Harga Jual Produk</p>
    </div>

    <div class="card-summary">
        <p class="card-title uppercase" style="color: var(--pc-maroon);">VARIAN KATALOG</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $totalVarian ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">SKU Varian</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Termasuk Varian Remahan</p>
    </div>
</div>

<!-- Tabel Stok Produk Jadi dengan Responsive Wrapper -->
<div class="table-container mt-6">
    <div class="table-header-bar">
        <span class="font-bold" style="color: var(--pc-dark);">Katalog Produk Jadi & Ketersediaan Stok</span>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID / SKU</th>
                    <th>NAMA PRODUK & VARIAN</th>
                    <th>NETTO</th>
                    <th>HARGA JUAL</th>
                    <th>STOK AKTUAL</th>
                    <th class="text-center">STATUS KATEGORI</th>
                    <th class="text-center no-print">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $prod)
                @php
                    $stokTerkunci = $prod->stok_terkunci_po;
                    $stokBebasJual = $prod->stok_atp;
                @endphp
                <tr>
                    <td class="font-bold">FG-00{{ $prod->id_produk_jadi }}</td>
                    <td>
                        <p class="font-black" style="font-size: 0.875rem; margin:0;">{{ $prod->nama_produk }}</p>
                        <p style="color: var(--text-muted); font-size: 0.625rem; margin: 0.125rem 0 0 0;">Varian: {{ $prod->varian }}</p>
                    </td>
                    <td style="color: #6b7280;">{{ $prod->netto }}</td>
                    <td class="font-bold">Rp {{ number_format($prod->harga_jual ?? 0, 0, ',', '.') }}</td>
                    <td>
                        <p class="font-black" style="color: #059669; font-size: 0.875rem; margin:0;">
                            {{ number_format($prod->stok) }} <span style="font-size: 0.625rem; color: var(--text-muted); font-weight: normal;">Pcs (Fisik)</span>
                        </p>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="badge badge-amber">🔒 Terkunci PO: {{ number_format($stokTerkunci) }}</span>
                            <span class="badge badge-blue">✅ ATP: {{ number_format($stokBebasJual) }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        @if(str_contains(strtolower($prod->nama_produk), 'remahan') || str_contains(strtolower($prod->varian), 'remahan'))
                            <span class="badge badge-amber">Grade B (Remahan)</span>
                        @else
                            <span class="badge badge-emerald">Grade A (Utama)</span>
                        @endif
                    </td>
                    <td class="text-center no-print">
                        <button onclick="openAdjustModal('{{ $prod->id_produk_jadi }}', '{{ $prod->nama_produk }}')" class="btn btn-outline">
                            ⚙️ Adjust Stok
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada data produk jadi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up Tambah Master Produk Jadi Baru -->
<div id="modal-product" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Tambah Master Produk Jadi Baru</h3>
            <button onclick="document.getElementById('modal-product').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('finished-goods.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <div>
                <label class="font-bold">Nama Produk</label>
                <input type="text" name="nama_produk" required placeholder="Misal: Rengginang Udang 100g" class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="font-bold">Varian Rasa / Jenis</label>
                    <input type="text" name="varian" required placeholder="Misal: Udang 100g" class="form-input">
                </div>
                <div>
                    <label class="font-bold">Netto / Berat Gram</label>
                    <input type="text" name="netto" required placeholder="100g" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="font-bold">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" required placeholder="12500" class="form-input">
                </div>
                <div>
                    <label class="font-bold">Stok Awal Fisik</label>
                    <input type="number" name="stok" value="0" required class="form-input">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-product').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Master Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Manual Adjust Stok -->
<div id="modal-adjust" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Penyesuaian (Adjust) Stok</h3>
            <button onclick="document.getElementById('modal-adjust').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-adjust" method="POST" style="margin-top: 1rem;">
            @csrf
            <p id="adjust-product-name" class="font-black" style="color: var(--pc-maroon); margin:0;"></p>
            
            <div class="mt-2">
                <label class="font-bold">Jumlah Penyesuaian Stok</label>
                <input type="number" name="stok_tambahan" required placeholder="Gunakan nilai negatif (-) jika pengurangan" class="form-input">
                <p style="font-size: 0.625rem; color: var(--text-muted); margin-top: 0.25rem;">*Contoh: masukan +50 untuk penambahan, atau -10 untuk opnam/retur.</p>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-adjust').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Update Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdjustModal(id, name) {
    document.getElementById('adjust-product-name').innerText = name;
    document.getElementById('form-adjust').action = '/finished-goods/update-stock/' + id;
    document.getElementById('modal-adjust').classList.remove('hidden');
}
</script>

@endsection