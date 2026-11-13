@extends('layouts.app')

@section('title', 'Pangeran Condet, Stok bahan')

@section('content')
@php
    $sortedMaterials = $materials->sortBy('qty_stok')->values();
@endphp

<section class="hero sm" aria-labelledby="j-stok">
    <div>
        <h1 id="j-stok">Pengadaan dan stok bahan produksi</h1>
        <p>Cek stok bahan baku, catat nota pembelian dari supplier, dan lihat rekap belanja bulan ini (Rp {{ number_format($totalPengadaanBulanIni ?? 0, 0, ',', '.') }}).</p>
        <div class="acts">
            <button type="button" class="btn" onclick="openPurchaseModal()">Catat nota pembelian</button>
            <button type="button" class="btn ghost" onclick="openDialog('modal-material')">Tambah master bahan</button>
        </div>
    </div>
    <ul class="trio" aria-label="Ringkasan">
        <li>
            <div class="disc {{ ($totalBahanCount ?? 0) > 0 ? 'fill' : 'empty' }}">{{ number_format($totalBahanCount ?? 0) }}</div>
            <b>Jenis bahan</b>
            <span>terdaftar</span>
        </li>
        <li>
            <div class="disc {{ ($stokKritisCount ?? 0) > 0 ? 'alert' : 'empty' }}">{{ number_format($stokKritisCount ?? 0) }}</div>
            <b>Stok kritis</b>
            <span>perlu dibeli ulang</span>
        </li>
    </ul>
</section>

