<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', $title ?? 'Pangeran Condet, Dasbor')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Young+Serif&family=Hanken+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-style.css') }}">
</head>
<body>
<div class="app">
    <header class="mobile-topbar no-print">
        <button
            type="button"
            class="burger-btn"
            data-nav-toggle
            aria-label="Buka menu navigasi"
            aria-controls="app-sidebar"
            aria-expanded="false"
        >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>
        <a class="brand mobile-brand" href="{{ route('dashboard') }}">
            Pangeran Condet
            <small>Manufaktur dan distribusi rengginang</small>
        </a>
    </header>

    <div class="nav-backdrop no-print" data-nav-backdrop hidden></div>

    <aside id="app-sidebar" class="side no-print" aria-label="Menu samping">
        <div class="side-head">
            <a class="brand" href="{{ route('dashboard') }}">
                Pangeran Condet
                <small>Manufaktur dan distribusi rengginang</small>
            </a>
            <button
                type="button"
                class="side-close"
                data-nav-close
                aria-label="Tutup menu navigasi"
            >&times;</button>
        </div>

        <nav class="nav" aria-label="Menu utama">
            <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                Dasbor
            </a>

            <a href="{{ route('raw-materials.index') }}" @if(request()->routeIs('raw-materials.*')) aria-current="page" @endif>
                Stok bahan
                @if(($navStokKritisCount ?? 0) > 0)
                    <span class="n warn">{{ $navStokKritisCount }}</span>
                @endif
            </a>

            <a href="{{ route('finished-goods.index') }}" @if(request()->routeIs('finished-goods.*')) aria-current="page" @endif>
                Produk jadi
            </a>

            <a href="{{ route('po-produk.index') }}" @if(request()->routeIs('po-produk.*')) aria-current="page" @endif>
                PO
                @if(($navPoActiveCount ?? 0) > 0)
                    <span class="n">{{ $navPoActiveCount }}</span>
                @endif
            </a>

            <a href="{{ route('log-produksi.index') }}" @if(request()->routeIs('log-produksi.*')) aria-current="page" @endif>
                Produksi
            </a>

            <a href="{{ route('shipments.index') }}" @if(request()->routeIs('shipments.*')) aria-current="page" @endif>
                Pengiriman
            </a>

            <a href="{{ route('reports.index') }}" @if(request()->routeIs('reports.*')) aria-current="page" @endif>
                Laporan
            </a>
        </nav>

        <div class="who">
            <div class="out">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="out-btn">Keluar sesi</button>
                </form>
            </div>
        </div>
    </aside>

    <main>
        @if(session('success'))
            <div class="flash-banner is-success no-print" role="status">
                <span>{{ session('success') }}</span>
                <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="flash-banner is-error no-print" role="alert">
                <span>{{ session('error') }}</span>
                <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Tutup peringatan">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="flash-banner is-error no-print" role="alert">
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Tutup galat">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="{{ asset('js/app-ui.js') }}" defer></script>
@stack('scripts')
</body>
</html>
