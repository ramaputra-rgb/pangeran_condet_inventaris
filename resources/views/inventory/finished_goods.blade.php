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
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 no-print">
    <div class="card-summary">
        <p class="card-title uppercase" style="color: #059669;">TOTAL STOK FISIK FG</p>
        <h3 class="card-value" style="color: {{ ($totalStokFisik ?? 0) < 0 ? '#dc2626' : 'var(--pc-dark)' }};">{{ number_format($totalStokFisik ?? 0) }} <span style="font-size:0.75rem; color: var(--text-muted);">Pcs/Pack</span></h3>
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
        <h3 class="card-value" style="color: {{ ($totalNilaiAset ?? 0) < 0 ? '#dc2626' : 'var(--pc-dark)' }};">Rp {{ number_format($totalNilaiAset ?? 0, 0, ',', '.') }}</h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">Berdasarkan Harga Jual Produk</p>
    </div>

    <div class="card-summary">
        <p class="card-title uppercase" style="color: var(--pc-maroon);">VARIAN KATALOG</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $totalVarian ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">SKU Varian</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Termasuk Varian Remahan</p>
    </div>
</div>

<!-- TOMBOL NAVIGASI TAB (KONSISTEN DENGAN MODUL STOK BAHAN BAKU) -->
<div class="tab-navigation-bar no-print">
    <button type="button" onclick="switchTab('stok')" id="tab-btn-stok" class="tab-nav-btn active">
        📦 Katalog Stok Produk Jadi
    </button>
    <button type="button" onclick="switchTab('riwayat')" id="tab-btn-riwayat" class="tab-nav-btn">
        📜 Riwayat Audit Log ({{ count($auditLogs ?? []) }})
    </button>
</div>

<!-- TAB 1: KATALOG PRODUK JADI & KETERSEDIAAN STOK (DEFAULT ACTIVE) -->
<div id="tab-content-stok">
    <div class="table-container">
        <div class="table-header-bar">
            <span class="font-bold" style="color: var(--pc-dark);">📦 Katalog Produk Jadi & Ketersediaan Stok</span>
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
                            <p class="font-black" style="color: {{ $prod->stok <= 0 ? '#dc2626' : '#059669' }}; font-size: 0.875rem; margin:0;">
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
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="openEditProductModal('{{ $prod->id_produk_jadi }}', '{{ addslashes($prod->nama_produk) }}', '{{ addslashes($prod->varian) }}', '{{ addslashes($prod->netto) }}', '{{ $prod->harga_jual }}')" class="btn btn-outline" style="padding: 0.25rem 0.5rem;" title="Edit Detail Produk">
                                    ✏️ Edit
                                </button>
                                <button onclick="openAdjustModal('{{ $prod->id_produk_jadi }}', '{{ addslashes($prod->nama_produk) }}', {{ (int) $prod->stok }}, {{ (int) $stokTerkunci }}, {{ (int) $stokBebasJual }})" class="btn btn-outline" style="padding: 0.25rem 0.5rem;" title="Penyesuaian Stok">
                                    ⚙️ Adjust Stok
                                </button>
                                <button onclick="openDeleteProductModal('{{ $prod->id_produk_jadi }}', '{{ addslashes($prod->nama_produk) }}', {{ (int) $prod->stok }})" class="btn" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.25rem 0.5rem;" title="Hapus Produk">
                                    🗑️ Hapus
                                </button>
                            </div>
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
</div>

