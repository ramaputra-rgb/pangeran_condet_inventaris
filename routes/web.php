<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinishedGoodsController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\MaterialPurchaseController;

Route::post('/finished-goods/{product}/allocate-po', [FinishedGoodsController::class, 'allocatePo'])->name('finished-goods.allocate-po');

// Buat Antrean SPK Dapur
Route::post('/production-batch', [ProductionBatchController::class, 'store'])->name('production-batch.store');

// Terima ke Fisik Gudang
Route::patch('/production-batch/{id}/accept', [ProductionBatchController::class, 'acceptHandover'])->name('production-batch.accept');

// Rute Input Transaksi Masuk Bahan Baku
Route::post('/raw-materials/purchase', [MaterialPurchaseController::class, 'store'])->name('raw-materials.purchase.store');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/finished-goods', [FinishedGoodsController::class, 'index'])->name('finished-goods.index');
Route::get('/raw-materials', [RawMaterialController::class, 'index'])->name('raw-materials.index');