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
            // 1. IMPORT DATA RIIL BAHAN PRODUKSI (dari SQL dump)
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

            $bahanModels = [];
            foreach ($bahanData as $b) {
                $bahanModels[] = BahanProduksi::create($b);
            }

            // 2. IMPORT DATA RIIL TRANSAKSI BELI BAHAN
            TransaksiBeliBahan::create([
                'nama_supplier' => 'Agen Condet',
                'total_qty'     => 10,
                'total_harga'   => 360000,
                'total_ongkir'  => 0,
                'id_bahan'      => $bahanModels[14]->id_bahan, // Minyak Goreng
            ]);

            TransaksiBeliBahan::create([
                'nama_supplier' => 'Agen Gas Condet',
                'total_qty'     => 3,
                'total_harga'   => 60000,
                'total_ongkir'  => 5000,
                'id_bahan'      => $bahanModels[8]->id_bahan, // Gas Melon
            ]);

            TransaksiBeliBahan::create([
                'nama_supplier' => 'Toko Stiker Pasnen',
                'total_qty'     => 5,
                'total_harga'   => 150000,
                'total_ongkir'  => 0,
                'id_bahan'      => $bahanModels[10]->id_bahan, // Stiker Kecil
            ]);

            // 3. IMPORT DATA RIIL PRODUK JADI
            $produkData = [
                ['nama_produk' => 'Rengginang Udang 100g', 'varian' => 'Udang 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Rengginang Udang 360g', 'varian' => 'Udang 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 100],
                ['nama_produk' => 'Rengginang Bawang 100g', 'varian' => 'Bawang 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Rengginang Bawang 360g', 'varian' => 'Bawang 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 100],
                ['nama_produk' => 'Rengginang Cumi 100g', 'varian' => 'Cumi 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Rengginang Cumi 360g', 'varian' => 'Cumi 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 100],
                ['nama_produk' => 'Rengginang Ikan 100g', 'varian' => 'Ikan 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Rengginang Ikan 360g', 'varian' => 'Ikan 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 100],
                ['nama_produk' => 'Rengginang Pedas 100g', 'varian' => 'Pedas 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Rengginang Pedas 360g', 'varian' => 'Pedas 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 100],
                ['nama_produk' => 'Rengginang Ori 100g', 'varian' => 'Ori 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Rengginang Ori 360g', 'varian' => 'Ori 360g', 'netto' => '360g', 'harga_jual' => 37000, 'stok' => 100],
                ['nama_produk' => 'Emping Singkong Ori 100g', 'varian' => 'Emping Ori 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Emping Singkong Pedas 100g', 'varian' => 'Emping Pedas 100g', 'netto' => '100g', 'harga_jual' => 12500, 'stok' => 100],
                ['nama_produk' => 'Produk Remahan 500g', 'varian' => 'Remahan Grade B', 'netto' => '500g', 'harga_jual' => 10000, 'stok' => 50],
            ];

            $produkModels = [];
            foreach ($produkData as $p) {
                $produkModels[] = ProdukJadi::create($p);
            }

            // 4. IMPORT DATA SURAT JALAN
            $sj1 = SuratJalan::create([
                'tgl_terbit'    => '2026-09-26',
                'nama_penerima' => 'Yanto',
                'nama_kurir'    => 'Iman',
                'ket'           => 'Serahkan Ke SPV',
            ]);

            $sj2 = SuratJalan::create([
                'tgl_terbit'    => '2026-09-26',
                'nama_penerima' => 'Yanti',
                'nama_kurir'    => 'Iman',
                'ket'           => 'Serahkan Ke SPV',
            ]);

            // 5. IMPORT DATA PO PRODUK (Make-to-Order)
            $po1 = PoProduk::create([
                'nama_pelanggan'     => 'HERO Super Market',
                'tipe_jual'          => 'Grosir',
                'jumlah_po'          => 10,
                'tgl_jatuh_tempo_po' => '2026-09-26',
                'status_po'          => 'SELESAI',
                'alamat_pelanggan'   => 'Cakung, Jakarta Timur',
                'no_ref'             => $sj1->no_ref,
            ]);

            $po2 = PoProduk::create([
                'nama_pelanggan'     => 'FRESH Market',
                'tipe_jual'          => 'Grosir',
                'jumlah_po'          => 10,
                'tgl_jatuh_tempo_po' => '2026-09-27',
                'status_po'          => 'SELESAI',
                'alamat_pelanggan'   => 'Gancit, Jaksel',
                'no_ref'             => $sj2->no_ref,
            ]);

            // 6. IMPORT DATA LOG PRODUKSI (Batch QC)
            $log1 = LogProduksi::create([
                'tgl_produksi'    => '2026-09-26',
                'rentang_tgl_exp' => '2026-12-26',
                'waktu_produksi'  => '08:00:00',
                'jml_grade_A'     => 99,
                'jml_grade_B'     => 1,
                'no_po_produk'    => $po1->no_po_produk,
            ]);

            // Attach Pemakaian Bahan Baku di Batch 1
            $log1->bahanProduksi()->attach([
                $bahanModels[0]->id_bahan => ['qty_dipakai' => 20.00], // Rengginang Udang
            ]);

            // 7. DETAIL PRODUKSI & ALOKASI PO
            DetailProduksi::create([
                'id_produk_jadi'  => $produkModels[0]->id_produk_jadi, // Rengginang Udang 100g
                'no_batch'        => $log1->no_batch,
                'jml_produk_jadi' => 99,
            ]);

            DetailAlokasiPo::create([
                'id_produk_jadi' => $produkModels[0]->id_produk_jadi,
                'no_po_produk'   => $po1->no_po_produk,
                'jml_alokasi'    => 10,
            ]);

            // 8. LOG PENGIRIMAN
            LogPengiriman::create([
                'Tanggal'            => '2026-09-26',
                'no_po_produk'       => $po1->no_po_produk,
                'bukti_serah_terima' => 'serah_terima_001.jpeg',
                'status_pengiriman'  => 'SELESAI',
            ]);
        });
    }
}