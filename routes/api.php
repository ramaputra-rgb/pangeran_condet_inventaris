<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PoApiController;

/*
|--------------------------------------------------------------------------
| API Routes untuk Pangeran Condet Inventaris
|--------------------------------------------------------------------------
|
| Rute API REST untuk integrasi sistem, termasuk integrasi pesanan masuk
| dari web e-commerce menjadi Purchase Order (PO) otomatis.
|
*/

Route::prefix('v1')->group(function () {
    // Sinkronisasi pesanan e-commerce menjadi PO
    Route::post('/po/sync-order', [PoApiController::class, 'syncOrder'])->name('api.v1.po.sync');
    
    // Pengecekan status PO berdasarkan nomor pesanan
    Route::get('/po/check/{orderNumber}', [PoApiController::class, 'check'])->name('api.v1.po.check');
});
