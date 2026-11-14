@extends('layouts.app')

@section('title', 'Pangeran Condet, Pusat Laporan')

@section('content')
@php
    $logs = $logCetakList ?? collect();
    $stokCount = $logs->where('jenis_laporan', 'stok')->count();
    $pengadaanCount = $logs->where('jenis_laporan', 'pengadaan')->count();
    $produksiCount = $logs->where('jenis_laporan', 'produksi')->count();
    $penjualanCount = $logs->where('jenis_laporan', 'penjualan')->count();
    $pengirimanCount = $logs->where('jenis_laporan', 'pengiriman')->count();
    $cetakHariIni = $logs->filter(fn ($item) => !empty($item->tgl_cetak) && \Carbon\Carbon::parse($item->tgl_cetak)->isToday())->count();
@endphp

<section class="masthead no-print" aria-labelledby="j-reports">
    <div>
        <span class="eyebrow">Pelaporan & audit cetak</span>
        <h1 id="j-reports">Pusat laporan operasional</h1>
        <p>Filter dan cetak laporan stok produk jadi, pengadaan bahan baku, log produksi QC, penjualan PO, hingga pengiriman Surat Jalan beserta arsip log cetak.</p>
    </div>
    <div class="acts">
        <button type="button" class="btn" onclick="openFilterModal()">+ Filter & cetak laporan</button>
    </div>
</section>

