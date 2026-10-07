<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanProduksi;
use App\Models\TransaksiBeliBahan;
use App\Models\SuratJalan;
use App\Models\PoProduk;
use App\Models\LogProduksi;
use App\Models\ProdukJadi;
use App\Models\DetailProduksi;
use App\Models\DetailAlokasiPo;
use App\Models\LogPengiriman;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. KATALOG MASTER BAHAN PRODUKSI (Stok Di-Nol-kan untuk Testing)
            $bahanData = [
                ['nama_bahan' => 'Rengginang Udang', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Rengginang Bawang', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Rengginang Cumi', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Rengginang Ikan', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Rengginang Pedas', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Rengginang Ori', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Emping Singkong Ori', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Emping Singkong Pedas', 'tipe' => 'kering', 'ukuran' => 'kg/karton', 'qty_stok' => 10],
                ['nama_bahan' => 'Gas Melon', 'tipe' => 'kering', 'ukuran' => '3kg/gas', 'qty_stok' => 5],
                ['nama_bahan' => 'Gas Pink', 'tipe' => 'kering', 'ukuran' => '5kg/gas', 'qty_stok' => 2],
                ['nama_bahan' => 'Stiker Kecil', 'tipe' => 'kering', 'ukuran' => '100/pack', 'qty_stok' => 5],
                ['nama_bahan' => 'Stiker Besar', 'tipe' => 'kering', 'ukuran' => '100/pack', 'qty_stok' => 5],
                ['nama_bahan' => 'P. Kemasan 100g', 'tipe' => 'kering', 'ukuran' => '/pack', 'qty_stok' => 5],
                ['nama_bahan' => 'P. Kemasan 360/200g', 'tipe' => 'kering', 'ukuran' => '/pack', 'qty_stok' => 5],
                ['nama_bahan' => 'Minyak Goreng 2L', 'tipe' => 'basah', 'ukuran' => 'Liter', 'qty_stok' => 20],
            ];

            foreach ($bahanData as $b) {
                BahanProduksi::create($b);
            }

            // 2. KATALOG MASTER PRODUK JADI / FINISHED GOODS (Stok Di-Nol-kan untuk Testing)
            $produkData = [
                ['nama_produk' => 'Rengginang Udang 100g', 'varian' => 'Udang 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Rengginang Udang 360g', 'varian' => 'Udang 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 0],
                ['nama_produk' => 'Rengginang Bawang 100g', 'varian' => 'Bawang 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Rengginang Bawang 360g', 'varian' => 'Bawang 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 0],
                ['nama_produk' => 'Rengginang Cumi 100g', 'varian' => 'Cumi 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Rengginang Cumi 360g', 'varian' => 'Cumi 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 0],
                ['nama_produk' => 'Rengginang Ikan 100g', 'varian' => 'Ikan 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Rengginang Ikan 360g', 'varian' => 'Ikan 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 0],
                ['nama_produk' => 'Rengginang Pedas 100g', 'varian' => 'Pedas 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Rengginang Pedas 360g', 'varian' => 'Pedas 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 0],
                ['nama_produk' => 'Rengginang Ori 100g', 'varian' => 'Ori 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Rengginang Ori 360g', 'varian' => 'Ori 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 0],
                ['nama_produk' => 'Emping Singkong Ori 100g', 'varian' => 'Emping Ori 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Emping Singkong Pedas 100g', 'varian' => 'Emping Pedas 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 0],
                ['nama_produk' => 'Produk Remahan 500g', 'varian' => 'Remahan Grade B', 'netto' => '500g', 'harga_jual' => 10000, 'stok' => 0],
            ];

            foreach ($produkData as $p) {
                ProdukJadi::create($p);
            }

            // Catatan: Seluruh data riwayat transaksi (PO, Log Produksi, Beli Bahan, Surat Jalan, Logistik) 
            // sengaja dikosongkan agar siap dipakai untuk pengujian alur otomatisasi dari nol (Fresh Testing).
        });
    }
}