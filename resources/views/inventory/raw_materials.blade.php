@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Pengadaan & Stok Bahan Produksi</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Kelola stok bahan baku, catat nota pembelian multi-item dari supplier, dan rekapitulasi pengadaan.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button onclick="document.getElementById('modal-purchase').classList.remove('hidden')" class="btn btn-primary">
            🛒 + Catat Nota Pembelian Bahan
        </button>

        <button onclick="document.getElementById('modal-material').classList.remove('hidden')" class="btn btn-outline">
            + Master Bahan Baru
        </button>
    </div>
</div>

<!-- 3 Kartu Ringkasan Top-Bar Konsisten (Grid 3 Kolom) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 no-print">
    <!-- Kartu 1: Total Master Bahan -->
    <div class="card-summary">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #d97706;">TOTAL MASTER BAHAN</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-dark);">
            {{ number_format($totalBahanCount ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Jenis</span>
        </h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Terdaftar di Sistem</p>
    </div>

    <!-- Kartu 2: Stok Kritis / Restock -->
    <div class="card-summary" style="{{ ($stokKritisCount ?? 0) > 0 ? 'border-left: 4px solid #ef4444;' : 'border-left: 4px solid #10b981;' }}">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: #dc2626;">STOK KRITIS / RESTOCK</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-dark);">
            {{ number_format($stokKritisCount ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Bahan</span>
        </h3>
        @if(($stokKritisCount ?? 0) > 0)
            <p class="font-black mt-2" style="font-size:0.625rem; color: #dc2626;">⚠️ Butuh Pembelian Ulang</p>
        @else
            <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Semua Stok Aman</p>
        @endif
    </div>

    <!-- Kartu 3: Total Pengadaan Bulan Ini -->
    <div class="card-summary" style="border-left: 4px solid var(--pc-maroon);">
        <div class="flex justify-between items-start">
            <p class="card-title uppercase" style="color: var(--pc-maroon);">BELANJA BAHAN (BULAN INI)</p>
        </div>
        <h3 class="card-value" style="color: var(--pc-maroon);">
            Rp {{ number_format($totalPengadaanBulanIni ?? 0, 0, ',', '.') }}
        </h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">Akumulasi Pembelian Supplier</p>
    </div>
</div>

<!-- TOMBOL NAVIGASI TAB TERHUBUNG DENGAN SCRIPT -->
<div class="tab-navigation-bar no-print">
    <button type="button" onclick="switchTab('stok')" id="tab-btn-stok" class="tab-nav-btn active">
        📦 Stok Bahan Baku
    </button>
    <button type="button" onclick="switchTab('riwayat')" id="tab-btn-riwayat" class="tab-nav-btn">
        📜 Riwayat Transaksi Pembelian
    </button>
</div>

<!-- TAB 1: STOK BAHAN BAKU (DEFAULT ACTIVE) -->
<div id="tab-content-stok">
    <div class="table-container">
        <div class="table-header-bar">
            <span class="font-bold" style="color: var(--pc-dark);">📦 Daftar Master & Stok Bahan Baku</span>
        </div>
        <div class="table-responsive-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID / NAMA BAHAN</th>
                        <th>TIPE BAHAN</th>
                        <th>SATUAN UKURAN</th>
                        <th>STOK AKTUAL</th>
                        <th class="text-center">STATUS KECUKUPAN</th>
                        <th class="text-center no-print">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $mat)
                    <tr>
                        <td>
                            <p class="font-black" style="color: var(--pc-dark); margin:0;">{{ $mat->nama_bahan }}</p>
                            <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0;">ID: BAHAN-00{{ $mat->id_bahan }}</p>
                        </td>
                        <td class="uppercase font-bold" style="color: #4b5563;">{{ $mat->tipe }}</td>
                        <td class="font-bold" style="color: #6b7280;">{{ $mat->ukuran }}</td>
                        <td class="font-black text-sm" style="color: #059669;">
                            {{ number_format($mat->qty_stok) }} {{ $mat->ukuran }}
                        </td>
                        <td class="text-center">
                            @if($mat->qty_stok <= 5)
                                <span class="badge" style="background-color: #fef2f2; color: #dc2626; border-color: #fecaca;">
                                    ⚠️ Stok Kritis
                                </span>
                            @else
                                <span class="badge badge-emerald">
                                    ✓ Stok Aman
                                </span>
                            @endif
                        </td>
                        <td class="text-center no-print">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="editMaterial('{{ $mat->id_bahan }}', '{{ $mat->nama_bahan }}', '{{ $mat->tipe }}', '{{ $mat->ukuran }}', '{{ $mat->qty_stok }}')" 
                                        title="Edit Master Bahan" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                    ✏️ Edit
                                </button>

                                <form action="{{ route('raw-materials.material.destroy', $mat->id_bahan) }}" method="POST" onsubmit="return confirm('Hapus master bahan baku ini?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Master Bahan" class="btn" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.25rem 0.5rem;">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada master bahan baku.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: RIWAYAT TRANSAKSI PEMBELIAN (SEMBUNYI BY DEFAULT DENGAN CLASS HIDDEN) -->
<div id="tab-content-riwayat" class="hidden">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 no-print mb-4" style="background: #fff; padding: 1rem; border-radius: 1rem; border: 1px solid var(--border-color);">
        <form action="{{ route('raw-materials.index') }}" method="GET" class="flex items-center gap-2">
            <span class="font-bold" style="color: #4b5563;">Filter Periode:</span>
            <select name="periode" class="form-input font-bold" style="background:#fff; margin:0; width:auto;">
                <option value="">Semua Riwayat</option>
                <option value="minggu_ini" {{ request('periode') == 'minggu_ini' ? 'selected' : '' }}>Minggu Ini</option>
                <option value="bulan_ini" {{ request('periode') == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
        </form>

        <button onclick="window.print()" class="btn btn-outline">
            🖨️ Cetak Laporan Transaksi Beli
        </button>
    </div>

    <div class="table-container">
        <div class="table-header-bar">
            <span class="font-bold" style="color: var(--pc-dark);">📜 Riwayat Transaksi & Nota Pembelian Bahan</span>
        </div>
        <div class="table-responsive-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>SUPPLIER & TANGGAL</th>
                        <th class="text-center">JUMLAH ITEM</th>
                        <th>TOTAL BELANJA</th>
                        <th class="text-center no-print">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($groupedTransactions) > 0)
                        @foreach($groupedTransactions as $groupKey => $items)
                            @php
                                $firstItem = $items->first();
                                $totalHargaGroup = $items->sum('total_harga');
                                $totalQtyGroup = $items->sum('total_qty');
                                $txIds = $items->pluck('no_transaksi_beli')->toArray();
                            @endphp
                            <tr>
                                <td>
                                    <p class="font-black" style="color: var(--pc-dark); margin:0;">{{ $firstItem->nama_supplier }}</p>
                                    <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0;">{{ \Carbon\Carbon::parse($firstItem->tanggal)->format('d M Y H:i') }}</p>
                                </td>
                                <td class="text-center font-bold" style="color: #4b5563;">
                                    {{ $items->count() }} Jenis Bahan ({{ number_format($totalQtyGroup) }} Qty)
                                </td>
                                <td class="font-black text-sm" style="color: var(--pc-maroon);">
                                    Rp {{ number_format($totalHargaGroup, 0, ',', '.') }}
                                </td>
                                <td class="text-center no-print">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="showDetailGroup('{{ $firstItem->nama_supplier }}', '{{ \Carbon\Carbon::parse($firstItem->tanggal)->format('d M Y H:i') }}', '{{ json_encode($items) }}')" 
                                                class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            👁️ Detail
                                        </button>

                                        <form action="{{ route('raw-materials.purchase.destroy-group') }}" method="POST" onsubmit="return confirm('Hapus seluruh nota pembelian ini? Stok bahan akan disesuaikan.')" style="display:inline;">
                                            @csrf
                                            @foreach($txIds as $id)
                                                <input type="hidden" name="ids[]" value="{{ $id }}">
                                            @endforeach
                                            <button type="submit" title="Hapus Transaksi Nota Ini" class="btn" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.25rem 0.5rem;">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada riwayat transaksi pembelian.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Pop-up Multi-Item Pembelian Bahan -->
<div id="modal-purchase" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 32rem;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Catat Nota Pembelian Bahan Baru (Multi-Item)</h3>
            <button onclick="document.getElementById('modal-purchase').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('raw-materials.purchase.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="font-bold">Nama Supplier / Toko</label>
                    <input type="text" name="nama_supplier" required placeholder="Misal: Agen Condet Utama" class="form-input">
                </div>
                <div>
                    <label class="font-bold">Total Ongkos Kirim (Rp)</label>
                    <input type="number" name="total_ongkir" value="0" min="0" class="form-input">
                </div>
            </div>

            <div class="mt-2" style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 0.75rem; background-color: rgba(249,250,251,0.5);">
                <div class="flex justify-between items-center" style="margin-bottom: 0.5rem;">
                    <span class="font-black" style="color: var(--pc-dark); font-size: 0.75rem;">Daftar Bahan Yang Dibeli:</span>
                    <button type="button" onclick="addRow()" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.5625rem;">+ Tambah Baris Bahan</button>
                </div>

                <div id="row-container" class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 row-item">
                        <select name="bahan_ids[]" required class="form-input font-bold" style="width: 40%; margin:0; background:#fff;">
                            @foreach($materials as $m)
                                <option value="{{ $m->id_bahan }}">{{ $m->nama_bahan }} ({{ $m->ukuran }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="qty[]" step="0.1" required placeholder="Qty" class="form-input" style="width: 25%; margin:0;">
                        <input type="number" name="harga_total[]" required placeholder="Subtotal (Rp)" class="form-input" style="width: 30%; margin:0;">
                        <button type="button" onclick="removeRow(this)" style="color: #ef4444; font-weight: bold; background: none; border: none; cursor: pointer;">✕</button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Nota Pembelian</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Pembelian -->
<div id="modal-detail" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" id="detail-supplier-title" style="margin:0;">Detail Transaksi Pembelian</h3>
            <button onclick="document.getElementById('modal-detail').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <div class="table-responsive-wrapper mt-2">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NAMA BAHAN</th>
                        <th class="text-center">QTY</th>
                        <th class="text-right">TOTAL HARGA</th>
                    </tr>
                </thead>
                <tbody id="detail-tbody">
                    <!-- Injected via Javascript -->
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-4">
            <button type="button" onclick="document.getElementById('modal-detail').classList.add('hidden')" class="btn btn-outline">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Pop-up Master Bahan Baru -->
<div id="modal-material" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Tambah Master Bahan Baku Baru</h3>
            <button onclick="document.getElementById('modal-material').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('raw-materials.material.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <div>
                <label class="font-bold">Nama Bahan Baku</label>
                <input type="text" name="nama_bahan" required placeholder="Misal: Tepung Tapioka Super" class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="font-bold">Tipe Bahan</label>
                    <select name="tipe" class="form-input font-bold" style="background:#fff;">
                        <option value="kering">Kering</option>
                        <option value="basah">Basah</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold">Satuan Ukuran</label>
                    <input type="text" name="ukuran" required placeholder="kg / Liter / pack" class="form-input">
                </div>
            </div>

            <div class="mt-2">
                <label class="font-bold">Stok Awal</label>
                <input type="number" name="qty_stok" value="0" required class="form-input">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-material').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Master Bahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Master Bahan Baku -->
<div id="modal-edit-material" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Edit Master Bahan Baku</h3>
            <button onclick="document.getElementById('modal-edit-material').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-edit-material" method="POST" style="margin-top: 1rem;">
            @csrf
            @method('PUT')
            <div>
                <label class="font-bold">Nama Bahan Baku</label>
                <input type="text" id="edit-nama-bahan" name="nama_bahan" required class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="font-bold">Tipe Bahan</label>
                    <select id="edit-tipe" name="tipe" class="form-input font-bold" style="background:#fff;">
                        <option value="kering">Kering</option>
                        <option value="basah">Basah</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold">Satuan Ukuran</label>
                    <input type="text" id="edit-ukuran" name="ukuran" required class="form-input">
                </div>
            </div>

            <div class="mt-2">
                <label class="font-bold">Stok Aktual</label>
                <input type="number" id="edit-qty-stok" name="qty_stok" required class="form-input">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-edit-material').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Perbarui Master Bahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    const btnStok = document.getElementById('tab-btn-stok');
    const btnRiwayat = document.getElementById('tab-btn-riwayat');
    const contentStok = document.getElementById('tab-content-stok');
    const contentRiwayat = document.getElementById('tab-content-riwayat');

    if (tab === 'stok') {
        contentStok.classList.remove('hidden');
        contentRiwayat.classList.add('hidden');
        
        btnStok.classList.add('active');
        btnRiwayat.classList.remove('active');
    } else {
        contentStok.classList.add('hidden');
        contentRiwayat.classList.remove('hidden');

        btnRiwayat.classList.add('active');
        btnStok.classList.remove('active');
    }
}

function addRow() {
    const container = document.getElementById('row-container');
    const firstRow = container.children[0];
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('input[name="qty[]"]').value = '';
    newRow.querySelector('input[name="harga_total[]"]').value = '';
    container.appendChild(newRow);
}

function removeRow(btn) {
    const container = document.getElementById('row-container');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    }
}

function showDetailGroup(supplier, tgl, itemsJson) {
    document.getElementById('detail-supplier-title').innerText = 'Nota ' + supplier + ' (' + tgl + ')';
    const items = JSON.parse(itemsJson);
    const tbody = document.getElementById('detail-tbody');
    tbody.innerHTML = '';

    items.forEach(i => {
        const namaBahan = i.bahan_produksi ? i.bahan_produksi.nama_bahan : 'Bahan';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="font-bold" style="color: var(--pc-dark);">${namaBahan}</td>
            <td class="text-center font-bold">${i.total_qty}</td>
            <td class="text-right font-black" style="color: var(--pc-maroon);">Rp ${new Intl.NumberFormat('id-ID').format(i.total_harga)}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('modal-detail').classList.remove('hidden');
}

function editMaterial(id, nama, tipe, ukuran, stok) {
    document.getElementById('form-edit-material').action = '/raw-materials/material/' + id;
    document.getElementById('edit-nama-bahan').value = nama;
    document.getElementById('edit-tipe').value = tipe;
    document.getElementById('edit-ukuran').value = ukuran;
    document.getElementById('edit-qty-stok').value = stok;
    document.getElementById('modal-edit-material').classList.remove('hidden');
}

// PERIKSA URL UNTUK OTOMATIS BUKA TAB RIWAYAT JIKA ADA FILTER PERIODE
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('periode')) {
        switchTab('riwayat');
    }
});
</script>

@endsection