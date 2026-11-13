<?php

namespace App\Providers;

use App\Models\BahanProduksi;
use App\Models\PoProduk;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $stokKritisCount = Schema::hasTable('bahan_produksi')
                ? BahanProduksi::where('qty_stok', '<=', 10)->count()
                : 0;

            $poActiveCount = Schema::hasTable('po_produk')
                ? PoProduk::whereIn('status_po', ['PENDING', 'PROSES', 'DIKIRIM'])
                    ->where('nama_pelanggan', '!=', 'STOK INTERNAL GUDANG')
                    ->count()
                : 0;

            $view->with([
                'navStokKritisCount' => $stokKritisCount,
                'navPoActiveCount' => $poActiveCount,
            ]);
        });

        View::composer('dashboard', function ($view) {
            $lowStockMaterials = Schema::hasTable('bahan_produksi')
                ? BahanProduksi::where('qty_stok', '<=', 10)
                    ->orderBy('qty_stok', 'asc')
                    ->take(6)
                    ->get()
                : collect();

            $view->with('lowStockMaterials', $lowStockMaterials);
        });
    }
}
