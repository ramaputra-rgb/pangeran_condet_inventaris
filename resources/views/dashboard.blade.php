@extends('layouts.app')

@section('title', 'Pangeran Condet, Dasbor')

@section('content')
@php
    $trendSeriesData = $trendSeries ?? [];
    $trendMax = max(1, (int) ($trendYMax ?? 10));
    $poStatusData = $poStatusChart ?? [];
    $poTotalCount = (int) ($totalPoAll ?? 0);
    $productDemandData = $productDemandChart ?? collect();
    $productScaleMax = max(1, (int) ($productMaxVal ?? 1));
    $materialData = $materialChart ?? collect();
    $materialMax = max(12, (int) ($materialYMax ?? 15));
@endphp

<section class="masthead" aria-labelledby="judul">
    <div>
        <span class="eyebrow">Ringkasan &amp; analitik</span>
        <h1 id="judul">Dasbor grafik operasional</h1>
        <p>
            @if(($poActiveCount ?? 0) > 0)
                Terdapat {{ number_format($poActiveCount) }} PO aktif, {{ number_format($totalStokFg ?? 0) }} pcs stok produk jadi, dan {{ number_format($stokKritisCount ?? 0) }} bahan baku di bawah batas minimum.
            @else
                Seluruh antrean PO telah selesai diproses dengan {{ number_format($totalStokFg ?? 0) }} pcs stok jadi dan {{ number_format($stokKritisCount ?? 0) }} bahan di bawah batas minimum.
            @endif
        </p>
    </div>
    <div class="acts">
        @if(($stokKritisCount ?? 0) > 0)
            <a class="btn" href="{{ route('raw-materials.index') }}">Belanja bahan baku</a>
        @else
            <a class="btn" href="{{ route('po-produk.index') }}">Kelola antrean PO</a>
        @endif
        <a class="btn outline" href="{{ route('log-produksi.index') }}">Catat batch produksi</a>
    </div>
</section>

<section class="stub-grid four" aria-label="Ringkasan metrik utama">
    <x-stub-card
        title="PO aktif"
        subtitle="Menunggu, proses, atau dikirim"
        :value="number_format($poActiveCount ?? 0)"
        unit="pesanan"
        :variant="($poActiveCount ?? 0) > 0 ? 'warm' : null"
    />
    <x-stub-card
        title="Bahan di bawah batas"
        subtitle="Stok &le; 10 unit (min. aman)"
        :value="number_format($stokKritisCount ?? 0)"
        unit="bahan"
        :variant="($stokKritisCount ?? 0) > 0 ? 'alert' : null"
    />
    <x-stub-card
        title="Lolos QC Grade A"
        subtitle="{{ number_format($totalBatchProduksi ?? 0) }} batch &middot; Grade B: {{ number_format($totalGradeB ?? 0) }}"
        :value="number_format($totalGradeA ?? 0)"
        unit="pcs"
    />
    <x-stub-card
        title="Stok produk jadi"
        subtitle="Aset Rp {{ number_format($totalNilaiAsetFg ?? 0, 0, ',', '.') }}"
        :value="number_format($totalStokFg ?? 0)"
        unit="pcs"
        variant="warm"
    />
</section>