<!-- TAB 2: RIWAYAT AUDIT LOG AKTIVITAS (SEMBUNYI BY DEFAULT DENGAN CLASS HIDDEN) -->
<div id="tab-content-riwayat" class="hidden">
    <div class="table-container">
        <div class="table-header-bar flex justify-between items-center">
            <span class="font-bold" style="color: var(--pc-dark);">📜 Riwayat Audit Log (Tambah, Edit, Hapus Barang, & Penyesuaian Stok)</span>
            <span style="font-size: 0.6875rem; color: var(--text-muted);">Tercatat Otomatis per Akun & Role</span>
        </div>

        <div class="table-responsive-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>WAKTU (DATETIME)</th>
                        <th>AKTOR & ROLE</th>
                        <th class="text-center">AKSI</th>
                        <th>PRODUK</th>
                        <th>DETAIL PERUBAHAN</th>
                        <th>KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                    <tr>
                        <td class="font-bold" style="font-size: 0.75rem; white-space: nowrap;">
                            🕒 {{ $log['datetime'] ?? '-' }}
                        </td>
                        <td>
                            <p class="font-black" style="margin:0; font-size: 0.75rem; color: var(--pc-dark);">{{ $log['user_name'] ?? 'System' }}</p>
                            <span class="badge" style="background-color: #f3f4f6; color: var(--pc-maroon); font-size: 0.5625rem; margin-top: 0.125rem;">
                                👤 {{ $log['role'] ?? 'STAFF' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if(($log['aksi'] ?? '') === 'ADD BARANG')
                                <span class="badge badge-emerald">+ ADD BARANG</span>
                            @elseif(($log['aksi'] ?? '') === 'EDIT BARANG')
                                <span class="badge badge-blue">✏️ EDIT BARANG</span>
                            @elseif(($log['aksi'] ?? '') === 'DELETE BARANG')
                                <span class="badge" style="background-color: #fee2e2; color: #dc2626;">🗑️ DELETE BARANG</span>
                            @else
                                <span class="badge badge-amber">⚙️ ADJUST STOK</span>
                            @endif
                        </td>
                        <td class="font-bold" style="color: var(--pc-dark);">{{ $log['produk'] ?? '-' }}</td>
                        <td style="font-size: 0.75rem; color: #374151;">{{ $log['detail'] ?? '-' }}</td>
                        <td style="font-size: 0.6875rem; color: var(--text-muted); font-style: italic;">{{ $log['keterangan'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada riwayat perubahan data produk atau stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
                    <input type="number" name="harga_jual" required min="0" placeholder="12500" class="form-input">
                </div>
                <div>
                    <label class="font-bold">Stok Awal Fisik</label>
                    <input type="number" name="stok" value="0" min="0" required class="form-input">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-product').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Master Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Edit Master Produk Jadi -->
<div id="modal-edit-product" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">✏️ Edit Detail Produk Jadi</h3>
            <button onclick="document.getElementById('modal-edit-product').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-edit-product" method="POST" style="margin-top: 1rem;">
            @csrf
            @method('PUT')
            <div>
                <label class="font-bold">Nama Produk</label>
                <input type="text" id="edit-nama-produk" name="nama_produk" required class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                    <label class="font-bold">Varian Rasa / Jenis</label>
                    <input type="text" id="edit-varian" name="varian" required class="form-input">
                </div>
                <div>
                    <label class="font-bold">Netto / Berat Gram</label>
                    <input type="text" id="edit-netto" name="netto" required class="form-input">
                </div>
            </div>

            <div class="mt-2">
                <label class="font-bold">Harga Jual (Rp)</label>
                <input type="number" id="edit-harga-jual" name="harga_jual" required min="0" class="form-input">
            </div>

            <div class="mt-2">
                <label class="font-bold">Alasan Perubahan (Untuk Log Audit)</label>
                <input type="text" name="keterangan" placeholder="Misal: Penyesuaian harga jual baru" class="form-input">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-edit-product').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Manual Adjust Stok dengan Guard Anti-Minus -->
<div id="modal-adjust" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Penyesuaian (Adjust) Stok</h3>
            <button onclick="document.getElementById('modal-adjust').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-adjust" method="POST" onsubmit="return validateAdjustStock()" style="margin-top: 1rem;">
            @csrf
            <p id="adjust-product-name" class="font-black" style="color: var(--pc-maroon); margin:0;"></p>

            <!-- Info Ketersediaan & Batas Pengurangan -->
            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 0.625rem; padding: 0.625rem; margin-top: 0.5rem; font-size: 0.6875rem;">
                <div class="flex justify-between">
                    <span>Stok Fisik Saat Ini:</span>
                    <strong id="info-stok-fisik">0 Pcs</strong>
                </div>
                <div class="flex justify-between mt-0.5">
                    <span>Terkunci PO Pelanggan:</span>
                    <strong id="info-stok-terkunci" style="color: #d97706;">0 Pcs</strong>
                </div>
                <div class="flex justify-between mt-0.5" style="border-top: 1px dashed #fcd34d; padding-top: 0.25rem;">
                    <span>Sisa Stok Bebas (Maksimal Kurang):</span>
                    <strong id="info-stok-bebas" style="color: #059669;">0 Pcs</strong>
                </div>
            </div>
            
            <div class="mt-2">
                <label class="font-bold">Jumlah Penyesuaian Stok</label>
                <input type="number" id="input-stok-tambahan" name="stok_tambahan" required placeholder="Contoh: +10 atau -2" class="form-input">
                <p id="adjust-hint-text" style="font-size: 0.625rem; color: var(--text-muted); margin-top: 0.25rem;"></p>
            </div>

            <div class="mt-2">
                <label class="font-bold">Keterangan / Alasan Penyesuaian</label>
                <input type="text" name="keterangan" required placeholder="Misal: Hasil stok opname gudang / kemasan rusak" class="form-input">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-adjust').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Update Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Konfirmasi Hapus Produk Jadi -->
<div id="modal-delete-product" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0; color: #dc2626;">🗑️ Konfirmasi Hapus Produk Jadi</h3>
            <button onclick="document.getElementById('modal-delete-product').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-delete-product" method="POST" style="margin-top: 1rem;">
            @csrf
            @method('DELETE')

            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 0.625rem; padding: 0.75rem; font-size: 0.75rem; color: #991b1b;">
                <p style="margin:0;">Anda yakin ingin menghapus produk:</p>
                <p id="delete-product-name" class="font-black" style="font-size: 0.875rem; margin: 0.25rem 0;"></p>
                <p id="delete-stock-warning" style="margin: 0.25rem 0 0 0; font-size: 0.6875rem;"></p>
            </div>

            <div class="mt-3">
                <label class="font-bold">Alasan Penghapusan (Wajib untuk Audit Log)</label>
                <input type="text" name="keterangan" required placeholder="Misal: Salah input SKU / Varian sudah tidak diproduksi" class="form-input">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-delete-product').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" id="btn-confirm-delete" class="btn btn-primary" style="background-color: #dc2626; border-color: #dc2626;">Ya, Hapus Permanen</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStokFisik = 0;
let currentStokTerkunci = 0;
let currentStokBebas = 0;

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

function openEditProductModal(id, nama, varian, netto, harga) {
    document.getElementById('edit-nama-produk').value = nama;
    document.getElementById('edit-varian').value = varian;
    document.getElementById('edit-netto').value = netto;
    document.getElementById('edit-harga-jual').value = harga;
    document.getElementById('form-edit-product').action = '/finished-goods/update/' + id;
    document.getElementById('modal-edit-product').classList.remove('hidden');
}

function openAdjustModal(id, name, stokFisik, stokTerkunci, stokBebas) {
    currentStokFisik = stokFisik;
    currentStokTerkunci = stokTerkunci;
    currentStokBebas = stokBebas;

    document.getElementById('adjust-product-name').innerText = name;
    document.getElementById('info-stok-fisik').innerText = stokFisik + ' Pcs';
    document.getElementById('info-stok-terkunci').innerText = stokTerkunci + ' Pcs';
    document.getElementById('info-stok-bebas').innerText = stokBebas + ' Pcs';
    document.getElementById('info-stok-bebas').style.color = stokBebas > 0 ? '#059669' : '#dc2626';

    const inputEl = document.getElementById('input-stok-tambahan');
    const hintEl = document.getElementById('adjust-hint-text');
    inputEl.value = '';

    if (stokFisik <= 0 || stokBebas <= 0) {
        inputEl.min = 1;
        hintEl.innerHTML = '<span style="color:#dc2626; font-weight:bold;">⚠️ Stok bebas = 0 (stok kosong atau terkunci PO). Hanya diperbolehkan penambahan stok (+).</span>';
    } else {
        inputEl.min = -stokBebas;
        hintEl.innerText = '*Masukan angka positif (+) untuk tambah stok, atau maksimal -' + stokBebas + ' untuk pengurangan.';
    }

    document.getElementById('form-adjust').action = '/finished-goods/update-stock/' + id;
    document.getElementById('modal-adjust').classList.remove('hidden');
}

function validateAdjustStock() {
    const val = parseInt(document.getElementById('input-stok-tambahan').value) || 0;
    if (val === 0) {
        alert('Jumlah penyesuaian stok tidak boleh 0.');
        return false;
    }
    if (val < 0) {
        if (currentStokFisik <= 0 || currentStokBebas <= 0) {
            alert('Ditolak! Stok aktual saat ini ' + currentStokFisik + ' Pcs dan terkunci PO ' + currentStokTerkunci + ' Pcs (Stok Bebas = 0). Tidak dapat melakukan pengurangan stok!');
            return false;
        }
        if (Math.abs(val) > currentStokBebas) {
            alert('Ditolak! Pengurangan ' + Math.abs(val) + ' Pcs melebihi sisa stok bebas (' + currentStokBebas + ' Pcs). Maksimal pengurangan adalah -' + currentStokBebas + ' Pcs.');
            return false;
        }
    }
    return true;
}

function openDeleteProductModal(id, name, stokFisik) {
    document.getElementById('delete-product-name').innerText = name + ' (FG-00' + id + ')';
    document.getElementById('form-delete-product').action = '/finished-goods/destroy/' + id;

    const warningEl = document.getElementById('delete-stock-warning');
    const submitBtn = document.getElementById('btn-confirm-delete');

    if (stokFisik !== 0) {
        warningEl.innerHTML = '⚠️ <strong>Stok saat ini masih ' + stokFisik + ' Pcs.</strong> Kosongkan stok menjadi 0 terlebih dahulu sebelum menghapus.';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    } else {
        warningEl.innerHTML = '✓ Stok fisik sudah 0 Pcs. Tindakan ini akan dicatat di Riwayat Audit Log.';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }

    document.getElementById('modal-delete-product').classList.remove('hidden');
}
</script>

@endsection