@extends('layouts.app')

@section('title', 'Dashboard - Pangeran Condet')

@section('styles')
<style>
    .dashboard-container {
        width: 100%;
        display: block;
    }

    /* Header Bar Sejajar Presisi */
    .header-wrapper {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        padding-bottom: 14px;
        margin-bottom: 24px;
        border-bottom: 2px solid #F47C20;
    }

    .header-wrapper h1 {
        color: #FFFFFF;
        font-size: 26px;
        font-weight: 700;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1;
    }

    .search-box-right {
        width: 240px;
        position: relative;
    }

    .search-box-right input {
        width: 100%;
        background: #FFFFFF;
        border: none;
        padding: 9px 36px 9px 16px;
        border-radius: 8px;
        font-size: 12.5px;
        color: #333333;
        outline: none;
        box-sizing: border-box;
    }

    .search-box-right i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #888888;
        font-size: 13px;
        pointer-events: none;
    }

    /* 4 Top Card Menyamping (Paksa Flexbox Row) */
    .metrics-row {
        display: flex !important;
        flex-direction: row !important;
        gap: 16px !important;
        margin-bottom: 24px !important;
        width: 100% !important;
    }

    .m-card {
        flex: 1 !important; /* Membuat ke-4 card memiliki lebar yang persis sama */
        background: #FFFFFF;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-sizing: border-box;
    }

    .m-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background-color: #FFF3E0;
        color: #F47C20;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .m-card-info p {
        font-size: 12px;
        font-weight: 600;
        color: #4E2A13;
        margin: 0;
    }

    .m-card-info h2 {
        font-size: 30px;
        font-weight: 700;
        color: #222222;
        line-height: 1;
        margin-top: 4px;
    }

    /* 4 Main Widgets Grid (Paksa 2 Kolom) */
    .widgets-row {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 20px !important;
        width: 100% !important;
    }

    .w-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 20px;
        min-height: 240px;
        box-sizing: border-box;
    }

    .w-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #8E1515;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .w-title i {
        font-size: 16px;
        color: #F47C20;
    }

    .stock-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #F5F5F5;
        font-size: 12px;
        font-weight: 600;
        color: #333;
    }

    .badge-qty {
        background-color: #FFCDD2;
        color: #B71C1C;
        padding: 2px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
    }

    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        padding: 8px 0;
        color: #444;
        font-weight: 600;
        border-bottom: 1px solid #F8F8F8;
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Header Top Bar -->
    <div class="header-wrapper">
        <h1>Dashboard</h1>
        <div class="search-box-right">
            <input type="text" placeholder="Cari Produk">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
    </div>

    <!-- 4 Top Cards (Menyamping Sejajar) -->
    <div class="metrics-row">
        <div class="m-card">
            <div class="m-card-icon"><i class="fa-solid fa-box-open"></i></div>
            <div class="m-card-info">
                <p>Total Produk</p>
                <h2>18</h2>
            </div>
        </div>
        <div class="m-card">
            <div class="m-card-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div class="m-card-info">
                <p>Total Stok</p>
                <h2>200</h2>
            </div>
        </div>
        <div class="m-card">
            <div class="m-card-icon"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
            <div class="m-card-info">
                <p>Produk Masuk</p>
                <h2>30</h2>
            </div>
        </div>
        <div class="m-card">
            <div class="m-card-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="m-card-info">
                <p>Produk Keluar</p>
                <h2>10</h2>
            </div>
        </div>
    </div>

    <!-- 4 Main Widgets Grid -->
    <div class="widgets-row">
        <div class="w-card">
            <div class="w-title"><i class="fa-solid fa-chart-line"></i> Pergerakan Stok</div>
            <div style="height: 160px; display: flex; justify-content: center; align-items: center; color: #aaa; font-size: 12px;">
                [ Area Grafik Line Chart ]
            </div>
        </div>

        <div class="w-card">
            <div class="w-title"><i class="fa-solid fa-triangle-exclamation" style="color: #E53935;"></i> Peringatan Stok Menipis</div>
            <div class="stock-row">
                <span>#104 Rengginang Bawang 100 Gram</span>
                <span class="badge-qty">8 pcs</span>
            </div>
            <div class="stock-row">
                <span>#102 Rengginang Cumi 100 Gram</span>
                <span class="badge-qty">8 pcs</span>
            </div>
            <div class="stock-row">
                <span>#101 Rengginang Ikan 360 Gram</span>
                <span class="badge-qty">8 pcs</span>
            </div>
            <div class="stock-row">
                <span>#103 Rengginang Original 100 Gram</span>
                <span class="badge-qty">8 pcs</span>
            </div>
        </div>

        <div class="w-card">
            <div class="w-title"><i class="fa-solid fa-clock-rotate-left"></i> Aktivitas Terbaru</div>
            <div class="list-item">
                <span>Produk Masuk - Rengginang Ikan 100 Gram</span>
                <span style="color: #2E7D32; font-weight:700;">+50 pcs</span>
            </div>
            <div class="list-item">
                <span>Produk Keluar - Rengginang Cumi 100 Gram</span>
                <span style="color: #C62828; font-weight:700;">-10 pcs</span>
            </div>
            <div class="list-item">
                <span>Retur - Rengginang Cumi 100 Gram</span>
                <span style="color: #555; font-weight:700;">2 pcs</span>
            </div>
        </div>

        <div class="w-card">
            <div class="w-title"><i class="fa-solid fa-trophy" style="color: #FFC107;"></i> Produk Terlaris</div>
            <div class="list-item">
                <span>#101 Rengginang Ikan 360 Gram</span>
                <span style="font-weight:700;">50 pcs</span>
            </div>
            <div class="list-item">
                <span>#102 Rengginang Cumi 100 Gram</span>
                <span style="font-weight:700;">30 pcs</span>
            </div>
            <div class="list-item">
                <span>#103 Rengginang Original 200 Gram</span>
                <span style="font-weight:700;">28 pcs</span>
            </div>
        </div>
    </div>
</div>
@endsection