<section class="panel" id="panel-stok" aria-label="Bahan baku">
    <div class="tabs" role="group" aria-label="Tampilan">
        <button type="button" data-t="stok" aria-selected="true">Stok bahan baku</button>
        <button type="button" data-t="riwayat" aria-selected="false">Riwayat pembelian</button>
    </div>

    <div id="t-stok" data-filter-box data-total="{{ $sortedMaterials->count() }}" data-unit="bahan">
        <div class="chips">
            <button type="button" data-f="semua" aria-pressed="true">Semua</button>
            <button type="button" data-f="kritis" aria-pressed="false">Kritis</button>
            <button type="button" data-f="aman" aria-pressed="false">Aman</button>
            <p>Garis tegak pada batang menandai batas minimum 10.</p>
        </div>

        @if($sortedMaterials->isNotEmpty())
            <ul class="rows">
                @foreach($sortedMaterials as $mat)
                    @php
                        $isKritis = $mat->qty_stok <= 5;
                        $gaugePct = min(100, max(4, (int) round(($mat->qty_stok / 20) * 100)));
                        $qtyLabel = rtrim(rtrim(number_format((float) $mat->qty_stok, 2, '.', ''), '0'), '.');
                    @endphp
                    <li class="row {{ $isKritis ? 'low' : 'ok' }}"
                        data-s="{{ $isKritis ? 'kritis' : 'aman' }}"
                        data-q="{{ strtolower('bahan-00' . $mat->id_bahan . ' ' . $mat->nama_bahan . ' ' . $mat->tipe . ' ' . $mat->ukuran) }}"
                        @if($loop->index >= 6) data-more="1" hidden @endif>
                        <div>
                            <b>{{ $mat->nama_bahan }}</b>
                            <small>BAHAN-00{{ $mat->id_bahan }}, {{ $mat->tipe }}</small>
                        </div>
                        <div class="gauge"><i style="width:{{ $gaugePct }}%"></i></div>
                        <div class="qty">{{ $qtyLabel }} <small>{{ $mat->ukuran }}</small></div>
                        <span class="st">{{ $isKritis ? 'Kritis' : 'Stok aman' }}</span>
                        <div class="act">
                            <button type="button" onclick="editMaterial({{ $mat->id_bahan }}, {{ \Illuminate\Support\Js::from($mat->nama_bahan) }}, {{ \Illuminate\Support\Js::from($mat->tipe) }}, {{ \Illuminate\Support\Js::from($mat->ukuran) }}, {{ (float) $mat->qty_stok }})">Ubah</button>
                            <form action="{{ route('raw-materials.material.destroy', $mat->id_bahan) }}" method="POST" onsubmit="return confirm('Hapus master bahan baku ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="del">Hapus</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="empty-filter" hidden>
                <strong>Tidak ada bahan yang cocok</strong>
                Pilih filter lain untuk menampilkan stok bahan baku.
            </div>
            <div class="foot">
                <span id="cnt" data-cnt>Menampilkan {{ min(6, $sortedMaterials->count()) }} dari {{ $sortedMaterials->count() }} bahan</span>
                @if($sortedMaterials->count() > 6)
                    <button type="button" class="btn" data-expand="Lihat semua {{ $sortedMaterials->count() }} bahan" data-collapse="Tampilkan 6 bahan">Lihat semua {{ $sortedMaterials->count() }} bahan</button>
                @endif
            </div>
        @else
            <x-void-state
                title="Belum ada master bahan baku"
                description="Tambahkan master bahan baku pertama agar stok dapur dan nota pembelian supplier bisa dicatat."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-material')">Tambah master bahan</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>

    <div id="t-riwayat" hidden>
        <div class="filter-bar no-print">
            <form action="{{ route('raw-materials.index') }}" method="GET">
                <select name="periode" class="field" aria-label="Filter periode transaksi">
                    <option value="">Semua riwayat</option>
                    <option value="minggu_ini" {{ request('periode') == 'minggu_ini' ? 'selected' : '' }}>Minggu ini</option>
                    <option value="bulan_ini" {{ request('periode') == 'bulan_ini' ? 'selected' : '' }}>Bulan ini</option>
                </select>
                <button type="submit" class="btn outline">Filter</button>
            </form>
            <div class="filter-summary">
                <span>Belanja bulan ini: <strong>Rp {{ number_format($totalPengadaanBulanIni ?? 0, 0, ',', '.') }}</strong></span>
                <button type="button" class="btn outline" onclick="window.print()">Cetak laporan transaksi</button>
            </div>
        </div>

        @if(count($groupedTransactions) > 0)
            <ul class="rows">
                @foreach($groupedTransactions as $groupKey => $items)
                    @php
                        $firstItem = $items->first();
                        $txInfo = $txDetailMap[$groupKey];
                        $txIds = $items->pluck('no_transaksi_beli')->toArray();
                    @endphp
                    <li class="row tx ok">
                        <div>
                            <b>{{ $firstItem->nama_supplier }}</b>
                            <small>{{ $txInfo['kode_transaksi'] }} · {{ $txInfo['waktu_transaksi'] }}</small>
                        </div>
                        <div class="qty">
                            {{ $items->count() }} <small>jenis ({{ number_format($txInfo['total_qty']) }} qty)</small>
                        </div>
                        <div class="price">
                            <b>Rp {{ number_format($txInfo['total_harga'], 0, ',', '.') }}</b>
                            <small>total belanja</small>
                        </div>
                        <div class="act">
                            <button type="button" onclick="showDetailGroup({{ \Illuminate\Support\Js::from($groupKey) }})">Detail</button>
                            <form action="{{ route('raw-materials.purchase.destroy-group') }}" method="POST" onsubmit="return confirm('Hapus seluruh nota pembelian ini? Stok bahan akan disesuaikan.')">
                                @csrf
                                @foreach($txIds as $id)
                                    <input type="hidden" name="ids[]" value="{{ $id }}">
                                @endforeach
                                <button type="submit" class="del">Hapus</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-void-state
                title="Belum ada nota pembelian"
                description="Nota dari supplier yang kamu catat akan muncul di sini, lengkap dengan rekap belanja per bulan."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openPurchaseModal()">Catat nota pembelian</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>
</section>

<!-- Modal Catat Nota Pembelian Bahan -->
<x-modal
    id="modal-purchase"
    title="Catat nota pembelian bahan"
    subtitle="Catat belanja multi-item dari supplier untuk menambah stok bahan baku secara otomatis."
    :wide="true"
