@extends('layouts.app')

@section('title', 'Pangeran Condet, Pengiriman')

@section('content')
@php
    $jalanCount = 0;
    $selesaiCount = 0;
    foreach ($shipments as $shp) {
        if ($shp->status_pengiriman === 'SELESAI') {
            $selesaiCount++;
        } else {
            $jalanCount++;
        }
    }
@endphp

<section class="masthead" aria-labelledby="j-kirim">
    <div>
        <span class="eyebrow">Logistik &amp; serah terima</span>
        <h1 id="j-kirim">Surat Jalan &amp; pelacakan kirim</h1>
        <p>Terbitkan Surat Jalan untuk PO yang stoknya sudah dipotong, pantau paket dalam perjalanan, dan arsipkan bukti serah terima barang.</p>
    </div>
    <div class="acts">
        <button type="button" class="btn" onclick="openDialog('modal-create-sj')">+ Terbitkan Surat Jalan</button>
    </div>
</section>

<section class="stub-grid two" aria-label="Ringkasan pengiriman">
    <x-stub-card
        title="Pengiriman hari ini"
        subtitle="Aktivitas pengiriman keluar gudang"
        :value="number_format($pengirimanHarian ?? 0)"
        unit="kirim"
        variant="warm"
    />
    <x-stub-card
        title="Pesanan selesai diterima"
        subtitle="Arsip Surat Jalan: {{ number_format($suratJalanList->count()) }} dokumen"
        :value="number_format($pengirimanSelesai ?? $selesaiCount)"
        unit="selesai"
    />
</section>

