<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinishedGoodsController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\MaterialPurchaseController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\LogProduksiController;
use App\Http\Controllers\PoProdukController;
use App\Http\Controllers\ShipmentController;

// Modul Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

// Modul Pengiriman & Surat jalan
Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
Route::post('/shipments/surat-jalan/store', [ShipmentController::class, 'storeSuratJalan'])->name('shipments.surat-jalan.store');
Route::post('/shipments/update-status/{id}', [ShipmentController::class, 'updateShipmentStatus'])->name('shipments.update-status');

// Modul PO Produk
Route::get('/po-produk', [PoProdukController::class, 'index'])->name('po-produk.index');
Route::post('/po-produk/store', [PoProdukController::class, 'store'])->name('po-produk.store');
Route::post('/po-produk/update-status/{id}', [PoProdukController::class, 'updateStatus'])->name('po-produk.update-status');

// Modul Log Produksi
Route::get('/log-produksi', [LogProduksiController::class, 'index'])->name('log-produksi.index');
Route::post('/log-produksi/store', [LogProduksiController::class, 'store'])->name('log-produksi.store');

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

// Modul Bahan Baku & Pengadaan (Raw Materials)
Route::get('/raw-materials', [RawMaterialController::class, 'index'])->name('raw-materials.index');
# Route::post('/raw-materials/purchase', [MaterialPurchaseController::class, 'store'])->name('raw-materials.purchase.store');
Route::post('/raw-materials/purchase', [RawMaterialController::class, 'storePurchase'])->name('raw-materials.purchase.store');
Route::post('/raw-materials/supplier', [RawMaterialController::class, 'storeSupplier'])->name('raw-materials.supplier.store');
Route::post('/raw-materials/material', [RawMaterialController::class, 'storeMaterial'])->name('raw-materials.material.store');
Route::get('/raw-materials/export-csv', [RawMaterialController::class, 'exportCsv'])->name('raw-materials.export-csv');