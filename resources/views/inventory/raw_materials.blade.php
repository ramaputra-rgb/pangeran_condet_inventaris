@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Pengadaan & Stok Bahan Produksi</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Kelola stok bahan baku, catat nota pembelian multi-item dari supplier, dan rekapitulasi pengadaan.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button onclick="openPurchaseModal()" class="btn btn-primary">
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
                        <td style="vertical-align: middle;">
                            <p class="font-black" style="color: var(--pc-dark); margin:0;">{{ $mat->nama_bahan }}</p>
                            <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0;">ID: BAHAN-00{{ $mat->id_bahan }}</p>
                        </td>
                        <td class="uppercase font-bold" style="color: #4b5563; vertical-align: middle;">{{ $mat->tipe }}</td>
                        <td class="font-bold" style="color: #6b7280; vertical-align: middle;">{{ $mat->ukuran }}</td>
                        <td class="font-black text-sm" style="color: #059669; vertical-align: middle;">
                            {{ number_format($mat->qty_stok) }} {{ $mat->ukuran }}
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
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
                        <td class="text-center no-print" style="vertical-align: middle;">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editMaterial('{{ $mat->id_bahan }}', '{{ addslashes($mat->nama_bahan) }}', '{{ $mat->tipe }}', '{{ addslashes($mat->ukuran) }}', '{{ $mat->qty_stok }}')"
                                        title="Edit Master Bahan" class="btn btn-outline" style="padding: 0.35rem 0.65rem;">
                                    ✏️ Edit
                                </button>

                                <form action="{{ route('raw-materials.material.destroy', $mat->id_bahan) }}" method="POST" onsubmit="return confirm('Hapus master bahan baku ini?')" style="display:inline; margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Master Bahan" class="btn" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.35rem 0.65rem;">
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

<!-- TAB 2: RIWAYAT TRANSAKSI PEMBELIAN -->
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
            <table class="custom-table" style="table-layout: fixed;">
                <colgroup>
                    <col style="width: 36%;">
                    <col style="width: 22%;">
                    <col style="width: 20%;">
                    <col style="width: 22%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>KODE & SUPPLIER</th>
                        <th class="text-center">JUMLAH ITEM</th>
                        <th class="text-right">TOTAL BELANJA</th>
                        <th class="text-center no-print">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($groupedTransactions) > 0)
                        @foreach($groupedTransactions as $groupKey => $items)
                            @php
                                $firstItem = $items->first();
                                $txInfo = $txDetailMap[$groupKey];
                                $txIds = $items->pluck('no_transaksi_beli')->toArray();
                            @endphp
                            <tr>
                                <td style="vertical-align: middle;">
                                    <span class="font-black" style="color: #d97706; font-size: 0.6875rem;">{{ $txInfo['kode_transaksi'] }}</span>
                                    <p class="font-black" style="color: var(--pc-dark); margin: 0.15rem 0 0 0; font-size: 0.8125rem;">{{ $firstItem->nama_supplier }}</p>
                                    <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.15rem 0 0 0;">🕒 {{ $txInfo['waktu_transaksi'] }}</p>
                                </td>
                                <td class="text-center font-bold" style="color: #4b5563; vertical-align: middle;">
                                    <span class="badge" style="background-color: #f3f4f6; color: #374151; font-size: 0.6875rem; padding: 0.3rem 0.6rem;">
                                        {{ $items->count() }} Jenis Bahan ({{ number_format($txInfo['total_qty']) }} Qty)
                                    </span>
                                </td>
                                <td class="text-right font-black" style="color: var(--pc-maroon); font-size: 0.8125rem; vertical-align: middle;">
                                    Rp {{ number_format($txInfo['total_harga'], 0, ',', '.') }}
                                </td>
                                <td class="text-center no-print" style="vertical-align: middle;">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                                onclick="showDetailGroup({{ \Illuminate\Support\Js::from($groupKey) }})"
                                                class="btn btn-outline" style="padding: 0.375rem 0.7rem;">
                                            👁️ Detail
                                        </button>

                                        <form action="{{ route('raw-materials.purchase.destroy-group') }}" method="POST" onsubmit="return confirm('Hapus seluruh nota pembelian ini? Stok bahan akan disesuaikan.')" style="display:inline; margin:0;">
                                            @csrf
                                            @foreach($txIds as $id)
                                                <input type="hidden" name="ids[]" value="{{ $id }}">
                                            @endforeach
                                            <button type="submit" title="Hapus Transaksi Nota Ini" class="btn" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.375rem 0.7rem;">
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

