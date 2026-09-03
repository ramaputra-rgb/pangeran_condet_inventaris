<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pangeran Condet - Inventaris')</title>
    
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-maroon: #580B0B;
            --brand-red: #8E1515;
            --brand-orange: #F47C20;
            --border-light: #EAEAEA;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body { 
            display: flex; 
            height: 100vh; 
            width: 100vw;
            background-color: var(--bg-maroon); 
            overflow: hidden; 
        }

        /* Sidebar Fix Width */
        .sidebar {
            width: 280px;
            min-width: 280px;
            height: 100vh;
            background-color: #FFFFFF;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid var(--border-light);
            z-index: 10;
        }

        .brand-logo { text-align: center; margin-bottom: 20px; }
        .brand-logo img { width: 130px; }

        .nav-menu { list-style: none; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 16px;
            margin-bottom: 6px;
            border-radius: 8px;
            color: #222222;
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            transition: 0.2s;
        }
        .nav-item i { font-size: 16px; width: 22px; text-align: center; color: #444; }
        
        .nav-item.active { background-color: var(--brand-red); color: #FFFFFF; }
        .nav-item.active i { color: #FFFFFF; }
        .nav-item:hover:not(.active) { background-color: #F5F5F5; }

        /* Profile Card Admin di Bawah Sidebar */
        .user-profile-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: #F9F9F9;
            border-radius: 12px;
            border: 1px solid #EEE;
            margin-top: 10px;
        }
        .user-avatar {
            width: 36px;
            height: 36px;
            background-color: #FFE0B2;
            color: #F47C20;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .user-info { flex: 1; overflow: hidden; }
        .user-info h4 { font-size: 12px; font-weight: 700; color: #222; margin: 0; }
        .user-info p { font-size: 10px; color: #777; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Area Main Content Luas & Responsif */
        .main-content {
            flex: 1;
            width: calc(100vw - 280px);
            height: 100vh;
            background-color: var(--bg-maroon);
            padding: 30px;
            overflow-y: auto;
            box-sizing: border-box;
        }

        @yield('styles')
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div>
            <div class="brand-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Pangeran Condet Logo">
            </div>
            <ul class="nav-menu">
                <li><a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fa-solid fa-border-all"></i> Dashboard</a></li>
                <li><a href="#" class="nav-item"><i class="fa-solid fa-box-archive"></i> Data Barang</a></li>
                <li><a href="#" class="nav-item"><i class="fa-solid fa-boxes-packing"></i> Barang Masuk</a></li>
                <li><a href="#" class="nav-item"><i class="fa-solid fa-truck-ramp-box"></i> Barang Keluar</a></li>
                <li><a href="#" class="nav-item"><i class="fa-solid fa-rotate-left"></i> Retur Barang</a></li>
                <li><a href="#" class="nav-item"><i class="fa-solid fa-chart-line"></i> Pemantauan Stok</a></li>
                <li><a href="#" class="nav-item"><i class="fa-solid fa-file-lines"></i> Laporan</a></li>
                <li><a href="#" class="nav-item"><i class="fa-regular fa-bell"></i> Notifikasi</a></li>
            </ul>
        </div>

        <div>
            <div class="user-profile-card">
                <div class="user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="user-info">
                    <h4>Admin123</h4>
                    <p>Admin123@gmail.com</p>
                </div>
                <i class="fa-solid fa-angle-down" style="font-size: 12px; color: #888; cursor: pointer;"></i>
            </div>
            <a href="{{ route('login') }}" class="nav-item" style="color: #D32F2F; margin-top: 8px; padding: 6px 12px;"><i class="fa-solid fa-arrow-right-from-bracket" style="color: #D32F2F;"></i> Keluar</a>
        </div>
    </aside>

    <main class="main-content">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>