<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ERP UD Pangeran Condet' }}</title>
    <!-- CSS Native Utama (Offline) -->
    <link rel="stylesheet" href="{{ asset('css/custom-style.css') }}">
</head>
<body>
    <div class="layout-wrapper">
        
        <!-- SIDEBAR CONTAINER -->
        <aside class="sidebar-container no-print">
            <div>
                <!-- Brand Logo Header -->
                <div class="brand-header">
                    <div class="brand-logo-icon">
                        <span style="color:#fff; font-weight:900; font-size:1.1rem;">👑</span>
                    </div>
                    <div>
                        <h1 class="brand-title">Pangeran Condet</h1>
                        <p class="brand-subtitle">Manufaktur & Distribusi Rengginang</p>
                    </div>
                </div>

                <!-- Navigasi Menu Sidebar -->
                <nav class="nav-menu">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Dashboard Utama</span>
                    </a>

                    <a href="{{ route('raw-materials.index') }}" class="nav-link {{ request()->routeIs('raw-materials.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        <span>Manajemen Stok Bahan</span>
                    </a>

                    <a href="{{ route('finished-goods.index') }}" class="nav-link {{ request()->routeIs('finished-goods.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l6 3.43a2 2 0 0 0 2.06 0l6-3.43a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L11 9.5a2 2 0 0 0-2.06 0l-6.03 3.42Z"/><path d="m7 16.5 4-2.3 4 2.3"/><path d="M11 9.5v7"/></svg>
                        <span>Master Produk Jadi</span>
                    </a>

                    <a href="{{ route('po-produk.index') }}" class="nav-link {{ request()->routeIs('po-produk.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span>PO Produk</span>
                    </a>

                    <a href="{{ route('log-produksi.index') }}" class="nav-link {{ request()->routeIs('log-produksi.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Log Produksi</span>
                    </a>

                    <a href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        <span>Log Pengiriman</span>
                    </a>
                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <a href="#" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Bantuan Pabrik</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link btn-logout">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span class="font-bold">Keluar Sesi</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="main-content">
            
            <!-- TOP BAR HEADER -->
            <header class="top-header no-print">
                <div class="flex items-center gap-3">
                    <span class="status-pill">
                        <span class="pulse-dot"></span>
                        Rumah Produksi Condet • Shift Pagi (Aktif)
                    </span>
                    <span style="font-size:0.6875rem; color: var(--text-muted); font-weight:600;">
                        📅 {{ \Carbon\Carbon::now()->format('d F Y') }}
                    </span>
                </div>

                <div class="user-profile-box">
                    <!-- Icon Bell Notification -->
                    <button type="button" class="icon-btn" title="Notifikasi">
                        <svg style="width:1.125rem; height:1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    </button>

                    <div class="avatar-circle">O</div>
                    <div>
                        <p class="font-black" style="color: var(--pc-dark); margin:0; font-size:0.75rem;">Owner Pangeran Condet</p>
                        <p style="font-size: 0.5625rem; color: var(--text-muted); margin:0;">Pemilik Usaha (Owner)</p>
                    </div>

                    <!-- Icon Logout Header -->
                    <form action="{{ route('logout') }}" method="POST" style="margin-left:0.5rem;">
                        @csrf
                        <button type="submit" class="icon-btn" title="Keluar">
                            <svg style="width:1.125rem; height:1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        </button>
                    </form>
                </div>
            </header>

            <!-- FLASH ALERT NOTIFICATION -->
            @if(session('success'))
                <div style="margin: 1rem 1.5rem 0 1.5rem;" class="alert alert-success">
                    <span>✓ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div style="margin: 1rem 1.5rem 0 1.5rem;" class="alert alert-danger">
                    <span>⚠️ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-weight:bold;">✕</button>
                </div>
            @endif

            <!-- DYNAMIC BODY CONTENT -->
            <main class="content-body">
                @yield('content')
            </main>

        </div>
    </div>
</body>
</html>