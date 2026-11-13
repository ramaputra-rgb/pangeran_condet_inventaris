@extends('layouts.app')

@section('title', 'Pangeran Condet, Dasbor')

@section('content')
<section class="hero" aria-labelledby="judul">
    <h1 id="judul">
        @if(($poActiveCount ?? 0) > 0)
            {{ number_format($poActiveCount) }} pesanan menunggu dapur
        @else
            Semua pesanan selesai diproses
        @endif
    </h1>
    <p>
        @if(($totalBatchProduksi ?? 0) == 0 && ($totalStokFg ?? 0) == 0)
            Belum ada batch yang digoreng dan stok jadi masih 0, jadi semua PO harus lewat dapur dulu.
        @else
            Tercatat {{ number_format($totalBatchProduksi ?? 0) }} batch produksi dapur dengan stok jadi {{ number_format($totalStokFg ?? 0) }} pcs/pack siap kirim.
        @endif
        @if(($stokKritisCount ?? 0) > 0)
            Ada {{ $stokKritisCount }} bahan baku di bawah batas minimum, belanja sebelum mulai produksi.
        @else
            Semua bahan baku berada di atas batas minimum produksi.
        @endif
    </p>
    @if(($stokKritisCount ?? 0) > 0)
        <a class="btn" href="{{ route('raw-materials.index') }}">Belanja bahan baku</a>
    @else
        <a class="btn" href="{{ route('po-produk.index') }}">Kelola antrean PO</a>
    @endif

    <ol class="line" aria-label="Jalur produksi">
        <li>
            <div class="disc {{ ($stokKritisCount ?? 0) > 0 ? 'alert' : 'empty' }}">{{ $stokKritisCount ?? 0 }}</div>
            <b>Bahan baku</b>
            <span>di bawah batas minimum</span>
        </li>
        <li>
            <div class="disc {{ ($poActiveCount ?? 0) > 0 ? 'fill' : 'empty' }}">{{ $poActiveCount ?? 0 }}</div>
            <b>Dapur</b>
            <span>PO menunggu digoreng</span>
        </li>
        <li>
            <div class="disc {{ ($totalGradeA ?? 0) > 0 ? 'fill' : 'empty' }}">{{ number_format($totalGradeA ?? 0) }}</div>
            <b>QC Grade A</b>
            <span>pcs lolos, Grade B {{ number_format($totalGradeB ?? 0) }} pack</span>
        </li>
        <li>
            <div class="disc {{ ($totalStokFg ?? 0) > 0 ? 'fill' : 'empty' }}">{{ number_format($totalStokFg ?? 0) }}</div>
            <b>Gudang jadi</b>
            <span>pcs/pack siap kirim, Rp {{ number_format($totalNilaiAsetFg ?? 0, 0, ',', '.') }}</span>
        </li>
        <li>
            <div class="disc {{ ($pengirimanProses ?? 0) > 0 ? 'fill' : 'empty' }}">{{ $pengirimanProses ?? 0 }}</div>
            <b>Kurir</b>
            <span>sedang di perjalanan</span>
        </li>
    </ol>
</section>