<section class="dash-charts" aria-label="Grafik analitik operasional">
    {{-- Grafik 1: Tren Permintaan PO vs Output Produksi --}}
    <article class="chart-card" aria-labelledby="ch-trend">
        <div class="chart-head">
            <div>
                <h2 id="ch-trend">Tren PO &amp; output produksi</h2>
                <p class="sub">Perbandingan jumlah pesanan masuk dan hasil produksi per tanggal.</p>
            </div>
            <a class="chart-link" href="{{ route('log-produksi.index') }}">Log produksi</a>
        </div>

        <div class="chart-body">
            @php
                $svgW = 560;
                $svgH = 230;
                $padL = 42;
                $padR = 18;
                $padT = 18;
                $padB = 34;
                $plotW = $svgW - $padL - $padR;
                $plotH = $svgH - $padT - $padB;
                $baseY = $padT + $plotH;
                $nPts = max(1, count($trendSeriesData));
                $poCoords = [];
                $prodCoords = [];

                foreach ($trendSeriesData as $idx => $pt) {
                    $x = $nPts > 1 ? $padL + ($idx / ($nPts - 1)) * $plotW : $padL + ($plotW / 2);
                    $yPo = $baseY - (($pt['poQty'] / $trendMax) * $plotH);
                    $yProd = $baseY - (($pt['prodQty'] / $trendMax) * $plotH);
                    $poCoords[] = ['x' => round($x, 1), 'y' => round($yPo, 1), 'pt' => $pt];
                    $prodCoords[] = ['x' => round($x, 1), 'y' => round($yProd, 1), 'pt' => $pt];
                }

                $poLinePts = collect($poCoords)->map(fn($c) => $c['x'] . ',' . $c['y'])->implode(' ');
                $prodLinePts = collect($prodCoords)->map(fn($c) => $c['x'] . ',' . $c['y'])->implode(' ');

                $poAreaPath = '';
                $prodAreaPath = '';
                if (count($poCoords) >= 2) {
                    $firstX = $poCoords[0]['x'];
                    $lastX = $poCoords[count($poCoords) - 1]['x'];
                    $poAreaPath = "M {$firstX},{$baseY} " . collect($poCoords)->map(fn($c) => "L {$c['x']},{$c['y']}")->implode(' ') . " L {$lastX},{$baseY} Z";
                    $prodAreaPath = "M {$firstX},{$baseY} " . collect($prodCoords)->map(fn($c) => "L {$c['x']},{$c['y']}")->implode(' ') . " L {$lastX},{$baseY} Z";
                }
            @endphp

            <svg class="chart-svg" viewBox="0 0 {{ $svgW }} {{ $svgH }}" role="img" aria-label="Grafik garis tren permintaan PO dan output produksi">
                @for($step = 0; $step <= 4; $step++)
                    @php
                        $val = (int) round(($trendMax / 4) * $step);
                        $gy = round($baseY - ($step / 4) * $plotH, 1);
                    @endphp
                    <line class="{{ $step === 0 ? 'chart-axis-line' : 'chart-grid-line' }}" x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $svgW - $padR }}" y2="{{ $gy }}" />
                    <text class="chart-axis-text" x="{{ $padL - 8 }}" y="{{ $gy + 4 }}" text-anchor="end">{{ number_format($val) }}</text>
                @endfor

                @if($poAreaPath !== '')
                    <path class="chart-area-po" d="{{ $poAreaPath }}" />
                    <path class="chart-area-prod" d="{{ $prodAreaPath }}" />
                @endif

                @if(count($poCoords) >= 2)
                    <polyline class="chart-line-po" points="{{ $poLinePts }}" />
                    <polyline class="chart-line-prod" points="{{ $prodLinePts }}" />
                @endif

                @foreach($poCoords as $idx => $c)
                    @php
                        $pt = $c['pt'];
                        $py = $prodCoords[$idx]['y'];
                        $hitW = round($plotW / $nPts, 1);
                        $hitX = round(max($padL, min($svgW - $padR - $hitW, $c['x'] - ($hitW / 2))), 1);
                        $tipLines = 'Permintaan PO: ' . number_format($pt['poQty']) . ' pcs (' . $pt['poCount'] . ' PO)|Output Produksi: ' . number_format($pt['prodQty']) . ' pcs (A: ' . $pt['gradeA'] . ', B: ' . $pt['gradeB'] . ')';
                    @endphp
                    <g class="chart-col-group">
                        <line class="chart-guide" x1="{{ $c['x'] }}" y1="{{ $padT }}" x2="{{ $c['x'] }}" y2="{{ $baseY }}" />
                        <circle class="chart-dot-po" cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="4.5" />
                        <circle class="chart-dot-prod" cx="{{ $c['x'] }}" cy="{{ $py }}" r="4.5" />
                        <text class="chart-axis-text" x="{{ $c['x'] }}" y="{{ $baseY + 20 }}" text-anchor="middle">{{ $pt['label'] }}</text>
                        <rect
                            class="chart-hit"
                            x="{{ $hitX }}"
                            y="{{ $padT }}"
                            width="{{ $hitW }}"
                            height="{{ $plotH }}"
                            tabindex="0"
                            data-tip-title="{{ $pt['fullDate'] }}"
                            data-tip-lines="{{ $tipLines }}"
                            aria-label="{{ $pt['fullDate'] }}: PO {{ $pt['poQty'] }} pcs, Produksi {{ $pt['prodQty'] }} pcs"
                        />
                    </g>
                @endforeach
            </svg>
        </div>

        <div class="chart-legend">
            <span class="chart-legend-item">
                <span class="chart-dot-swatch is-po" aria-hidden="true"></span>
                <span>Permintaan PO: <b>{{ number_format(collect($trendSeriesData)->sum('poQty')) }} pcs</b></span>
            </span>
            <span class="chart-legend-item">
                <span class="chart-dot-swatch is-prod" aria-hidden="true"></span>
                <span>Output Produksi: <b>{{ number_format(collect($trendSeriesData)->sum('prodQty')) }} pcs</b></span>
            </span>
        </div>
    </article>

    {{-- Grafik 2: Distribusi Status PO Pelanggan --}}
    <article class="chart-card" aria-labelledby="ch-status">
        <div class="chart-head">
            <div>
                <h2 id="ch-status">Distribusi status PO</h2>
                <p class="sub">Komposisi pesanan pelanggan berdasarkan tahapan alur kerja.</p>
            </div>
            <a class="chart-link" href="{{ route('po-produk.index') }}">Kelola PO</a>
        </div>

        <div class="chart-body donut-layout">
            @php
                $radius = 62;
                $circumference = 2 * pi() * $radius;
                $nonZeroSegments = collect($poStatusData)->filter(fn($s) => $s['count'] > 0)->values();
                $segCount = $nonZeroSegments->count();
                $offsetAcc = 0.0;
            @endphp

            <div class="donut-wrap">
                <svg class="donut-svg" viewBox="0 0 180 180" role="img" aria-label="Grafik donat distribusi status PO pelanggan">
                    <circle class="donut-track" cx="90" cy="90" r="{{ $radius }}" />
                    @if($poTotalCount > 0)
                        <g transform="rotate(-90 90 90)">
                            @foreach($nonZeroSegments as $seg)
                                @php
                                    $segLen = ($seg['count'] / $poTotalCount) * $circumference;
                                    $drawLen = $segCount > 1 ? max(3, $segLen - 2) : $circumference;
                                    $tipLines = $seg['count'] . ' PO (' . $seg['pct'] . '%)|Volume: ' . number_format($seg['qty']) . ' pcs';
                                @endphp
                                <circle
                                    class="donut-seg is-{{ $seg['tone'] }}"
                                    cx="90"
                                    cy="90"
                                    r="{{ $radius }}"
                                    stroke-dasharray="{{ round($drawLen, 2) }} {{ round($circumference, 2) }}"
                                    stroke-dashoffset="{{ round(-$offsetAcc, 2) }}"
                                    tabindex="0"
                                    data-tip-title="Status: {{ $seg['label'] }}"
                                    data-tip-lines="{{ $tipLines }}"
                                    aria-label="{{ $seg['label'] }}: {{ $seg['count'] }} PO ({{ $seg['pct'] }}%)"
                                />
                                @php $offsetAcc += $segLen; @endphp
                            @endforeach
                        </g>
                    @endif
                    <text class="donut-center-val" x="90" y="88" text-anchor="middle">{{ number_format($poTotalCount) }}</text>
                    <text class="donut-center-lbl" x="90" y="108" text-anchor="middle">Total PO</text>
                </svg>
            </div>

            <ul class="donut-list">
                @foreach($poStatusData as $st)
                    <li>
                        <span class="donut-swatch is-{{ $st['tone'] }}" aria-hidden="true"></span>
                        <span>{{ $st['label'] }}</span>
                        <div class="donut-meta">
                            <b>{{ number_format($st['count']) }} PO</b>
                            <small>{{ number_format($st['qty']) }} pcs ({{ $st['pct'] }}%)</small>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="chart-legend">
            <span class="chart-legend-item">
                <span>PO aktif: <b>{{ number_format($poActiveCount ?? 0) }} pesanan</b></span>
            </span>
            <span class="chart-legend-item">
                <span>Total volume: <b>{{ number_format(collect($poStatusData)->sum('qty')) }} pcs</b></span>
            </span>
        </div>
    </article>

    {{-- Grafik 3: Permintaan PO vs Stok Produk Jadi --}}
    <article class="chart-card" aria-labelledby="ch-produk">
        <div class="chart-head">
            <div>
                <h2 id="ch-produk">Permintaan PO vs stok jadi</h2>
                <p class="sub">Produk teratas berdasarkan alokasi PO aktif dibanding stok gudang.</p>
            </div>
            <a class="chart-link" href="{{ route('finished-goods.index') }}">Produk jadi</a>
        </div>

        <div class="chart-body">
            @if($productDemandData->isNotEmpty())
                <ul class="hbar-list">
                    @foreach($productDemandData as $prod)
                        @php
                            $poPct = $prod['poQty'] > 0 ? max(4, round(($prod['poQty'] / $productScaleMax) * 100, 1)) : 0;
                            $stokPct = $prod['stok'] > 0 ? max(4, round(($prod['stok'] / $productScaleMax) * 100, 1)) : 0;
                            $gapLabel = $prod['shortage'] > 0 ? ('Kurang ' . number_format($prod['shortage']) . ' pcs') : 'Stok cukup';
                            $tipLines = 'Permintaan PO: ' . number_format($prod['poQty']) . ' pcs|Stok Gudang: ' . number_format($prod['stok']) . ' pcs|Status: ' . $gapLabel;
                        @endphp
                        <li
                            class="hbar-item"
                            tabindex="0"
                            data-tip-title="{{ $prod['name'] }}"
                            data-tip-lines="{{ $tipLines }}"
                        >
                            <div class="hbar-top">
                                <b>{{ $prod['name'] }}</b>
                                <span class="hbar-gap {{ $prod['shortage'] > 0 ? 'is-short' : '' }}">{{ $gapLabel }}</span>
                            </div>
                            <div class="hbar-tracks">
                                <div class="hbar-row">
                                    <span>PO</span>
                                    <div class="hbar-track"><i class="hbar-fill is-po" style="width:{{ $poPct }}%"></i></div>
                                    <span class="hbar-num">{{ number_format($prod['poQty']) }}</span>
                                </div>
                                <div class="hbar-row">
                                    <span>Stok</span>
                                    <div class="hbar-track"><i class="hbar-fill is-stok" style="width:{{ $stokPct }}%"></i></div>
                                    <span class="hbar-num">{{ number_format($prod['stok']) }}</span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-void-state
                    title="Belum ada data produk jadi"
                    description="Tambahkan produk jadi terlebih dahulu untuk membandingkan stok gudang terhadap permintaan PO."
                />
            @endif
        </div>

        <div class="chart-legend">
            <span class="chart-legend-item">
                <span class="chart-dot-swatch is-po" aria-hidden="true"></span>
                <span>Permintaan PO aktif (pcs)</span>
            </span>
            <span class="chart-legend-item">
                <span class="chart-dot-swatch is-prod" aria-hidden="true"></span>
                <span>Stok tersedia di gudang (pcs)</span>
            </span>
        </div>
    </article>

    {{-- Grafik 4: Stok Bahan Baku Terendah vs Batas Minimum --}}
    <article class="chart-card" aria-labelledby="ch-bahan">
        <div class="chart-head">
            <div>
                <h2 id="ch-bahan">Stok bahan baku terendah</h2>
                <p class="sub">8 bahan dengan persediaan terkecil dibanding batas aman 10 unit.</p>
            </div>
            <a class="chart-link" href="{{ route('raw-materials.index') }}">Stok bahan</a>
        </div>

        <div class="chart-body">
            @if($materialData->isNotEmpty())
                @php
                    $mW = 560;
                    $mH = 246;
                    $mPadL = 48;
                    $mPadR = 16;
                    $mPadT = 24;
                    $mPadB = 60;
                    $mPlotW = $mW - $mPadL - $mPadR;
                    $mPlotH = $mH - $mPadT - $mPadB;
                    $mBaseY = $mPadT + $mPlotH;
                    $mCount = $materialData->count();
                    $mSlotW = $mPlotW / $mCount;
                    $mBarW = min(38, $mSlotW * 0.56);
                    $threshY = round($mBaseY - (min($materialMax, 10) / $materialMax) * $mPlotH, 1);
                @endphp

                <svg class="chart-svg" viewBox="0 0 {{ $mW }} {{ $mH }}" role="img" aria-label="Grafik batang stok bahan baku terendah dibanding batas minimum 10">
                    @for($step = 0; $step <= 3; $step++)
                        @php
                            $val = (int) round(($materialMax / 3) * $step);
                            $gy = round($mBaseY - ($step / 3) * $mPlotH, 1);
                        @endphp
                        <line class="{{ $step === 0 ? 'chart-axis-line' : 'chart-grid-line' }}" x1="{{ $mPadL }}" y1="{{ $gy }}" x2="{{ $mW - $mPadR }}" y2="{{ $gy }}" />
                        <text class="chart-axis-text" x="{{ $mPadL - 8 }}" y="{{ $gy + 4 }}" text-anchor="end">{{ number_format($val) }}</text>
                    @endfor

                    <line class="chart-thresh-line" x1="{{ $mPadL }}" y1="{{ $threshY }}" x2="{{ $mW - $mPadR }}" y2="{{ $threshY }}" />
                    <text class="chart-thresh-text" x="{{ $mW - $mPadR }}" y="{{ $threshY - 5 }}" text-anchor="end">Batas min. 10</text>

                    @foreach($materialData as $idx => $mat)
                        @php
                            $bx = round($mPadL + ($idx * $mSlotW) + ($mSlotW - $mBarW) / 2, 1);
                            $bcx = round($bx + ($mBarW / 2), 1);
                            $bh = $mat['qty'] > 0 ? max(4, round(($mat['qty'] / $materialMax) * $mPlotH, 1)) : 0;
                            $by = round($mBaseY - $bh, 1);
                            $labelY = $mBaseY + 14;
                            $tipLines = 'Stok: ' . $mat['qtyLabel'] . ' ' . $mat['unit'] . '|Status: ' . $mat['statusLabel'] . ' (Batas aman > 10)';
                        @endphp
                        <g>
                            <rect
                                class="chart-bar is-{{ $mat['tone'] }}"
                                x="{{ $bx }}"
                                y="{{ $by }}"
                                width="{{ round($mBarW, 1) }}"
                                height="{{ $bh }}"
                                rx="6"
                                tabindex="0"
                                data-tip-title="{{ $mat['name'] }}"
                                data-tip-lines="{{ $tipLines }}"
                                aria-label="{{ $mat['name'] }}: {{ $mat['qtyLabel'] }} {{ $mat['unit'] }} ({{ $mat['statusLabel'] }})"
                            />
                            <text class="chart-val-text" x="{{ $bcx }}" y="{{ $by - 6 }}" text-anchor="middle">{{ $mat['qtyLabel'] }}</text>
                            <text
                                class="chart-axis-text"
                                x="{{ $bcx + 2 }}"
                                y="{{ $labelY }}"
                                text-anchor="end"
                                transform="rotate(-24 {{ $bcx + 2 }} {{ $labelY }})"
                            >{{ $mat['shortName'] }}</text>
                        </g>
                    @endforeach
                </svg>
            @else
                <x-void-state
                    title="Belum ada data bahan baku"
                    description="Tambahkan master bahan baku untuk memantau grafik level persediaan dapur."
                />
            @endif
        </div>

        <div class="chart-legend">
            <span class="chart-legend-item">
                <span class="chart-dot-swatch is-alert" aria-hidden="true"></span>
                <span>Kritis (&le; 5)</span>
            </span>
            <span class="chart-legend-item">
                <span class="chart-dot-swatch is-warn" aria-hidden="true"></span>
                <span>Batas minimum (6&ndash;10)</span>
            </span>
            <span class="chart-legend-item">
                <span class="chart-dot-swatch is-ok" aria-hidden="true"></span>
                <span>Aman (&gt; 10)</span>
            </span>
        </div>
    </article>