<!-- MODAL CATAT NOTA PEMBELIAN BAHAN (RAPIH & PROPORSIONAL) -->
<div id="modal-purchase" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 56rem; padding: 1.5rem; max-height: 92vh; overflow-y: auto;">
        <!-- Tombol Close Pojok Kanan Atas -->
        <div class="flex justify-end" style="margin-bottom: 0.5rem;">
            <button type="button"
                    onclick="document.getElementById('modal-purchase').classList.add('hidden')"
                    style="width: 1.75rem; height: 1.75rem; border-radius: 9999px; background-color: #e5e7eb; color: #4b5563; border: none; cursor: pointer; font-weight: 900; display: flex; align-items: center; justify-content: center;"
                    title="Tutup">
                ✕
            </button>
        </div>

        <!-- Banner Judul Form -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.75rem 1rem; text-align: center;">
            <h3 class="font-black" style="margin: 0; font-size: 1.05rem; color: var(--pc-dark);">
                Catat Nota Pembelian Bahan
            </h3>
        </div>

        <!-- Baris Tanggal Transaksi & Preview Kode Transaksi -->
        <div class="flex flex-wrap justify-between items-center gap-2 mt-3 px-1">
            <span class="font-black" style="font-size: 0.8125rem; color: var(--pc-dark);">
                {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            </span>
            <span class="font-black" style="font-size: 0.8125rem; color: #eab308; letter-spacing: 0.03em;">
                {{ $nextTrsCode ?? 'TRS - 001 - ' . date('dmy') }}
            </span>
        </div>

        <form action="{{ route('raw-materials.purchase.store') }}" method="POST" style="margin-top: 0.75rem;">
            @csrf

            <!-- Kotak Ringkasan Input Toko, Ongkir, Total Item & Total Harga Otomatis -->
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.875rem 1rem;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold" style="font-size: 0.75rem; color: #374151;">Nama Toko / Supplier *</label>
                        <input type="text" name="nama_supplier" required placeholder="Misal: Agen Murni Jaya" class="form-input" style="margin-top: 0.25rem; background: #fff;">
                    </div>
                    <div>
                        <label class="font-bold" style="font-size: 0.75rem; color: #374151;">Ongkos Kirim (Opsional - Rp)</label>
                        <input type="number" id="input-total-ongkir" name="total_ongkir" value="0" min="0" oninput="recalcPurchaseSummary()" class="form-input" style="margin-top: 0.25rem; background: #fff;">
                    </div>
                </div>

                <div class="flex flex-wrap justify-between items-center gap-2 mt-3 pt-2" style="border-top: 1px dashed #cbd5e1; font-size: 0.8125rem;">
                    <div>
                        <span style="color: #4b5563; font-weight: 600;">Total Item : </span>
                        <span id="summary-total-item" class="font-black" style="color: var(--pc-dark);">1 Jenis Bahan</span>
                    </div>
                    <div>
                        <span style="color: #4b5563; font-weight: 600;">Total Harga : </span>
                        <span id="summary-total-harga" class="font-black" style="color: var(--pc-maroon); font-size: 0.9rem;">Rp. 0</span>
                    </div>
                </div>
            </div>

            <!-- Header Aksi Tambah Item -->
            <div class="flex justify-between items-center mt-4 mb-2">
                <span class="font-black" style="color: var(--pc-dark); font-size: 0.75rem;">Daftar Rincian Item Pembelian:</span>
                <button type="button" onclick="addRow()" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.6875rem;">
                    + Tambah Baris Item
                </button>
            </div>

            <!-- Tabel Input 6 Kolom Proporsional -->
            <div class="table-responsive-wrapper" style="border: 1.5px solid #93c5fd; border-radius: 0.5rem; overflow-x: auto;">
                <table class="custom-table table-compact" style="margin: 0; width: 100%; min-width: 44rem;">
                    <colgroup>
                        <col style="width: 2.5rem;">
                        <col style="width: auto;">
                        <col style="width: 4.75rem;">
                        <col style="width: 5.5rem;">
                        <col style="width: 7.75rem;">
                        <col style="width: 8.25rem;">
                        <col style="width: 2.25rem;">
                    </colgroup>
                    <thead>
                        <tr style="background-color: #fef9c3;">
                            <th class="text-center" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">No.</th>
                            <th style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Item</th>
                            <th class="text-center" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">QTY</th>
                            <th class="text-center" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Satuan</th>
                            <th class="text-right" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Harga Satuan</th>
                            <th class="text-right" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Total harga</th>
                            <th class="text-center" style="background-color: #fef9c3;"></th>
                        </tr>
                    </thead>
                    <tbody id="purchase-tbody">
                        @php
                            $defaultSatuan = $materials->first()->ukuran ?? '-';
                        @endphp
                        <tr class="purchase-row">
                            <td class="text-center font-bold row-number" style="color: #4b5563;">1</td>
                            <td>
                                <select name="bahan_ids[]" onchange="onBahanSelectChange(this)" required class="form-input font-bold" style="margin: 0; background: #fff; font-size: 0.75rem; padding: 0.4rem 0.5rem; width: 100%;">
                                    @foreach($materials as $m)
                                        <option value="{{ $m->id_bahan }}" data-satuan="{{ $m->ukuran }}">{{ $m->nama_bahan }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="qty[]" step="any" min="0.1" required placeholder="0" oninput="recalcRowFromUnit(this)" class="form-input text-center font-bold input-qty" style="margin: 0; font-size: 0.75rem; padding: 0.4rem 0.35rem; width: 100%;">
                            </td>
                            <td class="text-center">
                                <span class="badge satuan-label" style="background-color: #f3f4f6; color: #374151; font-weight: 800;">{{ $defaultSatuan }}</span>
                            </td>
                            <td>
                                <input type="number" name="harga_satuan[]" min="0" required placeholder="Rp 0" oninput="recalcRowFromUnit(this)" class="form-input text-right font-semibold input-harga-satuan" style="margin: 0; font-size: 0.75rem; padding: 0.4rem 0.5rem; width: 100%;">
                            </td>
                            <td>
                                <input type="number" name="harga_total[]" min="0" required placeholder="Rp 0" oninput="recalcRowFromTotal(this)" class="form-input text-right font-black input-harga-total" style="margin: 0; font-size: 0.75rem; padding: 0.4rem 0.5rem; width: 100%; color: var(--pc-dark);">
                            </td>
                            <td class="text-center">
                                <button type="button" onclick="removeRow(this)" style="color: #ef4444; font-weight: 900; background: none; border: none; cursor: pointer; padding: 0.25rem;" title="Hapus Baris">✕</button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #fefce8; border-top: 1px solid #e2e8f0;">
                            <td colspan="5" class="text-right font-black" style="color: var(--pc-dark); font-size: 0.8125rem; text-align: right;">Grand Total Nota</td>
                            <td id="tfoot-grand-total" class="text-right font-black" style="color: var(--pc-maroon); font-size: 0.875rem; text-align: right; white-space: nowrap;">Rp. 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-purchase').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Nota Pembelian</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL TRANSAKSI PEMBELIAN BAHAN (SESUAI RANCANGAN NOTA) -->
<div id="modal-detail" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 48rem; padding: 1.5rem; position: relative; max-height: 90vh; overflow-y: auto;">
        <div class="flex justify-end no-print" style="margin-bottom: 0.5rem;">
            <button type="button"
                    onclick="document.getElementById('modal-detail').classList.add('hidden')"
                    style="width: 1.75rem; height: 1.75rem; border-radius: 9999px; background-color: #e5e7eb; color: #4b5563; border: none; cursor: pointer; font-weight: 900; display: flex; align-items: center; justify-content: center;"
                    title="Tutup">
                ✕
            </button>
        </div>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.75rem 1rem; text-align: center;">
            <h3 class="font-black" style="margin: 0; font-size: 1.05rem; color: var(--pc-dark); letter-spacing: 0.01em;">
                Transaksi Pembelian Bahan
            </h3>
        </div>

        <div class="flex flex-wrap justify-between items-center gap-2 mt-4 px-1">
            <span id="detail-waktu-transaksi" class="font-black" style="font-size: 0.875rem; color: var(--pc-dark);">
                -
            </span>
            <span id="detail-kode-transaksi" class="font-black" style="font-size: 0.875rem; color: #eab308; letter-spacing: 0.03em;">
                TRS - 000 - 000000
            </span>
        </div>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.875rem 1.125rem; margin-top: 0.75rem; font-size: 0.8125rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 7.5rem; padding: 0.25rem 0; color: #4b5563; font-weight: 600;">Nama Toko</td>
                    <td style="width: 1rem; padding: 0.25rem 0; color: #4b5563; font-weight: 700;">:</td>
                    <td id="detail-nama-toko" class="font-black" style="padding: 0.25rem 0; color: var(--pc-dark);">-</td>
                </tr>
                <tr>
                    <td style="padding: 0.25rem 0; color: #4b5563; font-weight: 600;">Total Item</td>
                    <td style="padding: 0.25rem 0; color: #4b5563; font-weight: 700;">:</td>
                    <td id="detail-total-item" class="font-bold" style="padding: 0.25rem 0; color: var(--pc-dark);">0</td>
                </tr>
                <tr>
                    <td style="padding: 0.25rem 0; color: #4b5563; font-weight: 600;">Total Harga</td>
                    <td style="padding: 0.25rem 0; color: #4b5563; font-weight: 700;">:</td>
                    <td id="detail-total-harga" class="font-black" style="padding: 0.25rem 0; color: var(--pc-maroon); font-size: 0.9rem;">Rp. 0</td>
                </tr>
            </table>
        </div>

        <div class="table-responsive-wrapper mt-4" style="border: 1.5px solid #93c5fd; border-radius: 0.5rem; overflow: hidden;">
            <table class="custom-table table-compact" style="margin: 0;">
                <thead>
                    <tr style="background-color: #fef9c3;">
                        <th class="text-center" style="width: 3rem; color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">No.</th>
                        <th style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Item</th>
                        <th class="text-center" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">QTY</th>
                        <th class="text-center" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Satuan</th>
                        <th class="text-right" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Harga Satuan</th>
                        <th class="text-right" style="color: var(--pc-dark); font-weight: 800; background-color: #fef9c3;">Total harga</th>
                    </tr>
                </thead>
                <tbody id="detail-tbody">
                </tbody>
                <tfoot id="detail-tfoot" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                </tfoot>
            </table>
        </div>

        <div class="flex justify-end items-center gap-2 mt-4 no-print">
            <button type="button" onclick="window.print()" class="btn btn-outline">
                🖨️ Cetak Nota
            </button>
            <button type="button" onclick="document.getElementById('modal-detail').classList.add('hidden')" class="btn btn-primary">
                Tutup
            </button>
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
const txDetailDataMap = @json($txDetailMap ?? []);

function formatRupiahNota(angka) {
    return 'Rp. ' + new Intl.NumberFormat('id-ID').format(Math.round(angka || 0));
}

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

function openPurchaseModal() {
    recalcPurchaseSummary();
    document.getElementById('modal-purchase').classList.remove('hidden');
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
    document.getElementById('detail-total-item').innerText = tx.total_item + ' Jenis Bahan (' + new Intl.NumberFormat('id-ID').format(tx.total_qty) + ' Total Qty)';
    document.getElementById('detail-total-harga').innerText = formatRupiahNota(tx.total_harga);

    const tbody = document.getElementById('detail-tbody');
    const tfoot = document.getElementById('detail-tfoot');
    tbody.innerHTML = '';
    tfoot.innerHTML = '';

    tx.items.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center font-bold" style="color: #4b5563;">${index + 1}</td>
            <td class="font-bold" style="color: var(--pc-dark);">${item.nama_bahan}</td>
            <td class="text-center font-bold" style="color: var(--pc-dark);">${new Intl.NumberFormat('id-ID').format(item.qty)}</td>
            <td class="text-center font-semibold" style="color: #4b5563;">${item.satuan}</td>
            <td class="text-right font-semibold" style="color: #374151; white-space: nowrap;">${formatRupiahNota(item.harga_satuan)}</td>
            <td class="text-right font-black" style="color: var(--pc-dark); white-space: nowrap;">${formatRupiahNota(item.total_harga)}</td>
        `;
        tbody.appendChild(tr);
    });

    if (tx.total_ongkir > 0) {
        tfoot.innerHTML = `
            <tr>
                <td colspan="5" class="text-right font-bold" style="color: #4b5563; font-size: 0.75rem; text-align: right;">Ongkos Kirim</td>
                <td class="text-right font-bold" style="color: #4b5563; font-size: 0.75rem; text-align: right; white-space: nowrap;">${formatRupiahNota(tx.total_ongkir)}</td>
            </tr>
            <tr style="background-color: #fefce8;">
                <td colspan="5" class="text-right font-black" style="color: var(--pc-dark); font-size: 0.8125rem; text-align: right;">Grand Total Pembelian</td>
                <td class="text-right font-black" style="color: var(--pc-maroon); font-size: 0.875rem; text-align: right; white-space: nowrap;">${formatRupiahNota(tx.total_harga)}</td>
            </tr>
        `;
    } else {
        tfoot.innerHTML = `
            <tr style="background-color: #fefce8;">
                <td colspan="5" class="text-right font-black" style="color: var(--pc-dark); font-size: 0.8125rem; text-align: right;">Grand Total Pembelian</td>
                <td class="text-right font-black" style="color: var(--pc-maroon); font-size: 0.875rem; text-align: right; white-space: nowrap;">${formatRupiahNota(tx.total_harga)}</td>
            </tr>
        `;
    }

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