<section class="panel" id="panel-pengiriman" aria-label="Log Pengiriman dan Surat Jalan">
    <div class="tabs" role="group" aria-label="Tampilan pengiriman">
        <button type="button" data-t="log-kirim" aria-selected="true">Log pengiriman ({{ $shipments->count() }})</button>
        <button type="button" data-t="surat-jalan" aria-selected="false">Arsip Surat Jalan ({{ $suratJalanList->count() }})</button>
    </div>

    <div id="t-log-kirim" data-filter-box data-total="{{ $shipments->count() }}" data-unit="pengiriman">
        <div class="controls">
            <input
                type="search"
                class="field grow"
                data-search
                placeholder="Cari kode PO, pelanggan, penerima, atau nomor Surat Jalan..."
                aria-label="Cari log pengiriman"
            >
            <button type="button" class="reset-btn" data-reset>Reset</button>
        </div>

        <div class="chips">
            <button type="button" data-f="semua" aria-pressed="true">Semua ({{ $shipments->count() }})</button>
            <button type="button" data-f="jalan" aria-pressed="false">Diproses / Di jalan ({{ $jalanCount }})</button>
            <button type="button" data-f="selesai" aria-pressed="false">Selesai ({{ $selesaiCount }})</button>
            <p>Unggah foto atau catatan penerima untuk menandai pesanan telah selesai diterima.</p>
        </div>

        @if($shipments->isNotEmpty())
            <ul class="rows">
                @foreach($shipments as $shp)
                    @php
                        $isDone = $shp->status_pengiriman === 'SELESAI';
                        $kodeShp = 'SHP-' . str_pad($shp->id_log_pengiriman, 4, '0', STR_PAD_LEFT);
                        $kodePo = 'PO-' . str_pad($shp->no_po_produk, 4, '0', STR_PAD_LEFT);
                        $sj = optional($shp->poProduk)->suratJalan;
                        $kodeSj = $sj ? ('SJ-' . str_pad($sj->no_ref, 4, '0', STR_PAD_LEFT)) : '';
                    @endphp
                    <li class="row ship {{ $isDone ? 'ok' : 'mid' }}"
                        data-s="{{ $isDone ? 'selesai' : 'jalan' }}"
                        data-q="{{ strtolower($kodeShp . ' ' . $kodePo . ' ' . $kodeSj . ' ' . ($shp->poProduk->nama_pelanggan ?? '') . ' ' . ($sj->nama_penerima ?? $shp->poProduk->nama_penerima ?? '') . ' ' . ($sj->nama_kurir ?? '')) }}"
                        @if($loop->index >= 6) data-more="1" hidden @endif>
                        <div>
                            <b>{{ $kodeShp }}</b>
                            <small>{{ $shp->Tanggal ? \Carbon\Carbon::parse($shp->Tanggal)->format('d M Y') : '-' }} &middot; {{ $kodePo }}</small>
                            <div class="tags">
                                @if($kodeSj)
                                    <span class="tag ok">{{ $kodeSj }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <b>{{ $shp->poProduk->nama_pelanggan ?? 'Pelanggan' }}</b>
                            <small>
                                Penerima: {{ $sj->nama_penerima ?? ($shp->poProduk->nama_penerima ?? '-') }}
                                &middot; Kurir: {{ $sj->nama_kurir ?? '-' }}
                            </small>
                        </div>

                        <div>
                            <b>{{ $shp->poProduk ? number_format($shp->poProduk->jumlah_po) . ' pcs' : '-' }}</b>
                            <small>{{ $shp->bukti_serah_terima ? 'Bukti: ' . $shp->bukti_serah_terima : 'Belum ada bukti serah terima' }}</small>
                        </div>

                        <span class="st">{{ $shp->status_pengiriman }}</span>

                        <div class="act">
                            @if(!$isDone)
                                <button type="button" onclick="openUpdateShipmentModal({{ $shp->id_log_pengiriman }}, {{ \Illuminate\Support\Js::from($kodePo) }}, {{ \Illuminate\Support\Js::from($shp->status_pengiriman) }})">Update status</button>
                            @elseif($shp->bukti_serah_terima && (str_contains($shp->bukti_serah_terima, 'bukti_kirim/') || str_contains($shp->bukti_serah_terima, '.')))
                                <a href="{{ asset('storage/' . ltrim($shp->bukti_serah_terima, '/')) }}" target="_blank" rel="noopener">Lihat foto</a>
                            @else
                                <button type="button" onclick="openUpdateShipmentModal({{ $shp->id_log_pengiriman }}, {{ \Illuminate\Support\Js::from($kodePo) }}, {{ \Illuminate\Support\Js::from($shp->status_pengiriman) }})">Ubah bukti</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="empty-filter" hidden>
                <strong>Tidak ada pengiriman yang cocok</strong>
                Ubah kata kunci pencarian atau pilih status lain.
            </div>

            <div class="foot">
                <span id="cnt" data-cnt>Menampilkan {{ min(6, $shipments->count()) }} dari {{ $shipments->count() }} pengiriman</span>
                @if($shipments->count() > 6)
                    <button type="button" class="btn" data-expand="Lihat semua {{ $shipments->count() }} pengiriman" data-collapse="Tampilkan 6 pengiriman">Lihat semua {{ $shipments->count() }} pengiriman</button>
                @endif
            </div>
        @else
            <x-void-state
                title="Belum ada pengiriman berjalan"
                description="Setelah PO dipotong stoknya, terbitkan Surat Jalan untuk mulai melacak status pengantaran dan bukti serah terima."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-create-sj')">+ Terbitkan Surat Jalan</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>

    <div id="t-surat-jalan" hidden>
        @if($suratJalanList->isNotEmpty())
            <ul class="rows">
                @foreach($suratJalanList as $sj)
                    <li class="row sj ok">
                        <div>
                            <b>SJ-{{ str_pad($sj->no_ref, 4, '0', STR_PAD_LEFT) }}</b>
                            <small>{{ $sj->tgl_terbit ? \Carbon\Carbon::parse($sj->tgl_terbit)->format('d M Y') : '-' }}</small>
                        </div>
                        <div>
                            <b>{{ $sj->nama_penerima }}</b>
                            <small>Kurir: {{ $sj->nama_kurir }}</small>
                        </div>
                        <div>
                            <b>{{ $sj->poProduk->pluck('no_po_produk')->map(fn($n) => 'PO-' . str_pad($n, 4, '0', STR_PAD_LEFT))->implode(', ') ?: '-' }}</b>
                            <small>{{ $sj->ket ?: 'Tanpa catatan tambahan' }}</small>
                        </div>
                        <div class="act">
                            <span class="tag ok">Terbit</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-void-state
                title="Belum ada arsip Surat Jalan"
                description="Dokumen Surat Jalan resmi akan tersimpan di sini begitu diterbitkan untuk pengiriman PO pelanggan."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-create-sj')">+ Terbitkan Surat Jalan</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>
</section>

<!-- Modal Terbitkan Surat Jalan -->
<x-modal
    id="modal-create-sj"
    title="Terbitkan Surat Jalan baru"
    subtitle="Pilih Purchase Order berstatus STOK_DIPOTONG yang siap dikirimkan."
>
    <form action="{{ route('shipments.surat-jalan.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            @if($poActive->isEmpty())
                <div class="form-note is-warm">
                    Belum ada PO berstatus <b>STOK_DIPOTONG</b>. Lakukan potong stok terlebih dahulu di halaman Purchase Order.
                </div>
            @endif

            <div class="form-field">
                <label class="form-label" for="sj-select-po">Pilih PO Siap Kirim *</label>
                <select id="sj-select-po" name="no_po_produk" required class="form-input" onchange="prefillRecipientFromPo(this)">
                    <option value="">-- Pilih Purchase Order --</option>
                    @foreach($poActive as $po)
                        <option value="{{ $po->no_po_produk }}" data-penerima="{{ $po->nama_penerima ?: $po->nama_pelanggan }}">
                            PO-{{ str_pad($po->no_po_produk, 4, '0', STR_PAD_LEFT) }} &mdash; {{ $po->nama_pelanggan }} ({{ number_format($po->jumlah_po) }} pcs)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="sj-penerima">Nama Penerima *</label>
                    <input id="sj-penerima" type="text" name="nama_penerima" required placeholder="Nama penerima di tujuan" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="sj-tanggal">Tanggal Terbit *</label>
                    <input id="sj-tanggal" type="date" name="tgl_terbit" value="{{ date('Y-m-d') }}" required class="form-input">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="sj-kurir-input">Nama Kurir / Ekspedisi *</label>
                <input id="sj-kurir-input" type="text" name="nama_kurir" required placeholder="Misal: Kurir Toko / Paxel / JNE" class="form-input">
            </div>

            <div class="form-field">
                <label class="form-label" for="sj-catatan">Catatan Pengiriman (Opsional)</label>
                <input id="sj-catatan" type="text" name="ket" placeholder="Misal: 3 dus besar, fragile" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn" @if($poActive->isEmpty()) disabled @endif>Terbitkan Surat Jalan</button>
        </div>
    </form>
</x-modal>

<!-- Modal Update Status & Bukti Serah Terima -->
<x-modal
    id="modal-update-shipment"
    title="Update status &amp; bukti serah terima"
    subtitle="Catat perubahan status atau unggah bukti pengiriman barang."
    subtitleId="shp-modal-subtitle"
>
    <form id="form-update-shipment" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="shp-status-select">Status Pengiriman *</label>
                <select id="shp-status-select" name="status_pengiriman" required class="form-input">
                    <option value="DIPROSES">DIPROSES (Persiapan muat)</option>
                    <option value="DIKIRIM">DIKIRIM (Sedang di perjalanan)</option>
                    <option value="SELESAI">SELESAI (Diterima pelanggan)</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label" for="shp-bukti-file">Foto Bukti Serah Terima (Opsional, maks 2MB)</label>
                <input id="shp-bukti-file" type="file" name="bukti_serah_terima" accept="image/*" class="form-input">
            </div>

            <div class="form-field">
                <label class="form-label" for="shp-bukti-teks">Catatan Penerima / No. Resi (Opsional)</label>
                <input id="shp-bukti-teks" type="text" name="bukti_teks" placeholder="Misal: Diterima Ibu Sari pukul 14.20" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Simpan status</button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
    function prefillRecipientFromPo(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        var recipient = opt ? opt.getAttribute('data-penerima') : '';
        if (recipient) {
            document.getElementById('sj-penerima').value = recipient;
        }
    }

    function openUpdateShipmentModal(id, kodePo, currentStatus) {
        document.getElementById('form-update-shipment').action = '/shipments/update-status/' + id;
        document.getElementById('shp-modal-subtitle').textContent = 'Memperbarui pengiriman untuk ' + kodePo;
        var sel = document.getElementById('shp-status-select');
        sel.value = currentStatus === 'DIPROSES' ? 'DIKIRIM' : (currentStatus === 'DIKIRIM' ? 'SELESAI' : currentStatus);
        document.getElementById('shp-bukti-file').value = '';
        document.getElementById('shp-bukti-teks').value = '';
        openDialog('modal-update-shipment');
    }
</script>
@endpush