@if(!empty($previewActive))
<section class="panel print-sheet" id="panel-pratinjau-laporan" aria-label="Pratinjau Dokumen Laporan">
    <div class="print-controls no-print">
        <div>
            <span class="tag ok">Siap Cetak / Simpan PDF</span>
            <small style="margin-left: .5rem; color: var(--ink-3, #6b7280);">
                Tercatat sebagai <b>LOG-{{ str_pad($logAktif->id_log_cetak ?? 0, 4, '0', STR_PAD_LEFT) }}</b>
            </small>
        </div>
        <div class="print-btn-group">
            <button type="button" class="btn outline" onclick="openFilterModal()">Ubah filter</button>
            <a href="{{ route('reports.index') }}" class="btn outline">Tutup pratinjau</a>
            <button type="button" class="btn" onclick="window.print()">🖨️ Cetak dokumen</button>
        </div>
    </div>

    <div class="doc-header">
        <div>
            <span class="eyebrow">UD Pangeran Jaya · Rengginang Ikan Pangeran Condet</span>
            <h2 class="doc-title">{{ $judulLaporan }}</h2>
            <p class="doc-meta">
                @if($jenis === 'stok')
                    Posisi Stok: <b>Aktual Real-Time</b>
                @else
                    Periode: <b>{{ \Carbon\Carbon::parse($mulai)->format('d M Y') }}</b>
                    s/d
                    <b>{{ \Carbon\Carbon::parse($selesai)->format('d M Y') }}</b>
                @endif
                · Total Data: <b>{{ $dataLaporan->count() }} baris</b>
            </p>
        </div>
        <div class="doc-stamp">
            <div>No. Audit: <b>LOG-{{ str_pad($logAktif->id_log_cetak ?? 0, 4, '0', STR_PAD_LEFT) }}</b></div>
            <div>Dicetak: <b>{{ now()->format('d/m/Y H:i') }} WIB</b></div>
            <div>Oleh: <b>Owner Pangeran Condet</b></div>
        </div>
    </div>

    <div class="doc-table-wrap">
        @if($jenis === 'stok')
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 3rem;">No.</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk & Varian</th>
                        <th>Kemasan / Satuan</th>
                        <th class="num">Stok Tersedia</th>
                        <th>Status Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataLaporan as $idx => $item)
                        @php
                            $idProd = $item->id_produk ?? $item->id_produk_jadi ?? $item->id ?? ($idx + 1);
                            $stok = (float) ($item->qty_stok ?? $item->stok ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><b>PRD-{{ str_pad($idProd, 4, '0', STR_PAD_LEFT) }}</b></td>
                            <td>{{ $item->nama_produk ?? $item->varian ?? '-' }}</td>
                            <td>{{ $item->ukuran ?? $item->satuan ?? 'pcs' }}</td>
                            <td class="num"><b>{{ number_format($stok) }}</b></td>
                            <td>{{ $stok <= 10 ? 'Stok Menipis' : 'Aman' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-cell">Tidak ada data produk jadi pada sistem.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($dataLaporan->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="4" class="num">Total Akumulasi Stok Produk Jadi</th>
                        <th class="num">{{ number_format($dataLaporan->sum(fn($r) => (float) ($r->qty_stok ?? $r->stok ?? 0))) }}</th>
                        <th>{{ $dataLaporan->count() }} Varian</th>
                    </tr>
                </tfoot>
                @endif
            </table>

        @elseif($jenis === 'pengadaan')
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 3rem;">No.</th>
                        <th>Tanggal</th>
                        <th>Supplier / Toko</th>
                        <th>Bahan Baku</th>
                        <th class="num">Qty</th>
                        <th class="num">Ongkir</th>
                        <th class="num">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataLaporan as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td><b>{{ $item->nama_supplier }}</b></td>
                            <td>{{ $item->bahanProduksi->nama_bahan ?? ('Bahan #' . $item->id_bahan) }}</td>
                            <td class="num">{{ number_format((float) $item->total_qty) }} {{ $item->bahanProduksi->ukuran ?? '' }}</td>
                            <td class="num">Rp {{ number_format((float) ($item->total_ongkir ?? 0), 0, ',', '.') }}</td>
                            <td class="num"><b>Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</b></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">Tidak ada transaksi pembelian bahan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($dataLaporan->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="4" class="num">Total Pengadaan Bahan</th>
                        <th class="num">{{ number_format($dataLaporan->sum('total_qty')) }}</th>
                        <th class="num">Rp {{ number_format($dataLaporan->sum('total_ongkir'), 0, ',', '.') }}</th>
                        <th class="num">Rp {{ number_format($dataLaporan->sum('total_harga'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>

        @elseif($jenis === 'produksi')
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 3rem;">No.</th>
                        <th>Kode Batch</th>
                        <th>Tanggal Produksi</th>
                        <th>Referensi PO</th>
                        <th>Detail Hasil Produk</th>
                        <th class="num">Total Output</th>
                        <th>Status QC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataLaporan as $idx => $item)
                        @php
                            $idBatch = $item->id_log_produksi ?? $item->id ?? ($idx + 1);
                            $details = $item->detailProduksi ?? collect();
                            $outputQty = $details->isNotEmpty()
                                ? $details->sum(fn($d) => (float) ($d->jumlah_hasil ?? $d->qty_produksi ?? $d->qty ?? 0))
                                : (float) ($item->jumlah_produksi ?? $item->qty_hasil ?? 0);
                            $rincianNama = $details->map(function ($d) {
                                $nama = $d->produkJadi->nama_produk ?? $d->produkJadi->varian ?? 'Produk';
                                $q = $d->jumlah_hasil ?? $d->qty_produksi ?? $d->qty ?? 0;
                                return $nama . ' (' . number_format($q) . ')';
                            })->implode(', ');
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><b>PRD-B{{ str_pad($idBatch, 3, '0', STR_PAD_LEFT) }}</b></td>
                            <td>{{ $item->tgl_produksi ? \Carbon\Carbon::parse($item->tgl_produksi)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->no_po_produk ? 'PO-' . str_pad($item->no_po_produk, 4, '0', STR_PAD_LEFT) : 'Stok Reguler' }}</td>
                            <td>{{ $rincianNama ?: '-' }}</td>
                            <td class="num"><b>{{ number_format($outputQty) }} pcs</b></td>
                            <td>{{ $item->status_qc ?? $item->status ?? 'SELESAI' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">Tidak ada catatan log produksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($jenis === 'penjualan')
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 3rem;">No.</th>
                        <th>No. PO</th>
                        <th>Tanggal PO</th>
                        <th>Pelanggan</th>
                        <th>Rincian Produk</th>
                        <th class="num">Jumlah Pesanan</th>
                        <th>Status PO</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataLaporan as $idx => $item)
                        @php
                            $alokasi = $item->detailAlokasi ?? collect();
                            $rincianPo = $alokasi->map(function ($a) {
                                $nama = $a->produkJadi->nama_produk ?? $a->produkJadi->varian ?? 'Produk';
                                $q = $a->qty_alokasi ?? $a->jumlah ?? $a->qty ?? 0;
                                return $nama . ' (' . number_format($q) . ')';
                            })->implode(', ');
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><b>PO-{{ str_pad($item->no_po_produk, 4, '0', STR_PAD_LEFT) }}</b></td>
                            <td>{{ $item->tgl_po ? \Carbon\Carbon::parse($item->tgl_po)->format('d/m/Y') : '-' }}</td>
                            <td><b>{{ $item->nama_pelanggan }}</b></td>
                            <td>{{ $rincianPo ?: '-' }}</td>
                            <td class="num"><b>{{ number_format((float) $item->jumlah_po) }} pcs</b></td>
                            <td>{{ $item->status_po ?? $item->status ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">Tidak ada data Purchase Order pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($dataLaporan->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="5" class="num">Total Volume Pesanan Pelanggan</th>
                        <th class="num">{{ number_format($dataLaporan->sum('jumlah_po')) }} pcs</th>
                        <th>{{ $dataLaporan->count() }} PO</th>
                    </tr>
                </tfoot>
                @endif
            </table>

        @elseif($jenis === 'pengiriman')
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 3rem;">No.</th>
                        <th>Kode Kirim & SJ</th>
                        <th>Tanggal Kirim</th>
                        <th>No. PO & Pelanggan</th>
                        <th>Penerima & Kurir</th>
                        <th class="num">Volume</th>
                        <th>Status & Bukti Terima</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataLaporan as $idx => $item)
                        @php
                            $kodeShp = 'SHP-' . str_pad($item->id_log_pengiriman, 4, '0', STR_PAD_LEFT);
                            $kodePo = 'PO-' . str_pad($item->no_po_produk, 4, '0', STR_PAD_LEFT);
                            $sj = optional($item->poProduk)->suratJalan;
                            $kodeSj = $sj ? ('SJ-' . str_pad($sj->no_ref, 4, '0', STR_PAD_LEFT)) : 'Tanpa SJ';
                            $tglKirim = $item->Tanggal
                                ? \Carbon\Carbon::parse($item->Tanggal)->format('d/m/Y')
                                : ($item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-');
                            $penerima = $sj->nama_penerima ?? ($item->poProduk->nama_penerima ?? '-');
                            $kurir = $sj->nama_kurir ?? '-';
                            $qtyKirim = (float) ($item->poProduk->jumlah_po ?? 0);
                            $bukti = $item->bukti_serah_terima;
                            $buktiLabel = !$bukti
                                ? 'Belum ada bukti'
                                : ((str_contains($bukti, 'uploads/') || str_contains($bukti, 'bukti_kirim/') || preg_match('/\.(jpg|jpeg|png|webp)$/i', $bukti))
                                    ? 'Foto bukti terlampir'
                                    : $bukti);
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <b>{{ $kodeShp }}</b><br>
                                <small>{{ $kodeSj }}</small>
                            </td>
                            <td>{{ $tglKirim }}</td>
                            <td>
                                <b>{{ $kodePo }}</b><br>
                                <small>{{ $item->poProduk->nama_pelanggan ?? 'Pelanggan' }}</small>
                            </td>
                            <td>
                                <b>{{ $penerima }}</b><br>
                                <small>Kurir: {{ $kurir }}</small>
                            </td>
                            <td class="num"><b>{{ $qtyKirim > 0 ? number_format($qtyKirim) . ' pcs' : '-' }}</b></td>
                            <td>
                                <b>{{ $item->status_pengiriman }}</b><br>
                                <small>{{ $buktiLabel }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">Tidak ada data pengiriman pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($dataLaporan->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="5" class="num">Total Volume Barang Terkirim</th>
                        <th class="num">{{ number_format($dataLaporan->sum(fn($r) => (float) ($r->poProduk->jumlah_po ?? 0))) }} pcs</th>
                        <th>{{ $dataLaporan->count() }} Pengiriman</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        @endif
    </div>

    <div class="doc-sign">
        <div>
            <p>Disiapkan oleh,</p>
            <div class="sign-line">Admin Operasional & Gudang</div>
        </div>
        <div>
            <p>Jakarta, {{ now()->format('d F Y') }}<br>Mengetahui,</p>
            <div class="sign-line">Owner Pangeran Condet</div>
        </div>
    </div>
</section>
@endif

<section class="stub-grid no-print" aria-label="Ringkasan audit cetak">
    <x-stub-card
        title="Total dokumen tercetak"
        subtitle="Tersimpan di sistem audit"
        :value="number_format($logs->count())"
        unit="dokumen"
    />
    <x-stub-card
        title="Cetak hari ini"
        subtitle="Aktivitas audit cetak harian"
        :value="number_format($cetakHariIni)"
        unit="cetak"
        variant="warm"
    />
    <x-stub-card
        title="Sub-laporan tersedia"
        subtitle="Stok, pengadaan, produksi, PO, kirim"
        value="5"
        unit="modul"
    />
</section>

<section class="panel no-print" id="panel-laporan" aria-label="Log Riwayat Pencetakan Laporan">
    <div class="tabs" role="group" aria-label="Tampilan laporan">
        <button type="button" data-t="log-cetak" aria-selected="true">Riwayat cetak laporan ({{ $logs->count() }})</button>
    </div>

    <div id="t-log-cetak" data-filter-box data-total="{{ $logs->count() }}" data-unit="dokumen">
        <div class="controls">
            <input
                type="search"
                class="field grow"
                data-search
                placeholder="Cari kode log, jenis laporan, atau rentang periode..."
                aria-label="Cari riwayat cetak laporan"
            >
            <button type="button" class="reset-btn" data-reset>Reset</button>
        </div>

        <div class="chips">
            <button type="button" data-f="semua" aria-pressed="true">Semua ({{ $logs->count() }})</button>
            <button type="button" data-f="stok" aria-pressed="false">Stok ({{ $stokCount }})</button>
            <button type="button" data-f="pengadaan" aria-pressed="false">Pengadaan ({{ $pengadaanCount }})</button>
            <button type="button" data-f="produksi" aria-pressed="false">Produksi ({{ $produksiCount }})</button>
            <button type="button" data-f="penjualan" aria-pressed="false">Penjualan ({{ $penjualanCount }})</button>
            <button type="button" data-f="pengiriman" aria-pressed="false">Pengiriman ({{ $pengirimanCount }})</button>
            <p>Setiap pratinjau cetak otomatis dicatat ke log audit sistem.</p>
        </div>

        @if($logs->isNotEmpty())
            <ul class="rows">
                @foreach($logs as $log)
                    @php
                        $kodeLog = 'LOG-' . str_pad($log->id_log_cetak, 4, '0', STR_PAD_LEFT);
                        $jenisKey = strtolower($log->jenis_laporan);
                        $parsedMulai = '';
                        $parsedSelesai = '';
                        if (preg_match('/Periode:\s*(\d{4}-\d{2}-\d{2})\s*s\/d\s*(\d{4}-\d{2}-\d{2})/', $log->kriteria_filter ?? '', $m)) {
                            $parsedMulai = $m[1];
                            $parsedSelesai = $m[2];
                        }
                        $canDirectReprint = $jenisKey === 'stok' || ($parsedMulai !== '' && $parsedSelesai !== '');
                    @endphp
                    <li class="row sj ok"
                        data-s="{{ $jenisKey }}"
                        data-q="{{ strtolower($kodeLog . ' ' . $jenisKey . ' ' . ($log->kriteria_filter ?? '')) }}"
                        @if($loop->index >= 6) data-more="1" hidden @endif>
                        <div>
                            <b>{{ $kodeLog }}</b>
                            <small>{{ \Carbon\Carbon::parse($log->tgl_cetak)->format('d M Y H:i') }}</small>
                        </div>
                        <div>
                            <b>Laporan {{ ucfirst($log->jenis_laporan) }}</b>
                            <small>{{ $log->kriteria_filter }}</small>
                        </div>
                        <div>
                            <span class="tag ok">{{ $log->status_cetak }}</span>
                        </div>
                        <div class="act">
                            @if($canDirectReprint)
                                <form action="{{ route('reports.preview') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="jenis_laporan" value="{{ $jenisKey }}">
                                    <input type="hidden" name="periode_mulai" value="{{ $parsedMulai }}">
                                    <input type="hidden" name="periode_selesai" value="{{ $parsedSelesai }}">
                                    <button type="submit">Buka & cetak</button>
                                </form>
                            @else
                                <button type="button" onclick="openFilterModalWithPreset('{{ $jenisKey }}')">Pilih periode & cetak</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="empty-filter" hidden>
                <strong>Tidak ada riwayat cetak yang cocok</strong>
                Ubah kata kunci pencarian atau pilih kategori laporan lain.
            </div>

            <div class="foot">
                <span id="cnt" data-cnt>Menampilkan {{ min(6, $logs->count()) }} dari {{ $logs->count() }} dokumen</span>
                @if($logs->count() > 6)
                    <button type="button" class="btn" data-expand="Lihat semua {{ $logs->count() }} dokumen" data-collapse="Tampilkan 6 dokumen">Lihat semua {{ $logs->count() }} dokumen</button>
                @endif
            </div>
        @else
            <x-void-state
                title="Belum ada riwayat cetak laporan"
                description="Gunakan tombol Filter & cetak laporan untuk membuat pratinjau laporan stok, pembelian, produksi, penjualan, atau pengiriman."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openFilterModal()">+ Filter & cetak laporan</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>
</section>

<!-- Modal Filter & Cetak Laporan -->
<x-modal
    id="modal-filter-laporan"
    title="Filter & pratinjau cetak laporan"
    subtitle="Pilih modul sub-laporan dan rentang tanggal untuk menampilkan pratinjau dokumen rekap."
>
    <form action="{{ route('reports.preview') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="rep-jenis">Pilih Jenis Sub-Laporan *</label>
                <select id="rep-jenis" name="jenis_laporan" required class="form-input" onchange="togglePeriodeFields()">
                    <option value="stok" @selected(old('jenis_laporan', $jenis ?? 'stok') === 'stok')>Laporan Stok & Varian Produk Jadi</option>
                    <option value="pengadaan" @selected(old('jenis_laporan', $jenis ?? '') === 'pengadaan')>Laporan Pengadaan / Pembelian Bahan</option>
                    <option value="produksi" @selected(old('jenis_laporan', $jenis ?? '') === 'produksi')>Laporan Log Produksi & QC Dapur</option>
                    <option value="penjualan" @selected(old('jenis_laporan', $jenis ?? '') === 'penjualan')>Laporan PO & Penjualan Pelanggan</option>
                    <option value="pengiriman" @selected(old('jenis_laporan', $jenis ?? '') === 'pengiriman')>Laporan Log Pengiriman & Surat Jalan</option>
                </select>
            </div>

            <div id="rep-stok-note" class="form-note">
                Laporan stok menampilkan kondisi ketersediaan produk jadi di gudang saat ini secara langsung, sehingga tidak memerlukan filter rentang tanggal.
            </div>

            <div id="rep-periode-group" class="form-grid two" hidden>
                <div class="form-field">
                    <label class="form-label" for="rep-mulai">Periode Mulai *</label>
                    <input id="rep-mulai" type="date" name="periode_mulai" value="{{ old('periode_mulai', $mulai ?? '') }}" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="rep-selesai">Periode Selesai *</label>
                    <input id="rep-selesai" type="date" name="periode_selesai" value="{{ old('periode_selesai', $selesai ?? '') }}" class="form-input">
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Tampilkan pratinjau laporan</button>
        </div>
    </form>
</x-modal>

<style>
.print-sheet {
    background: #ffffff;
    border: 1px solid var(--line, #e5e7eb);
}
.print-controls {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px dashed var(--line, #e5e7eb);
}
.print-btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.doc-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid var(--ink, #1f2937);
}
.doc-title {
    margin: .2rem 0 .35rem;
    font-size: 1.35rem;
    line-height: 1.2;
}
.doc-meta {
    margin: 0;
    font-size: .85rem;
    color: var(--ink-2, #4b5563);
}
.doc-stamp {
    font-size: .8rem;
    line-height: 1.6;
    text-align: right;
    color: var(--ink-2, #4b5563);
}
.doc-table-wrap {
    width: 100%;
    overflow-x: auto;
}
.doc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .85rem;
}
.doc-table th,
.doc-table td {
    padding: .625rem .75rem;
    border: 1px solid var(--line, #d1d5db);
    text-align: left;
    vertical-align: top;
}
.doc-table thead th,
.doc-table tfoot th {
    background: var(--paper-2, #f9fafb);
    font-weight: 700;
}
.doc-table .num {
    text-align: right;
    white-space: nowrap;
}
.doc-table .empty-cell {
    text-align: center;
    padding: 1.5rem;
    color: var(--ink-3, #6b7280);
}
.doc-sign {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 2rem;
    margin-top: 2rem;
    font-size: .85rem;
    text-align: center;
}
.sign-line {
    margin-top: 3.5rem;
    padding-top: .4rem;
    border-top: 1px solid var(--ink, #1f2937);
    display: inline-block;
    min-width: 13rem;
    font-weight: 700;
}
@media print {
    body {
        background: #ffffff !important;
    }
    .app {
        display: block !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
    .print-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .doc-stamp {
        text-align: right;
    }
}
</style>
@endsection

@push('scripts')
<script>
    function togglePeriodeFields() {
        var jenisEl = document.getElementById('rep-jenis');
        var periodeGroup = document.getElementById('rep-periode-group');
        var stokNote = document.getElementById('rep-stok-note');
        var mulaiInput = document.getElementById('rep-mulai');
        var selesaiInput = document.getElementById('rep-selesai');

        if (!jenisEl || !periodeGroup) return;

        var isStok = jenisEl.value === 'stok';

        if (isStok) {
            periodeGroup.hidden = true;
            if (stokNote) stokNote.hidden = false;
            if (mulaiInput) {
                mulaiInput.required = false;
                mulaiInput.value = '';
            }
            if (selesaiInput) {
                selesaiInput.required = false;
                selesaiInput.value = '';
            }
        } else {
            periodeGroup.hidden = false;
            if (stokNote) stokNote.hidden = true;
            if (mulaiInput) mulaiInput.required = true;
            if (selesaiInput) selesaiInput.required = true;
        }
    }

    function openFilterModal() {
        togglePeriodeFields();
        openDialog('modal-filter-laporan');
    }

    function openFilterModalWithPreset(jenis) {
        var jenisEl = document.getElementById('rep-jenis');
        if (jenisEl && jenis) {
            jenisEl.value = jenis;
        }
        togglePeriodeFields();
        openDialog('modal-filter-laporan');
    }

    document.addEventListener('DOMContentLoaded', function () {
        togglePeriodeFields();
    });
</script>
@endpush