>
    <form action="{{ route('raw-materials.purchase.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="nota-header-meta">
                <span>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
                <span class="nota-code">{{ $nextTrsCode ?? 'TRS - 001 - ' . date('dmy') }}</span>
            </div>

            <div class="form-box">
                <div class="form-grid two">
                    <div class="form-field">
                        <label class="form-label" for="purchase-supplier">Nama Toko / Supplier *</label>
                        <input id="purchase-supplier" type="text" name="nama_supplier" required placeholder="Misal: Agen Murni Jaya" class="form-input">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="input-total-ongkir">Ongkos Kirim (Opsional - Rp)</label>
                        <input type="number" id="input-total-ongkir" name="total_ongkir" value="0" min="0" oninput="recalcPurchaseSummary()" class="form-input">
                    </div>
                </div>
                <div class="summary-kv">
                    <span>Total item: <strong id="summary-total-item">1 Jenis Bahan</strong></span>
                    <span>Total harga: <strong id="summary-total-harga">Rp. 0</strong></span>
                </div>
            </div>

            <div class="form-box-head">
                <span>Daftar rincian item pembelian</span>
                <button type="button" onclick="addRow()" class="btn outline">+ Tambah baris item</button>
            </div>

            <div class="modal-table-wrap">
                <table class="nota-table">
                    <thead>
                        <tr>
                            <th class="ctr">No.</th>
                            <th>Item</th>
                            <th class="ctr">Qty</th>
                            <th class="ctr">Satuan</th>
                            <th class="num">Harga Satuan</th>
                            <th class="num">Total Harga</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="purchase-tbody">
                        @php
                            $defaultSatuan = $materials->first()->ukuran ?? '-';
                        @endphp
                        <tr class="purchase-row">
                            <td class="ctr row-number">1</td>
                            <td>
                                <select name="bahan_ids[]" onchange="onBahanSelectChange(this)" required class="form-input">
                                    @foreach($materials as $m)
                                        <option value="{{ $m->id_bahan }}" data-satuan="{{ $m->ukuran }}">{{ $m->nama_bahan }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="qty[]" step="any" min="0.1" required placeholder="0" oninput="recalcRowFromUnit(this)" class="form-input input-qty">
                            </td>
                            <td class="ctr">
                                <span class="tag satuan-label">{{ $defaultSatuan }}</span>
                            </td>
                            <td>
                                <input type="number" name="harga_satuan[]" min="0" required placeholder="0" oninput="recalcRowFromUnit(this)" class="form-input input-harga-satuan">
                            </td>
                            <td>
                                <input type="number" name="harga_total[]" min="0" required placeholder="0" oninput="recalcRowFromTotal(this)" class="form-input input-harga-total">
                            </td>
                            <td class="ctr">
                                <button type="button" onclick="removeRow(this)" class="row-remove-btn" title="Hapus baris">&times;</button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="num">Grand Total Nota</td>
                            <td id="tfoot-grand-total" class="num">Rp. 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="modal-foot">
            <span>Stok bahan bertambah otomatis setelah disimpan</span>
            <div class="acts">
                <button type="button" class="btn outline" data-close>Batal</button>
                <button type="submit" class="btn">Simpan nota pembelian</button>
            </div>
        </div>
    </form>
</x-modal>

<!-- Modal Detail Transaksi Pembelian -->
<x-modal
    id="modal-detail"
    title="Transaksi pembelian bahan"
    subtitle="Rincian nota pembelian supplier beserta ongkos kirim."
    :wide="true"
>
    <div class="modal-body">
        <div class="nota-header-meta">
            <span id="detail-waktu-transaksi">-</span>
            <span id="detail-kode-transaksi" class="nota-code">TRS - 000 - 000000</span>
        </div>

        <div class="modal-meta">
            <div><span>Nama toko / supplier</span><b id="detail-nama-toko">-</b></div>
            <div><span>Total item</span><b id="detail-total-item">0</b></div>
            <div><span>Total harga nota</span><b id="detail-total-harga">Rp. 0</b></div>
        </div>

        <div class="modal-table-wrap">
            <table class="nota-table">
                <thead>
                    <tr>
                        <th class="ctr">No.</th>
                        <th>Item</th>
                        <th class="ctr">Qty</th>
                        <th class="ctr">Satuan</th>
                        <th class="num">Harga Satuan</th>
                        <th class="num">Total Harga</th>
                    </tr>
                </thead>
                <tbody id="detail-tbody"></tbody>
                <tfoot id="detail-tfoot"></tfoot>
            </table>
        </div>
    </div>

    <div class="modal-foot no-print">
        <span>Arsip nota pembelian bahan baku</span>
        <div class="acts">
            <button type="button" onclick="window.print()" class="btn outline">Cetak nota</button>
            <button type="button" class="btn" data-close>Tutup</button>
        </div>
    </div>
</x-modal>

<!-- Modal Tambah Master Bahan Baru -->
<x-modal
    id="modal-material"
    title="Tambah master bahan baku"
    subtitle="Daftarkan bahan baku baru ke inventaris dapur Pangeran Condet."
>
    <form action="{{ route('raw-materials.material.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="add-nama-bahan">Nama Bahan Baku *</label>
                <input id="add-nama-bahan" type="text" name="nama_bahan" required placeholder="Misal: Tepung Tapioka Super" class="form-input">
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="add-tipe-bahan">Tipe Bahan *</label>
                    <select id="add-tipe-bahan" name="tipe" class="form-input">
                        <option value="kering">Kering</option>
                        <option value="basah">Basah</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="add-ukuran-bahan">Satuan Ukuran *</label>
                    <input id="add-ukuran-bahan" type="text" name="ukuran" required placeholder="kg / Liter / pack" class="form-input">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="add-stok-bahan">Stok Awal *</label>
                <input id="add-stok-bahan" type="number" name="qty_stok" value="0" min="0" step="any" required class="form-input">
            </div>
        </div>

        <div class="modal-foot">
            <span>Batas minimum aman adalah 10 unit</span>
            <div class="acts">
                <button type="button" class="btn outline" data-close>Batal</button>
                <button type="submit" class="btn">Simpan master bahan</button>
            </div>
        </div>
    </form>
</x-modal>

<!-- Modal Edit Master Bahan Baku -->
<x-modal
    id="modal-edit-material"
    title="Ubah master bahan baku"
    subtitle="Perbarui informasi nama, tipe, satuan, atau stok aktual bahan baku."
>
    <form id="form-edit-material" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="edit-nama-bahan">Nama Bahan Baku *</label>
                <input type="text" id="edit-nama-bahan" name="nama_bahan" required class="form-input">
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="edit-tipe">Tipe Bahan *</label>
                    <select id="edit-tipe" name="tipe" class="form-input">
                        <option value="kering">Kering</option>
                        <option value="basah">Basah</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label" for="edit-ukuran">Satuan Ukuran *</label>
                    <input type="text" id="edit-ukuran" name="ukuran" required class="form-input">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="edit-qty-stok">Stok Aktual *</label>
                <input type="number" id="edit-qty-stok" name="qty_stok" min="0" step="any" required class="form-input">
            </div>
        </div>

        <div class="modal-foot">
            <span>Perubahan langsung memperbarui status kritis</span>
            <div class="acts">
                <button type="button" class="btn outline" data-close>Batal</button>
                <button type="submit" class="btn">Perbarui master bahan</button>
            </div>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
const txDetailDataMap = @json($txDetailMap ?? []);

function formatRupiahNota(angka) {
    return 'Rp. ' + new Intl.NumberFormat('id-ID').format(Math.round(angka || 0));
}

function openPurchaseModal() {
    recalcPurchaseSummary();
    openDialog('modal-purchase');
}

function onBahanSelectChange(selectEl) {
    const row = selectEl.closest('tr');
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const satuan = selectedOption ? (selectedOption.getAttribute('data-satuan') || '-') : '-';
    const satuanLabel = row.querySelector('.satuan-label');
    if (satuanLabel) {
        satuanLabel.innerText = satuan;
    }
}

function recalcRowFromUnit(inputEl) {
    const row = inputEl.closest('tr');
    const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
    const hargaSatuan = parseFloat(row.querySelector('.input-harga-satuan').value) || 0;
    const totalInput = row.querySelector('.input-harga-total');

    totalInput.value = qty > 0 && hargaSatuan > 0 ? Math.round(qty * hargaSatuan) : '';
    recalcPurchaseSummary();
}

function recalcRowFromTotal(inputEl) {
    const row = inputEl.closest('tr');
    const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
    const totalHarga = parseFloat(inputEl.value) || 0;
    const satuanInput = row.querySelector('.input-harga-satuan');

    if (qty > 0 && totalHarga > 0) {
        satuanInput.value = Math.round(totalHarga / qty);
    }
    recalcPurchaseSummary();
}

function recalcPurchaseSummary() {
    const rows = document.querySelectorAll('#purchase-tbody tr.purchase-row');
    let subtotalSemua = 0;

    rows.forEach((row, index) => {
        const numCell = row.querySelector('.row-number');
        if (numCell) numCell.innerText = index + 1;

        const totalVal = parseFloat(row.querySelector('.input-harga-total').value) || 0;
        subtotalSemua += totalVal;
    });

    const ongkir = parseFloat(document.getElementById('input-total-ongkir')?.value) || 0;
    const grandTotal = subtotalSemua + ongkir;

    document.getElementById('summary-total-item').innerText = rows.length + ' Jenis Bahan';
    document.getElementById('summary-total-harga').innerText = formatRupiahNota(grandTotal);
    document.getElementById('tfoot-grand-total').innerText = formatRupiahNota(grandTotal);
}

function addRow() {
    const tbody = document.getElementById('purchase-tbody');
    const firstRow = tbody.querySelector('tr.purchase-row');
    if (!firstRow) return;
    const newRow = firstRow.cloneNode(true);

    const selectEl = newRow.querySelector('select[name="bahan_ids[]"]');
    selectEl.selectedIndex = 0;
    onBahanSelectChange(selectEl);

    newRow.querySelector('.input-qty').value = '';
    newRow.querySelector('.input-harga-satuan').value = '';
    newRow.querySelector('.input-harga-total').value = '';

    tbody.appendChild(newRow);
    recalcPurchaseSummary();
}

function removeRow(btn) {
    const tbody = document.getElementById('purchase-tbody');
    if (tbody.querySelectorAll('tr.purchase-row').length > 1) {
        btn.closest('tr').remove();
        recalcPurchaseSummary();
    }
}

function showDetailGroup(groupKey) {
    const tx = txDetailDataMap[groupKey];
    if (!tx) return;

    document.getElementById('detail-waktu-transaksi').innerText = tx.waktu_transaksi;
    document.getElementById('detail-kode-transaksi').innerText = tx.kode_transaksi;
    document.getElementById('detail-nama-toko').innerText = tx.nama_toko;
    document.getElementById('detail-total-item').innerText =
        tx.total_item + ' Jenis Bahan (' + new Intl.NumberFormat('id-ID').format(tx.total_qty) + ' Total Qty)';
    document.getElementById('detail-total-harga').innerText = formatRupiahNota(tx.total_harga);

    const tbody = document.getElementById('detail-tbody');
    const tfoot = document.getElementById('detail-tfoot');
    tbody.textContent = '';
    tfoot.textContent = '';

    tx.items.forEach((item, index) => {
        const tr = document.createElement('tr');

        const tdNo = document.createElement('td');
        tdNo.className = 'ctr';
        tdNo.textContent = index + 1;

        const tdNama = document.createElement('td');
        const bNama = document.createElement('b');
        bNama.textContent = item.nama_bahan;
        tdNama.appendChild(bNama);

        const tdQty = document.createElement('td');
        tdQty.className = 'ctr';
        tdQty.textContent = new Intl.NumberFormat('id-ID').format(item.qty);

        const tdSatuan = document.createElement('td');
        tdSatuan.className = 'ctr';
        tdSatuan.textContent = item.satuan;

        const tdHargaSatuan = document.createElement('td');
        tdHargaSatuan.className = 'num';
        tdHargaSatuan.textContent = formatRupiahNota(item.harga_satuan);

        const tdTotal = document.createElement('td');
        tdTotal.className = 'num';
        const bTotal = document.createElement('b');
        bTotal.textContent = formatRupiahNota(item.total_harga);
        tdTotal.appendChild(bTotal);

        tr.append(tdNo, tdNama, tdQty, tdSatuan, tdHargaSatuan, tdTotal);
        tbody.appendChild(tr);
    });

    if (tx.total_ongkir > 0) {
        const trOngkir = document.createElement('tr');
        const tdOngkirLbl = document.createElement('td');
        tdOngkirLbl.colSpan = 5;
        tdOngkirLbl.className = 'num';
        tdOngkirLbl.textContent = 'Ongkos Kirim';
        const tdOngkirVal = document.createElement('td');
        tdOngkirVal.className = 'num';
        tdOngkirVal.textContent = formatRupiahNota(tx.total_ongkir);
        trOngkir.append(tdOngkirLbl, tdOngkirVal);
        tfoot.appendChild(trOngkir);
    }

    const trGrand = document.createElement('tr');
    const tdGrandLbl = document.createElement('td');
    tdGrandLbl.colSpan = 5;
    tdGrandLbl.className = 'num';
    tdGrandLbl.textContent = 'Grand Total Pembelian';
    const tdGrandVal = document.createElement('td');
    tdGrandVal.className = 'num';
    tdGrandVal.textContent = formatRupiahNota(tx.total_harga);
    trGrand.append(tdGrandLbl, tdGrandVal);
    tfoot.appendChild(trGrand);

    openDialog('modal-detail');
}

function editMaterial(id, nama, tipe, ukuran, stok) {
    document.getElementById('form-edit-material').action = '/raw-materials/material/' + id;
    document.getElementById('edit-nama-bahan').value = nama;
    document.getElementById('edit-tipe').value = tipe;
    document.getElementById('edit-ukuran').value = ukuran;
    document.getElementById('edit-qty-stok').value = stok;
    openDialog('modal-edit-material');
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('periode')) {
        const panel = document.getElementById('panel-stok');
        if (panel && window.activatePanelTab) {
            window.activatePanelTab(panel, 'riwayat');
        }
    }
});
</script>
@endpush
