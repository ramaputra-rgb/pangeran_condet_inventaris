<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pangeran Condet - Manufaktur & Distribusi</title>
    
    <!-- Font Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons Script (Sama persis dengan Icon Set Figma) -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        pc: {
                            maroon: '#B71C1C',
                            orange: '#F47C20',
                            yellow: '#FFC107',
                            cream: '#FFF3E0',
                            dark: '#4E2A13',
                            bg: '#FAF9F6',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#FAF9F6] text-pc-dark font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR NAVIGATION -->
        <aside class="w-64 bg-white border-r border-gray-100 flex flex-col justify-between shrink-0 shadow-sm">
            <div>
                <!-- Header Brand Logo (Mahkota Pangeran Condet) -->
                <div class="p-4 border-b border-gray-100 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pc-maroon to-pc-orange flex items-center justify-center text-white shadow-md">
                        <i data-lucide="crown" class="w-5 h-5 text-pc-yellow"></i>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-pc-maroon text-sm leading-tight tracking-tight">Pangeran Condet</h1>
                        <p class="text-[9px] text-gray-400 font-semibold">Manufaktur & Distribusi Re...</p>
                    </div>
                </div>

                <!-- Navigasi Menu Sidebar -->
                <nav class="p-3 space-y-4 text-xs font-semibold">
                    <div>
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-pc-cream text-pc-maroon font-bold border border-pc-orange/20 shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard Utama</span>
                        </a>
                    </div>

                    <!-- Group 1: MANAJEMEN INVENTARIS -->
                    <div class="space-y-1">
                        <p class="px-3.5 text-[9px] font-extrabold text-gray-400 uppercase tracking-wider">MANAJEMEN INVENTARIS</p>
                        
                        <a href="{{ route('finished-goods.index') }}" 
                           class="flex items-center space-x-3 px-3.5 py-2 rounded-xl transition {{ request()->routeIs('finished-goods.index') ? 'bg-pc-cream text-pc-maroon font-bold border border-pc-orange/20' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i data-lucide="package-check" class="w-4 h-4"></i>
                            <span>Stok Produk Jadi (FG)</span>
                        </a>

                        <a href="{{ route('raw-materials.index') }}" 
                           class="flex items-center space-x-3 px-3.5 py-2 rounded-xl transition {{ request()->routeIs('raw-materials.index') ? 'bg-pc-cream text-pc-maroon font-bold border border-pc-orange/20' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            <span>Pengadaan & Bahan Baku</span>
                        </a>

                        <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            <span>Pesanan PO & Toko / E-Com</span>
                        </a>

                        <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                            <span>Mutasi Keluar & Masuk</span>
                        </a>

                        <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                            <span>Laporan Omset & Stok</span>
                        </a>
                    </div>

                    <!-- Group 2: MANAJEMEN PRODUKSI -->
                    <div class="space-y-1">
                        <p class="px-3.5 text-[9px] font-extrabold text-gray-400 uppercase tracking-wider">MANAJEMEN PRODUKSI</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                            <i data-lucide="scroll-text" class="w-4 h-4"></i>
                            <span>Formula BoM & Resep</span>
                        </a>

                        <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                            <span>Work Order Produksi</span>
                        </a>

                        <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                            <i data-lucide="history" class="w-4 h-4"></i>
                            <span>Log Batch Produksi</span>
                        </a>
                    </div>

                    <!-- Group 3: INTEGRASI SALURAN -->
                    <div class="space-y-1">
                        <p class="px-3.5 text-[9px] font-extrabold text-gray-400 uppercase tracking-wider">INTEGRASI SALURAN</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                            <i data-lucide="store" class="w-4 h-4"></i>
                            <span>Webstore & Kasir POS</span>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Footer Sidebar (Bantuan & Logout) -->
            <div class="p-3 border-t border-gray-100 space-y-1 text-xs font-semibold text-gray-500">
                <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl hover:bg-gray-50 transition">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                    <span>Bantuan Pabrik</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-3.5 py-2 rounded-xl hover:bg-red-50 text-pc-maroon transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar Sesi</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            
            <!-- Top Header Bar -->
            <header class="bg-white border-b border-gray-100 px-6 py-3 flex justify-between items-center sticky top-0 z-10 shadow-sm">
                <div class="flex items-center space-x-3 text-xs">
                    <span class="flex items-center space-x-2 bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold px-3 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Rumah Produksi Condet • Shift Pagi (Aktif)</span>
                    </span>
                    <span class="text-gray-400 font-medium flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i> 25 Agustus 2024
                    </span>
                </div>

                <!-- Admin Profile Badge -->
                <div class="flex items-center space-x-4 text-xs">
                    <button class="p-1.5 text-gray-400 hover:text-pc-maroon relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-pc-maroon rounded-full"></span>
                    </button>
                    <div class="flex items-center space-x-3 border-l border-gray-100 pl-4">
                        <div class="w-8 h-8 rounded-full bg-pc-maroon text-white font-extrabold flex items-center justify-center text-xs shadow-sm">
                            A
                        </div>
                        <div>
                            <p class="font-bold text-pc-dark leading-none">Admin123</p>
                            <p class="text-[10px] text-gray-400 font-semibold mt-0.5">Kepala Operasional / Admin</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dynamic Content Area -->
            <main class="p-6 space-y-6">
                @yield('content')
            </main>
        </div>

    </div>

    <!-- Script Render Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>