</section>

<div class="grid">
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

    <section aria-labelledby="h-batch">
        <div class="head">
            <h2 id="h-batch">Batch produksi dapur terbaru</h2>
            <a href="{{ route('log-produksi.index') }}">Lihat semua batch</a>
        </div>
        <p class="sub">Lima batch terakhir beserta hasil sortir Grade A dan Grade B.</p>
        @if(($recentLogProduksi ?? collect())->isEmpty())
            <x-void-state
                title="Belum ada batch produksi"
                description="Batch yang dicatat dapur akan muncul di sini lengkap dengan hasil Grade A dan Grade B."
            >
                <x-slot:action>
                    <a class="btn" href="{{ route('log-produksi.index') }}">Catat batch pertama</a>
                </x-slot:action>
            </x-void-state>
        @else
            <div class="panel is-flush">
                <ul class="rows">
                    @foreach($recentLogProduksi as $log)
                        @php
                            $isQcDone = strtoupper($log->status_approval) === 'APPROVED';
                        @endphp
                        <li class="row batch-compact {{ $isQcDone ? 'ok' : 'mid' }}">
                            <div>
                                <b>BATCH-00{{ $log->no_batch }} &middot; {{ $log->poProduk->nama_pelanggan ?? ($log->no_po_produk ? 'PO #' . $log->no_po_produk : 'Stok Internal Gudang') }}</b>
                                <small>
                                    {{ \Carbon\Carbon::parse($log->tgl_produksi)->format('d M Y') }}
                                    &middot; {{ $log->no_po_produk ? 'PO-00' . $log->no_po_produk : 'Produksi mandiri' }}
                                    &middot; Grade B: {{ number_format($log->jml_grade_B) }} pack
                                </small>
                            </div>
                            <div class="batch-compact-right">
                                <div class="qty">{{ number_format($log->jml_grade_A) }} <small>Grade A</small></div>
                                <span class="st">{{ $isQcDone ? 'Lolos QC' : 'Proses' }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
</div>

<div class="chart-tooltip" id="dash-chart-tooltip" hidden role="status" aria-live="polite"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/dashboard-charts.js') }}" defer></script>
@endpush
