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
        <span class="eyebrow dash-eyebrow">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 3v18h18" />
                <path d="m19 9-5 5-4-4-3 3" />
            </svg>
            Ringkasan &amp; analitik
        </span>
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
            <a class="btn has-icon" href="{{ route('raw-materials.index') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                    <path d="M3 6h18" />
                    <path d="M16 10a4 4 0 0 1-8 0" />
                </svg>
                Belanja bahan baku
            </a>
        @else
            <a class="btn has-icon" href="{{ route('po-produk.index') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                    <path d="M12 11h4" />
                    <path d="M12 16h4" />
                    <path d="M8 11h.01" />
                    <path d="M8 16h.01" />
                </svg>
                Kelola antrean PO
            </a>
        @endif
        <a class="btn outline has-icon" href="{{ route('log-produksi.index') }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
            </svg>
            Catat batch produksi
        </a>
    </div>
</section>

<section class="stub-grid four" aria-label="Ringkasan metrik utama">
    <x-stub-card
        title="PO aktif"
        subtitle="Menunggu, proses, atau dikirim"
        :value="number_format($poActiveCount ?? 0)"
        unit="pesanan"
        :variant="($poActiveCount ?? 0) > 0 ? 'warm' : null"
        iconTone="warm"
    >
        <x-slot:icon>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
                <path d="M14 2v6h6" />
                <path d="M16 13H8" />
                <path d="M16 17H8" />
                <path d="M10 9H8" />
            </svg>
        </x-slot:icon>
    </x-stub-card>

    <x-stub-card
        title="Bahan di bawah batas"
        subtitle="Stok &le; 10 unit (min. aman)"
        :value="number_format($stokKritisCount ?? 0)"
        unit="bahan"
        :variant="($stokKritisCount ?? 0) > 0 ? 'alert' : null"
        :iconTone="($stokKritisCount ?? 0) > 0 ? 'alert' : 'ok'"
    >
        <x-slot:icon>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                <path d="M12 9v4" />
                <path d="M12 17h.01" />
            </svg>
        </x-slot:icon>
    </x-stub-card>

    <x-stub-card
        title="Lolos QC Grade A"
        subtitle="{{ number_format($totalBatchProduksi ?? 0) }} batch &middot; Grade B: {{ number_format($totalGradeB ?? 0) }}"
        :value="number_format($totalGradeA ?? 0)"
        unit="pcs"
        iconTone="ok"
    >
        <x-slot:icon>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                <path d="m9 12 2 2 4-4" />
            </svg>
        </x-slot:icon>
    </x-stub-card>

    <x-stub-card
        title="Stok produk jadi"
        subtitle="Aset Rp {{ number_format($totalNilaiAsetFg ?? 0, 0, ',', '.') }}"
        :value="number_format($totalStokFg ?? 0)"
        unit="pcs"
        variant="warm"
        iconTone="warm"
    >
        <x-slot:icon>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                <path d="m3.3 7 8.7 5 8.7-5" />
                <path d="M12 22V12" />
            </svg>
        </x-slot:icon>
    </x-stub-card>
</section>

<section class="dash-charts" aria-label="Grafik analitik operasional">
    {{-- Grafik 1: Tren Permintaan PO vs Output Produksi --}}
    <article class="chart-card" aria-labelledby="ch-trend">
        <div class="chart-head">
            <div class="chart-title-wrap">
                <span class="chart-icon is-warm" aria-hidden="true">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                        <polyline points="16 7 22 7 22 13" />
                    </svg>
                </span>
                <div>
                    <h2 id="ch-trend">Tren PO &amp; output produksi</h2>
                    <p class="sub">Perbandingan jumlah pesanan masuk dan hasil produksi per tanggal.</p>
                </div>
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
            <div class="chart-title-wrap">
                <span class="chart-icon is-warm" aria-hidden="true">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                        <path d="M22 12A10 10 0 0 0 12 2v10z" />
                    </svg>
                </span>
                <div>
                    <h2 id="ch-status">Distribusi status PO</h2>
                    <p class="sub">Komposisi pesanan pelanggan berdasarkan tahapan alur kerja.</p>
                </div>
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
            <div class="chart-title-wrap">
                <span class="chart-icon is-ok" aria-hidden="true">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z" />
                        <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65" />
                        <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65" />
                    </svg>
                </span>
                <div>
                    <h2 id="ch-produk">Permintaan PO vs stok jadi</h2>
                    <p class="sub">Produk teratas berdasarkan alokasi PO aktif dibanding stok gudang.</p>
                </div>
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
            <div class="chart-title-wrap">
                <span class="chart-icon {{ ($stokKritisCount ?? 0) > 0 ? 'is-alert' : 'is-ok' }}" aria-hidden="true">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18" />
                        <rect width="4" height="7" x="7" y="10" rx="1" />
                        <rect width="4" height="12" x="15" y="5" rx="1" />
                    </svg>
                </span>
                <div>
                    <h2 id="ch-bahan">Stok bahan baku terendah</h2>
                    <p class="sub">8 bahan dengan persediaan terkecil dibanding batas aman 10 unit.</p>
                </div>
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
            <h2 id="h-po" class="section-heading-wrap">
                <span class="section-icon" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                        <path d="M14 8H8" />
                        <path d="M16 12H8" />
                        <path d="M13 16H8" />
                    </svg>
                </span>
                PO terbaru dari pelanggan
            </h2>
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
            <h2 id="h-batch" class="section-heading-wrap">
                <span class="section-icon" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
                    </svg>
                </span>
                Batch produksi dapur terbaru
            </h2>
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