<div class="grid">
    <section class="stock" aria-labelledby="h-stok">
        <h2 id="h-stok">Bahan yang harus dibeli</h2>
        <p class="sub">
            @if(($stokKritisCount ?? 0) > 0)
                Enam yang paling tipis dari {{ $stokKritisCount }} bahan. Batas minimum 10 unit.
            @else
                Seluruh bahan baku mencukupi di atas batas minimum 10 unit.
            @endif
        </p>
        <ul>
            @forelse(($lowStockMaterials ?? collect()) as $mat)
                @php
                    $qtyVal = (float) $mat->qty_stok;
                    $pct = min(100, max(6, (int) round(($qtyVal / 10) * 100)));
                    $qtyLabel = rtrim(rtrim(number_format($qtyVal, 2, '.', ''), '0'), '.');
                @endphp
                <li>
                    <span>{{ $mat->nama_bahan }}</span>
                    <div class="bar"><i class="{{ $qtyVal > 3 ? 'mid' : '' }}" style="width:{{ $pct }}%"></i></div>
                    <em>{{ $qtyLabel }}</em>
                </li>
            @empty
                <li>
                    <span>Semua bahan aman</span>
                    <div class="bar"><i class="mid" style="width:100%"></i></div>
                    <em>OK</em>
                </li>
            @endforelse
        </ul>
        <a class="btn" href="{{ route('raw-materials.index') }}">Lihat semua bahan</a>
    </section>

    <section aria-labelledby="h-po">
        <div class="head">
            <h2 id="h-po">PO terbaru dari pelanggan</h2>
            <a href="{{ route('po-produk.index') }}">Lihat semua PO</a>
        </div>
        @php
            $latestThreePo = ($recentPoList ?? collect())->take(3);
            $allPendingRecent = $latestThreePo->isNotEmpty() && $latestThreePo->every(fn($p) => $p->status_po === 'PENDING');
        @endphp
        <p class="sub">
            @if($latestThreePo->isEmpty())
                Belum ada pesanan PO pelanggan masuk.
            @elseif($allPendingRecent)
                Semua masih menunggu diproses.
            @else
                Ringkasan {{ $latestThreePo->count() }} pesanan pelanggan terakhir.
            @endif
        </p>
        <div class="tickets">
            @forelse($latestThreePo as $po)
                @php
                    $poStatusText = match($po->status_po) {
                        'PENDING' => 'menunggu',
                        'PROSES' => 'proses',
                        'STOK_DIPOTONG' => 'siap kirim',
                        'DIKIRIM' => 'dikirim',
                        'SELESAI' => 'selesai',
                        default => strtolower($po->status_po),
                    };
                @endphp
                <article class="tk">
                    <div>
                        <h3>{{ $po->nama_pelanggan }}</h3>
                        <p>PO-00{{ $po->no_po_produk }}</p>
                    </div>
                    <div class="stub">
                        <b>{{ number_format($po->jumlah_po) }}</b>
                        <span>pcs, {{ $poStatusText }}</span>
                    </div>
                </article>
            @empty
                <x-void-state
                    title="Belum ada pesanan PO masuk"
                    description="Pesanan pelanggan baru akan muncul di sini."
                />
            @endforelse
        </div>
    </section>
</div>

@if(($recentLogProduksi ?? collect())->isEmpty())
    <section class="batch" aria-labelledby="h-batch">
        <div>
            <strong id="h-batch">Belum ada batch produksi</strong>
            <p>Batch yang dicatat dapur akan muncul di sini lengkap dengan hasil Grade A dan Grade B.</p>
        </div>
        <a class="btn" href="{{ route('log-produksi.index') }}">Catat batch pertama</a>
    </section>
@else
    <section class="panel" aria-labelledby="h-batch">
        <div class="head">
            <div>
                <h2 id="h-batch">Batch produksi dapur terbaru</h2>
                <p class="sub">Lima batch terakhir beserta hasil sortir Grade A dan Grade B.</p>
            </div>
            <a href="{{ route('log-produksi.index') }}">Lihat semua batch</a>
        </div>
        <ul class="rows">
            @foreach($recentLogProduksi as $log)
                @php
                    $isQcDone = strtoupper($log->status_approval) === 'APPROVED';
                @endphp
                <li class="row batch-row {{ $isQcDone ? 'ok' : 'mid' }}">
                    <div>
                        <b>BATCH-00{{ $log->no_batch }}</b>
                        <small>{{ \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') }}</small>
                    </div>
                    <div>
                        <b>{{ $log->poProduk->nama_pelanggan ?? ($log->no_po_produk ? 'PO #' . $log->no_po_produk : 'Stok Internal Gudang') }}</b>
                        <small>{{ $log->no_po_produk ? 'PO-00' . $log->no_po_produk : 'Produksi mandiri' }}</small>
                    </div>
                    <div class="qty">
                        {{ number_format($log->jml_grade_A) }} <small>pcs Grade A</small>
                    </div>
                    <span class="st">{{ $isQcDone ? 'Lolos QC' : 'Proses' }}</span>
                    <div class="act">
                        <span>Grade B: {{ number_format($log->jml_grade_B) }} pack</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
@endsection
