<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinishedGoodsController;
use App\Http\Controllers\RawMaterialsController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\LogProduksiController;
use App\Http\Controllers\PoProdukController;
use App\Http\Controllers\ShipmentController;

/*
|--------------------------------------------------------------------------
| Autentikasi Pengguna (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Logout (Auth Only)
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Rute Terproteksi Sistem ERP (Wajib Login / Auth Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

// Modul manajemen stok produksi
Route::get('/raw-materials', [RawMaterialsController::class, 'index'])->name('raw-materials.index');
Route::post('/raw-materials/material/store', [RawMaterialsController::class, 'storeMaterial'])->name('raw-materials.material.store');
Route::put('/raw-materials/material/{id}', [RawMaterialsController::class, 'updateMaterial'])->name('raw-materials.material.update');
Route::delete('/raw-materials/material/{id}', [RawMaterialsController::class, 'destroyMaterial'])->name('raw-materials.material.destroy');

Route::post('/raw-materials/purchase/store', [RawMaterialsController::class, 'storePurchase'])->name('raw-materials.purchase.store');
Route::post('/raw-materials/purchase/destroy-group', [RawMaterialsController::class, 'destroyPurchaseGroup'])->name('raw-materials.purchase.destroy-group');

    // Modul Dashboard
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Pengiriman & Surat Jalan
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments/surat-jalan/store', [ShipmentController::class, 'storeSuratJalan'])->name('shipments.surat-jalan.store');
    Route::post('/shipments/update-status/{id}', [ShipmentController::class, 'updateShipmentStatus'])->name('shipments.update-status');

// Modul PO Produk
Route::get('/po-produk', [PoProdukController::class, 'index'])->name('po-produk.index');
Route::post('/po-produk/store', [PoProdukController::class, 'store'])->name('po-produk.store');
Route::post('/po-produk/potong-stok/{id}', [PoProdukController::class, 'potongStokReady'])->name('po-produk.potong-stok');
Route::get('/po-produk/buat-batch/{id}', [PoProdukController::class, 'buatProdukBatch'])->name('po-produk.buat-batch');
Route::post('/po-produk/update-status/{id}', [PoProdukController::class, 'updateStatus'])->name('po-produk.update-status');

// Modul Log Produksi
Route::get('/log-produksi', [LogProduksiController::class, 'index'])->name('log-produksi.index');
Route::post('/log-produksi/store', [LogProduksiController::class, 'store'])->name('log-produksi.store');
Route::post('/log-produksi/approve/{id}', [LogProduksiController::class, 'approveBatch'])->name('log-produksi.approve');

    // Modul Work Orders
    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::post('/work-orders/store', [WorkOrderController::class, 'store'])->name('work-orders.store');

    // Modul Formula BOM
    Route::get('/bom-recipes', [BomController::class, 'index'])->name('bom-recipes.index');
    Route::post('/bom-recipes/store', [BomController::class, 'store'])->name('bom-recipes.store');

    // Modul Finished Goods (Barang Jadi)
    Route::get('/finished-goods', [FinishedGoodsController::class, 'index'])->name('finished-goods.index');
    Route::post('/finished-goods/store', [FinishedGoodsController::class, 'storeProduct'])->name('finished-goods.store');
    Route::post('/finished-goods/update-stock/{id}', [FinishedGoodsController::class, 'updateStock'])->name('finished-goods.update-stock');

    // SPK & Serah Terima Hasil Produksi Dapur
    Route::post('/production-batch', [ProductionBatchController::class, 'store'])->name('production-batch.store');
    Route::patch('/production-batch/{id}/accept', [FinishedGoodsController::class, 'acceptBatch'])->name('production-batch.accept');

});