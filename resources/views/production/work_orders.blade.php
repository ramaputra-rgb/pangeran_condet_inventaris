@extends('layouts.app')

@section('title', 'Pangeran Condet, Work Order SPK')

@section('content')
<section class="masthead" aria-labelledby="j-wo">
    <div>
        <span class="eyebrow">Work Order &amp; SPK Dapur</span>
        <h1 id="j-wo">Perintah kerja penggorengan</h1>
        <p>Pengendalian produksi harian dapur Condet. Penerbitan SPK otomatis menghitung kebutuhan bahan sesuai resep standar BoM.</p>
    </div>
    <div class="acts no-print">
        <button type="button" class="btn" onclick="openDialog('modal-spk')">+ Terbitkan SPK dapur</button>
        <button type="button" class="btn outline" onclick="window.print()">Cetak antrean</button>
    </div>
</section>

<section class="stub-grid" aria-label="Ringkasan Work Order">
    <x-stub-card
        title="SPK dalam proses"
        subtitle="Penggorengan berjalan"
        :value="number_format($spkProsesCount ?? 0)"
        unit="SPK aktif"
        variant="warm"
    />
    <x-stub-card
        title="Target produksi berjalan"
        subtitle="Akumulasi target batch aktif"
        :value="number_format($totalTargetBatch ?? 0)"
        unit="pcs"
    />
    <x-stub-card
        title="SPK selesai serah terima"
        subtitle="Masuk ke stok produk jadi"
        :value="number_format($spkSelesaiCount ?? 0)"
        unit="SPK selesai"
    />
</section>

<section class="panel" aria-label="Daftar Work Order SPK">
    <div class="tabs" role="group" aria-label="Tampilan SPK">
        <button type="button" data-t="spk-list" aria-selected="true">Daftar SPK dapur aktif ({{ $workOrders->count() ?? 0 }})</button>
    </div>

    <div id="t-spk-list">
        @if(($workOrders ?? collect())->isNotEmpty())
            <ul class="rows">
                @foreach($workOrders as $spk)
                    @php
                        $isDone = $spk->status_spk === 'SELESAI';
                    @endphp
                    <li class="row batch-row {{ $isDone ? 'ok' : 'mid' }}">
                        <div>
                            <b>{{ $spk->no_spk }}</b>
                            <small>{{ \Carbon\Carbon::parse($spk->tanggal_spk)->format('d M Y') }}</small>
                        </div>
                        <div>
                            <b>{{ $spk->produk->nama_produk ?? '-' }}</b>
                            <small>Kode: {{ $spk->produk->kode_produk ?? '-' }}</small>
                        </div>
                        <div class="qty">
                            {{ number_format($spk->target_produksi_pcs) }} <small>pcs</small>
                        </div>
                        <span class="st">{{ $isDone ? 'Selesai' : ($spk->status_spk === 'PROSES' ? 'Digoreng' : 'Draft') }}</span>
                        <div class="act no-print">
                            <a href="{{ route('finished-goods.index') }}">Serah terima FG</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-void-state
                title="Belum ada SPK dapur aktif"
                description="Terbitkan SPK baru untuk memulai batch penggorengan dan memotong stok bahan baku sesuai formula BoM."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-spk')">+ Terbitkan SPK dapur</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>
</section>

<!-- Modal Terbitkan SPK Dapur Baru -->
<x-modal
    id="modal-spk"
    title="Terbitkan SPK penggorengan dapur"
    subtitle="Stok bahan baku akan terpotong sesuai acuan BoM produk terkait."
>
    <form action="{{ route('work-orders.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="spk-produk">Pilih Produk Varian *</label>
                <select id="spk-produk" name="id_produk" required class="form-input">
                    <option value="" disabled selected>-- Pilih Produk --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id_produk ?? $prod->id_produk_jadi ?? $prod->id }}">
                            {{ $prod->nama_produk }} (Kode: {{ $prod->kode_produk ?? ('PRD-' . ($prod->id_produk ?? $prod->id_produk_jadi ?? '')) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="spk-target">Target Jumlah (Pcs) *</label>
                    <input id="spk-target" type="number" name="target_produksi_pcs" min="1" required placeholder="Misal: 500" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="spk-tanggal">Tanggal SPK *</label>
                    <input id="spk-tanggal" type="date" name="tanggal_spk" value="{{ date('Y-m-d') }}" required class="form-input">
                </div>
            </div>

            <p class="form-hint">Stok bahan baku dan kemasan akan terpotong dari gudang sesuai formula BoM produk terpilih.</p>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Terbitkan SPK</button>
        </div>
    </form>
</x-modal>
@endsection
