@extends('layouts.app')

@section('content')

<!-- Header Modul & Tombol Aksi -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-xl font-black" style="color: var(--pc-dark); margin:0;">Pesanan PO Pelanggan (Make-to-Order)</h2>
        <p class="text-xs mt-0.5" style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Pencatatan Purchase Order dari Toko, Supermarket, & Agen. Menjadi acuan otomatis batch produksi dapur Condet.</p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button onclick="document.getElementById('modal-po').classList.remove('hidden')" class="btn btn-primary">
            + Buat PO Pelanggan Baru
        </button>

        <button onclick="window.print()" class="btn btn-outline">
            🖨️ Cetak Laporan PO
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Top-Bar Konsisten -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
    <div class="card-summary border-l-amber">
        <p class="card-title uppercase" style="color: #d97706;">PO PENDING (MENUNGGU)</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $poPendingCount ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">Pesanan</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #d97706;">⏳ Belum Dijadwalkan Dapur</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid var(--pc-orange);">
        <p class="card-title uppercase" style="color: var(--pc-orange);">PO DALAM PRODUKSI</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $poProsesCount ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">PO Aktif</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">🔥 Sedang Digoreng Dapur</p>
    </div>

    <div class="card-summary" style="border-left: 4px solid #10b981;">
        <p class="card-title uppercase" style="color: #059669;">PO SELESAI / TERKIRIM</p>
        <h3 class="card-value" style="color: #059669;">{{ $poSelesaiCount ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">Selesai</span></h3>
        <p class="mt-2" style="font-size:0.625rem; color: var(--text-muted);">✓ Sudah Diterima Pelanggan</p>
    </div>

    <div class="card-summary">
        <p class="card-title uppercase" style="color: var(--pc-maroon);">TOTAL PESANAN PO</p>
        <h3 class="card-value" style="color: var(--pc-dark);">{{ $totalPo ?? 0 }} <span style="font-size:0.75rem; color: var(--text-muted);">Total PO</span></h3>
        <p class="font-bold mt-2" style="font-size:0.625rem; color: #059669;">✓ Rantai Pasok Terintegrasi</p>
    </div>
</div>

<!-- Tabel Daftar PO Produk Pelanggan dengan Responsive Wrapper -->
<div class="table-container mt-6">
    <div class="table-header-bar">
        <span class="font-bold" style="color: var(--pc-dark);">Daftar Purchase Order (PO) Pelanggan</span>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>NO. PO</th>
                    <th>PELANGGAN & PENERIMA</th>
                    <th>DETAIL PESANAN & PROGRES DAPUR (REAL-TIME)</th>
                    <th class="text-center">TOTAL TARGET</th>
                    <th>JATUH TEMPO</th>
                    <th class="text-center">STATUS KESELURUHAN</th>
                    <th class="text-center no-print">AKSI EKSEKUSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($poList as $po)
                @php
                    $isSemuaItemSelesai = true;
                    $totalGorengKeseluruhan = 0;
                    
                    $hasilGorengPerProduk = [];
                    foreach($po->logProduksi as $log) {
                        foreach($log->detailProduksi as $det) {
                            $idProd = $det->id_produk_jadi;
                            $hasilGorengPerProduk[$idProd] = ($hasilGorengPerProduk[$idProd] ?? 0) + $det->jml_produk_jadi;
                        }
                    }
                @endphp
                <tr>
                    <td class="font-bold">
                        PO-00{{ $po->no_po_produk }}
                        @if($po->suratJalan)
                            <p style="font-size:0.625rem; color: var(--text-muted); margin:0;">SJ Ref: #{{ $po->suratJalan->no_ref }}</p>
                        @endif
                    </td>
                    <td>
                        <p class="font-black" style="color: var(--pc-dark); margin:0; font-size:0.875rem;">{{ $po->nama_pelanggan }}</p>
                        <p class="font-bold" style="font-size:0.625rem; color: #4b5563; margin:0.125rem 0 0 0;">👤 Penerima: {{ $po->nama_penerima ?? '-' }}</p>
                        <p class="truncate" style="font-size:0.625rem; color: var(--text-muted); max-width: 15rem; margin:0.125rem 0 0 0;">{{ $po->alamat_pelanggan }}</p>
                        <span class="badge" style="background-color:#f3f4f6; color:#4b5563; margin-top:0.25rem;">
                            {{ $po->tipe_jual }}
                        </span>
                        @if($po->nomor_pesanan_ecommerce)
                            <span class="badge badge-blue" style="margin-top:0.25rem; margin-left:0.25rem;" title="Pesanan Otomatis dari E-Commerce">
                                🛒 {{ $po->nomor_pesanan_ecommerce }}
                            </span>
                        @endif
                    </td>

                    <!-- KOLOM DETAIL BREAKDOWN PER-ITEM PRODUK & PROGRES REAL-TIME -->
                    <td>
                        <div style="max-width: 24rem;">
                            @forelse($po->detailAlokasi as $alokasi)
                            @php
                                $idProd = $alokasi->id_produk_jadi;
                                $targetQty = $alokasi->jml_alokasi;
                                $sudahGoreng = $hasilGorengPerProduk[$idProd] ?? 0;
                                $stokGudangFisik = $alokasi->produkJadi->stok ?? 0;
                                
                                $isStatusSudahLanjut = in_array($po->status_po, ['STOK_DIPOTONG', 'DIKIRIM', 'SELESAI']);
                                
                                if ($isStatusSudahLanjut) {
                                    $totalSiap = $targetQty;
                                    $isItemCukup = true;
                                } else {
                                    $totalSiap = min($targetQty, $stokGudangFisik + $sudahGoreng);
                                    $isItemCukup = ($totalSiap >= $targetQty);
                                }

                                if (!$isItemCukup) {
                                    $isSemuaItemSelesai = false;
                                }

                                $persenItem = $targetQty > 0 ? min(100, round(($totalSiap / $targetQty) * 100)) : 0;
                            @endphp

                            <div class="item-box">
                                <div class="flex justify-between items-center" style="font-size:0.75rem;">
                                    <span class="font-black">{{ $alokasi->produkJadi->nama_produk ?? 'Produk' }}</span>
                                    @if($isItemCukup)
                                        <span class="badge badge-emerald">
                                            ✅ SIAP ({{ number_format($totalSiap) }}/{{ number_format($targetQty) }} Pcs)
                                        </span>
                                    @else
                                        <span class="badge badge-amber">
                                            🔥 PROSES ({{ number_format($totalSiap) }}/{{ number_format($targetQty) }} Pcs)
                                        </span>
                                    @endif
                                </div>

                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill {{ $isItemCukup ? 'bg-emerald-fill' : 'bg-orange-fill' }}" 
                                         style="width: {{ $persenItem }}%"></div>
                                </div>
                                
                                <div class="flex justify-between items-center" style="font-size:0.5625rem; color: var(--text-muted);">
                                    <span>Target PO: {{ number_format($targetQty) }} Pcs</span>
                                    @if(!$isItemCukup)
                                        <span class="font-bold" style="color: #d97706;">Kurang: {{ number_format($targetQty - $totalSiap) }} Pcs lagi</span>
                                    @else
                                        <span class="font-bold" style="color: #059669;">✓ Kuota Produk Terpenuhi</span>
                                    @endif
                                </div>
                            </div>
                            @empty
                                <p style="color: var(--text-muted); font-style: italic; font-size: 0.625rem; margin:0;">Belum ada detail alokasi produk</p>
                            @endforelse
                        </div>
                    </td>

                    <td class="text-center font-black" style="color: var(--pc-maroon); font-size: 0.875rem;">
                        {{ number_format($po->jumlah_po) }} Pcs
                    </td>
                    <td class="font-bold">
                        <p style="color: var(--pc-dark); margin:0;">
                            Tgl PO: {{ \Carbon\Carbon::parse($po->tgl_po ?? $po->created_at)->format('d M Y') }}
                        </p>
                        <p style="font-size: 0.625rem; color: var(--text-muted); margin: 0.125rem 0 0 0;">
                            Jatuh Tempo: {{ \Carbon\Carbon::parse($po->tgl_jatuh_tempo_po)->format('d M Y') }}
                        </p>
                    </td>

                    <!-- STATUS KESELURUHAN PO -->
                    <td class="text-center">
                        @if($po->status_po === 'SELESAI')
                            <span class="badge badge-emerald">
                                ✅ SELESAI
                            </span>
                        @elseif($po->status_po === 'DIKIRIM')
                            <span class="badge badge-purple">
                                🚚 DIKIRIM
                            </span>
                        @elseif($po->status_po === 'STOK_DIPOTONG')
                            <span class="badge badge-blue">
                                📦 STOK DIPOTONG
                            </span>
                        @else
                            @if($isSemuaItemSelesai)
                                <span class="badge badge-emerald">
                                    ✓ SEMUA ITEM SIAP
                                </span>
                            @else
                                <span class="badge badge-amber">
                                    🍳 PROSES DAPUR
                                </span>
                            @endif
                        @endif
                    </td>

                    <!-- AKSI EKSEKUSI -->
                    <td class="text-center no-print">
                        <div class="flex flex-col gap-1 items-center">
                            @if($po->status_po !== 'SELESAI' && $po->status_po !== 'DIKIRIM')
                                
                                @if($isSemuaItemSelesai && $po->status_po !== 'STOK_DIPOTONG')
                                    <form action="{{ route('po-produk.potong-stok', $po->no_po_produk) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            ✂️ Potong Stok Ready
                                        </button>
                                    </form>

                                @elseif($po->status_po === 'STOK_DIPOTONG' && !$po->suratJalan)
                                    <button onclick="openModalSj('{{ $po->no_po_produk }}', '{{ $po->nama_penerima ?? $po->nama_pelanggan }}')" class="btn btn-info">
                                        📄 Terbitkan Surat Jalan
                                    </button>

                                @elseif($po->suratJalan)
                                    <span class="font-bold" style="font-size:0.625rem; color: #059669;">✓ SJ Sudah Terbit</span>

                                @else
                                    <a href="{{ route('po-produk.buat-batch', $po->no_po_produk) }}" class="btn btn-primary">
                                        🍳 Buat Batch Produksi Baru
                                    </a>
                                @endif

                            @endif

                            <button onclick="openStatusModal('{{ $po->no_po_produk }}', '{{ $po->status_po }}')" class="btn btn-outline">
                                ✏️ Ubah Status
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: var(--text-muted); padding: 1.5rem;">Belum ada pesanan PO pelanggan yang tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up Terbitkan Surat Jalan -->
<div id="modal-sj" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Terbitkan Surat Jalan</h3>
            <button onclick="document.getElementById('modal-sj').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('shipments.surat-jalan.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <input type="hidden" name="no_po_produk" id="sj-no-po">
            <input type="hidden" name="nama_penerima" id="sj-nama-penerima">

            <div>
                <label class="font-bold">Tanggal Terbit Surat Jalan</label>
                <input type="date" name="tgl_terbit" value="{{ date('Y-m-d') }}" required class="form-input font-bold">
            </div>

            <div class="mt-2">
                <label class="font-bold">Nama Kurir / Pengirim</label>
                <input type="text" name="nama_kurir" required placeholder="Misal: Pak Maman (Kurir Condet)" class="form-input">
            </div>

            <div class="mt-2">
                <label class="font-bold">Keterangan / Catatan Pengiriman (Opsional)</label>
                <textarea name="ket" rows="2" placeholder="Misal: Kirim via Mobil Box / Titip Security" class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-sj').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-info">Simpan & Terbitkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Buat PO Pelanggan Baru -->
<div id="modal-po" class="modal-overlay hidden">
    <div class="modal-box" style="max-width: 32rem; max-height: 90vh; overflow-y: auto;">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Input Pesanan PO Pelanggan Baru (Multi-Item)</h3>
            <button onclick="document.getElementById('modal-po').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form action="{{ route('po-produk.store') }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="font-bold">Nama Pelanggan / Toko</label>
                    <input type="text" name="nama_pelanggan" required placeholder="Misal: HERO Super Market" class="form-input">
                </div>
                <div>
                    <label class="font-bold">Nama Penerima Barang</label>
                    <input type="text" name="nama_penerima" required placeholder="Misal: Pak Budi (Gudang)" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-2">
                <div>
                    <label class="font-bold">Tipe Penjualan</label>
                    <select name="tipe_jual" required class="form-input font-bold" style="background:#fff;">
                        <option value="Konsinyasi">Konsinyasi</option>
                        <option value="Jual Putus">Jual Putus</option>
                        <option value="Eceran">Eceran</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold">Tanggal PO</label>
                    <input type="date" name="tgl_po" value="{{ date('Y-m-d') }}" required class="form-input">
                </div>
                <div>
                    <label class="font-bold">Jatuh Tempo</label>
                    <input type="date" name="tgl_jatuh_tempo_po" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required class="form-input">
                </div>
            </div>

            <div class="mt-2" style="border: 1px solid var(--border-color); border-radius: 1rem; padding: 0.75rem; background-color: rgba(249,250,251,0.5);">
                <div class="flex justify-between items-center" style="margin-bottom: 0.5rem;">
                    <span class="font-black" style="color: var(--pc-dark); font-size:0.75rem;">Daftar Produk Dipesan:</span>
                    <button type="button" onclick="addProdukRow()" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.5625rem;">+ Tambah Produk</button>
                </div>

                <div id="produk-row-container" class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 produk-item">
                        <select name="produk_ids[]" required class="form-input font-bold" style="width: 65%; margin:0; background:#fff;">
                            @foreach($products as $prod)
                                <option value="{{ $prod->id_produk_jadi }}">{{ $prod->nama_produk }} (Stok: {{ $prod->stok }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="produk_qty[]" required min="1" placeholder="Qty (Pcs)" class="form-input" style="width: 30%; margin:0;">
                        <button type="button" onclick="removeProdukRow(this)" style="color: #ef4444; font-weight: bold; background: none; border: none; cursor: pointer;">✕</button>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <label class="font-bold">Alamat Pengiriman Lengkap</label>
                <textarea name="alamat_pelanggan" required rows="2" placeholder="Jl. Raya Condet No. XX, Jakarta" class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-po').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pesanan PO</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pop-up Update Status PO -->
<div id="modal-status" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="font-black text-sm" style="margin:0;">Perbarui Status PO</h3>
            <button onclick="document.getElementById('modal-status').classList.add('hidden')" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
        </div>

        <form id="form-status" method="POST" style="margin-top: 1rem;">
            @csrf
            <div>
                <label class="font-bold">Pilih Status Baru</label>
                <select name="status_po" id="select-status-po" required class="form-input font-bold" style="background:#fff; color: var(--pc-dark);">
                    <option value="PENDING">PENDING (Menunggu)</option>
                    <option value="PROSES">PROSES (Dalam Penggorengan Dapur)</option>
                    <option value="STOK_DIPOTONG">STOK DIPOTONG (Siap Terbit SJ)</option>
                    <option value="DIKIRIM">DIKIRIM (Dalam Perjalanan)</option>
                    <option value="SELESAI">SELESAI (Diterima Pelanggan)</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('modal-status').classList.add('hidden')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function addProdukRow() {
    const container = document.getElementById('produk-row-container');
    const firstRow = container.children[0];
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('input[name="produk_qty[]"]').value = '';
    container.appendChild(newRow);
}

function removeProdukRow(btn) {
    const container = document.getElementById('produk-row-container');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    }
}

function openModalSj(noPo, namaPenerima) {
    document.getElementById('sj-no-po').value = noPo;
    document.getElementById('sj-nama-penerima').value = namaPenerima;
    document.getElementById('modal-sj').classList.remove('hidden');
}

function openStatusModal(id, currentStatus) {
    document.getElementById('select-status-po').value = currentStatus;
    document.getElementById('form-status').action = '/po-produk/update-status/' + id;
    document.getElementById('modal-status').classList.remove('hidden');
}
</script>

@endsection