<aside class="sidebar">
    <div>
        <div class="brand-logo">
            <!-- Nanti simpan file logo di: public/images/logo.png -->
            <img src="{{ asset('images/logo.png') }}" alt="Pangeran Condet Logo">
        </div>
        <ul class="nav-menu">
            <li><a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="#" class="nav-item">Data Barang</a></li>
            <li><a href="#" class="nav-item">Barang Masuk</a></li>
            <li><a href="#" class="nav-item">Barang Keluar</a></li>
            <li><a href="#" class="nav-item">Retur Barang</a></li>
            <li><a href="#" class="nav-item">Pemantauan Stok</a></li>
            <li><a href="#" class="nav-item">Laporan</a></li>
            <li><a href="#" class="nav-item">Notifikasi</a></li>
        </ul>
    </div>
    <div>
        <a href="{{ route('login') }}" class="nav-item" style="color: #D32F2F;">Keluar</a>
    </div>
</aside>