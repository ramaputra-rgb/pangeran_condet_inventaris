@extends('layouts.app')

@section('title', 'Pangeran Condet, Formula BoM')

@section('content')
<section class="masthead" aria-labelledby="j-bom">
    <div>
        <span class="eyebrow">Bill of Materials</span>
        <h1 id="j-bom">Master formula &amp; standar BoM</h1>
        <p>Pengaturan standar resep produksi rengginang dan overhead dapur untuk kalkulasi HPP serta pemotongan bahan otomatis.</p>
    </div>
    <div class="acts no-print">
        <button type="button" class="btn" onclick="openDialog('modal-bom')">+ Buat / edit formula BoM</button>
        <button type="button" class="btn outline" onclick="window.print()">Cetak resep</button>
    </div>
</section>

<section class="stub-grid" aria-label="Ringkasan formula BoM">
    <x-stub-card
        title="Formula terdaftar"
        subtitle="Resep master aktif dapur"
        :value="number_format($totalFormula ?? 0)"
        unit="formula"
    />
    <x-stub-card
        title="Standar batch goreng"
        subtitle="Acuan kalkulasi HPP"
        value="100"
        unit="pcs/batch"
        variant="warm"
    />
    <x-stub-card
        title="Bahan terhubung"
        subtitle="Sinkron ke modul gudang"
        :value="number_format($totalBahanBaku ?? 0)"
        unit="material"
    />
</section>

<section class="panel" aria-label="Katalog Formula BoM">
    <div class="tabs" role="group" aria-label="Tampilan BoM">
        <button type="button" data-t="bom-katalog" aria-selected="true">Katalog formula BoM ({{ $products->count() ?? 0 }})</button>
    </div>

    <div id="t-bom-katalog" class="form-grid">
        @forelse($products as $produk)
            <article class="form-box">
                <div class="form-box-head">
                    <div>
                        <span class="tag ok">Kode: {{ $produk->kode_produk ?? ('PRD-' . ($produk->id_produk ?? $produk->id_produk_jadi ?? '-')) }}</span>
                        <h3 class="modal-section-title">{{ $produk->nama_produk }}</h3>
                        <p class="form-hint">{{ $produk->formulaBom->nama_formula ?? 'Belum ada nama formula acuan' }}</p>
                    </div>
                    <div class="qty">
                        {{ $produk->formulaBom->porsi_batch_pcs ?? 100 }} <small>pcs / batch</small>
                    </div>
                </div>

                <div class="modal-table-wrap">
                    <table class="nota-table">
                        <thead>
                            <tr>
                                <th>Jenis Komponen</th>
                                <th>Nama Komponen / Material</th>
                                <th class="ctr">Jumlah / Batch</th>
                                <th class="ctr">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($produk->formulaBom && $produk->formulaBom->detailFormula->count() > 0)
                                @foreach($produk->formulaBom->detailFormula as $detail)
                                    <tr>
                                        <td>
                                            <span class="tag {{ $detail->jenis_komponen === 'BAHAN_BAKU' ? 'ok' : '' }}">
                                                {{ $detail->jenis_komponen }}
                                            </span>
                                        </td>
                                        <td>{{ $detail->bahanBaku->nama_bahan ?? $detail->bahanBaku->nama_bahan_baku ?? $detail->jenis_komponen }}</td>
                                        <td class="ctr">
                                            @if($detail->satuan_dibutuhkan === 'Rupiah')
                                                Rp {{ number_format($detail->jumlah_dibutuhkan, 0, ',', '.') }}
                                            @else
                                                {{ number_format($detail->jumlah_dibutuhkan, 2) }}
                                            @endif
                                        </td>
                                        <td class="ctr">{{ $detail->satuan_dibutuhkan }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="ctr">Belum ada rincian formula BoM yang dikonfigurasi untuk produk ini.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <x-void-state
                title="Belum ada formula BoM aktif"
                description="Konfigurasikan formula resep produksi agar pemakaian bahan baku dan HPP per batch terhitung standar."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-bom')">+ Buat formula BoM</button>
                </x-slot:action>
            </x-void-state>
        @endforelse
    </div>
</section>

<!-- Modal Konfigurasi Formula BoM -->
<x-modal
    id="modal-bom"
    title="Konfigurasi header formula BoM"
    subtitle="Tentukan standar resep dan porsi batch penggorengan untuk produk jadi."
>
    <form action="{{ route('bom-recipes.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="bom-produk">Pilih Produk Jadi *</label>
                <select id="bom-produk" name="id_produk" required class="form-input">
                    @foreach($products as $prod)
                        <option value="{{ $prod->id_produk ?? $prod->id_produk_jadi ?? $prod->id }}">
                            {{ $prod->nama_produk }} ({{ $prod->kode_produk ?? ('PRD-' . ($prod->id_produk ?? $prod->id_produk_jadi ?? '')) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label class="form-label" for="bom-nama">Nama / Judul Formula Resep *</label>
                <input id="bom-nama" type="text" name="nama_formula" required placeholder="Misal: Resep Standar Penggorengan 100 Pcs" class="form-input">
            </div>

            <div class="form-field">
                <label class="form-label" for="bom-porsi">Porsi Batch Acuan (Pcs) *</label>
                <input id="bom-porsi" type="number" name="porsi_batch_pcs" value="100" min="1" required class="form-input">
            </div>

            <div class="form-field">
                <label class="form-label" for="bom-ket">Keterangan Catatan Dapur</label>
                <textarea id="bom-ket" name="keterangan" rows="2" placeholder="Formula acuan HPP penggorengan dapur Condet..." class="form-input"></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Simpan formula master</button>
        </div>
    </form>
</x-modal>
@endsection
