@extends('layouts.app')

@section('title', 'Pangeran Condet, Pusat Laporan')

@section('content')
<section class="masthead" aria-labelledby="j-reports">
    <div>
        <span class="eyebrow">Pelaporan &amp; audit cetak</span>
        <h1 id="j-reports">Pusat laporan operasional</h1>
        <p>Filter dan cetak laporan stok produk jadi, pengadaan bahan baku supplier, log produksi QC, hingga penjualan Purchase Order beserta arsip log cetak.</p>
    </div>
    <div class="acts no-print">
        <button type="button" class="btn" onclick="openDialog('modal-filter-laporan')">+ Filter &amp; cetak laporan</button>
    </div>
</section>

<section class="stub-grid no-print" aria-label="Ringkasan audit cetak">
    <x-stub-card
        title="Total dokumen tercetak"
        subtitle="Tersimpan di sistem audit"
        :value="number_format($logCetakList->count() ?? 0)"
        unit="dokumen"
    />
    <x-stub-card
        title="Rekam jejak cetak"
        subtitle="Audit trail operasional"
        value="Aktif"
        unit="real-time"
        variant="warm"
    />
    <x-stub-card
        title="Format arsip"
        subtitle="Pratinjau &amp; cetak PDF/kertas"
        value="Siap"
        unit="4 modul"
    />
</section>

<section class="panel" aria-label="Log Riwayat Pencetakan Laporan">
    <div class="tabs" role="group" aria-label="Tampilan laporan">
        <button type="button" data-t="log-cetak" aria-selected="true">Riwayat cetak laporan ({{ $logCetakList->count() ?? 0 }})</button>
    </div>

    <div id="t-log-cetak">
        @if(($logCetakList ?? collect())->isNotEmpty())
            <ul class="rows">
                @foreach($logCetakList as $log)
                    <li class="row sj ok">
                        <div>
                            <b>LOG-00{{ $log->id_log_cetak }}</b>
                            <small>{{ \Carbon\Carbon::parse($log->tgl_cetak)->format('d M Y H:i') }}</small>
                        </div>
                        <div>
                            <b>{{ ucfirst($log->jenis_laporan) }}</b>
                            <small>{{ $log->kriteria_filter }}</small>
                        </div>
                        <div>
                            <span class="st">{{ $log->status_cetak }}</span>
                        </div>
                        <div class="act no-print">
                            <button type="button" onclick="window.print()">Cetak ulang</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-void-state
                title="Belum ada riwayat cetak laporan"
                description="Gunakan tombol Filter &amp; cetak laporan untuk membuat pratinjau laporan stok, pembelian, produksi, atau penjualan."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-filter-laporan')">+ Filter &amp; cetak laporan</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>
</section>

<!-- Modal Filter & Cetak Laporan -->
<x-modal
    id="modal-filter-laporan"
    title="Filter &amp; pratinjau cetak laporan"
    subtitle="Pilih modul sub-laporan dan rentang tanggal untuk mencetak dokumen rekap."
>
    <form action="{{ route('reports.preview') }}" method="POST" target="_blank">
        @csrf
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="rep-jenis">Pilih Jenis Sub-Laporan *</label>
                <select id="rep-jenis" name="jenis_laporan" required class="form-input">
                    <option value="stok">Laporan Stok &amp; Varian Produk Jadi</option>
                    <option value="pengadaan">Laporan Pengadaan / Pembelian Bahan</option>
                    <option value="produksi">Laporan Log Produksi &amp; QC Dapur</option>
                    <option value="penjualan">Laporan PO &amp; Penjualan Pelanggan</option>
                </select>
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="rep-mulai">Periode Mulai</label>
                    <input id="rep-mulai" type="date" name="periode_mulai" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="rep-selesai">Periode Selesai</label>
                    <input id="rep-selesai" type="date" name="periode_selesai" class="form-input">
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Proses &amp; cetak laporan</button>
        </div>
    </form>
</x-modal>
@endsection
