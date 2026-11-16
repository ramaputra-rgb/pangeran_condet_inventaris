<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', $title ?? 'Pangeran Condet, Dasbor')</title>
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('pc-theme');
                if (theme === 'dark' || theme === 'light') {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1" />
                        <rect width="7" height="5" x="14" y="3" rx="1" />
                        <rect width="7" height="9" x="14" y="12" rx="1" />
                        <rect width="7" height="5" x="3" y="16" rx="1" />
                    </svg>
                </span>
                <span>Dasbor</span>
            </a>

            <a href="{{ route('raw-materials.index') }}" @if(request()->routeIs('raw-materials.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z" />
                        <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65" />
                        <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65" />
                    </svg>
                </span>
                <span>Stok bahan</span>
                @if(($navStokKritisCount ?? 0) > 0)
                    <span class="n warn">{{ $navStokKritisCount }}</span>
                @endif
            </a>

            <a href="{{ route('finished-goods.index') }}" @if(request()->routeIs('finished-goods.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                        <path d="m3.3 7 8.7 5 8.7-5" />
                        <path d="M12 22V12" />
                    </svg>
                </span>
                <span>Produk jadi</span>
            </a>

            <a href="{{ route('po-produk.index') }}" @if(request()->routeIs('po-produk.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                        <path d="M12 11h4" />
                        <path d="M12 16h4" />
                        <path d="M8 11h.01" />
                        <path d="M8 16h.01" />
                    </svg>
                </span>
                <span>PO</span>
                @if(($navPoActiveCount ?? 0) > 0)
                    <span class="n">{{ $navPoActiveCount }}</span>
                @endif
            </a>

            <a href="{{ route('log-produksi.index') }}" @if(request()->routeIs('log-produksi.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
                    </svg>
                </span>
                <span>Produksi</span>
            </a>

            <a href="{{ route('shipments.index') }}" @if(request()->routeIs('shipments.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                        <path d="M15 18H9" />
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
                        <circle cx="17" cy="18" r="2" />
                        <circle cx="7" cy="18" r="2" />
                    </svg>
                </span>
                <span>Pengiriman</span>
            </a>

            <a href="{{ route('reports.index') }}" @if(request()->routeIs('reports.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
                        <path d="M14 2v6h6" />
                        <path d="M8 18v-2" />
                        <path d="M12 18v-4" />
                        <path d="M16 18v-6" />
                    </svg>
                </span>
                <span>Laporan</span>
            </a>

            <a href="{{ route('settings.index') }}" @if(request()->routeIs('settings.*')) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </span>
                <span>Pengaturan web</span>
            </a>
        </nav>

        <div class="who">
            <div class="out">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="out-btn">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" x2="9" y1="12" y2="12" />
                        </svg>
                        <span>Keluar sesi</span>
                    </button>
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
