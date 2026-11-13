<?php

namespace Tests\Feature;

use App\Models\BahanProduksi;
use App\Models\DetailAlokasiPo;
use App\Models\DetailProduksi;
use App\Models\LogPengiriman;
use App\Models\LogProduksi;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use App\Models\SuratJalan;
use App\Models\TransaksiBeliBahan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewsRenderTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwner(): User
    {
        return User::create([
            'name' => 'Owner Uji',
            'username' => 'owner_test',
            'email' => 'owner_test@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'owner',
        ]);
    }

    public function test_login_page_loads_editorial_assets(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('rengginang-login.jpg');
        $response->assertSee('Bahan baku');
        $response->assertSee('Antrean PO');
        $response->assertSee('Pengiriman');
        $response->assertDontSee('Kendali bahan baku, penggorengan, dan kirim dalam satu alur.');
        $response->assertDontSee('Pantau stok bahan baku dapur, susun prioritas Purchase Order, input hasil QC per batch, dan terbitkan Surat Jalan dengan rapi.');
        $response->assertSee('Username atau Email');
        $response->assertSee('Kata Sandi');
        $response->assertSee('tokens.css');
        $response->assertSee('custom-style.css');
    }

    public function test_all_primary_authenticated_views_render_cleanly_when_empty(): void
    {
        $user = $this->makeOwner();

        foreach ([
            '/dashboard',
            '/raw-materials',
            '/finished-goods',
            '/po-produk',
            '/log-produksi',
            '/shipments',
        ] as $uri) {
            $response = $this->actingAs($user)->get($uri);
            $response->assertStatus(200);
            $response->assertSee('tokens.css');
            $response->assertSee('app-ui.js');
            $response->assertSee('data-nav-toggle', false);
            $response->assertSee('id="app-sidebar"', false);
        }
    }

    public function test_all_primary_authenticated_views_render_cleanly_with_populated_records(): void
    {
        $user = $this->makeOwner();

        $bahan = BahanProduksi::create([
            'nama_bahan' => 'Beras Ketan Super',
            'tipe' => 'kering',
            'ukuran' => 'kg',
            'qty_stok' => 4,
        ]);

        TransaksiBeliBahan::create([
            'nama_supplier' => 'Agen Murni',
            'total_qty' => 20,
            'total_harga' => 300000,
            'total_ongkir' => 15000,
            'id_bahan' => $bahan->id_bahan,
        ]);

        $produk = ProdukJadi::create([
            'nama_produk' => 'Rengginang Bawang',
            'varian' => 'Original',
            'netto' => '150g',
            'harga_jual' => 18000,
            'stok' => 120,
        ]);

        $sj = SuratJalan::create([
            'tgl_terbit' => now()->toDateString(),
            'nama_penerima' => 'Ibu Sari',
            'nama_kurir' => 'Kurir Toko',
            'ket' => '2 dus rapi',
        ]);

        $poPending = PoProduk::create([
            'nama_pelanggan' => 'Toko Berkah',
            'nama_penerima' => 'Bapak Budi',
            'tipe_jual' => 'E-Commerce',
            'jumlah_po' => 150,
            'tgl_po' => now()->toDateString(),
            'tgl_jatuh_tempo_po' => now()->addDays(2)->toDateString(),
            'status_po' => 'PENDING',
            'alamat_pelanggan' => 'Condet Jakarta Timur',
            'nomor_pesanan_ecommerce' => 'EC-260918',
            'sumber_transaksi' => 'MANUAL',
        ]);

        $poDikirim = PoProduk::create([
            'nama_pelanggan' => 'Agen Kramat Jati',
            'nama_penerima' => 'Ibu Sari',
            'tipe_jual' => 'Konsinyasi',
            'jumlah_po' => 60,
            'tgl_po' => now()->subDays(2)->toDateString(),
            'tgl_jatuh_tempo_po' => now()->addDay()->toDateString(),
            'status_po' => 'DIKIRIM',
            'alamat_pelanggan' => 'Kramat Jati',
            'no_ref' => $sj->no_ref,
            'sumber_transaksi' => 'MANUAL',
        ]);

        DetailAlokasiPo::create([
            'id_produk_jadi' => $produk->id_produk_jadi,
            'no_po_produk' => $poPending->no_po_produk,
            'jml_alokasi' => 150,
        ]);

        DetailAlokasiPo::create([
            'id_produk_jadi' => $produk->id_produk_jadi,
            'no_po_produk' => $poDikirim->no_po_produk,
            'jml_alokasi' => 60,
        ]);

        $batchOpen = LogProduksi::create([
            'tgl_produksi' => now()->toDateString(),
            'waktu_produksi' => 'Shift Pagi',
            'jml_grade_A' => 0,
            'jml_grade_B' => 0,
            'no_po_produk' => $poPending->no_po_produk,
            'status_approval' => 'PENDING',
            'ket' => 'Goreng awal',
        ]);

        DetailProduksi::create([
            'id_produk_jadi' => $produk->id_produk_jadi,
            'no_batch' => $batchOpen->no_batch,
            'jml_produk_jadi' => 100,
        ]);

        $batchDone = LogProduksi::create([
            'tgl_produksi' => now()->subDay()->toDateString(),
            'waktu_produksi' => 'Shift Siang',
            'jml_grade_A' => 80,
            'jml_grade_B' => 4,
            'no_po_produk' => $poDikirim->no_po_produk,
            'status_approval' => 'APPROVED',
            'ket' => 'Lulus QC',
        ]);

        DetailProduksi::create([
            'id_produk_jadi' => $produk->id_produk_jadi,
            'no_batch' => $batchDone->no_batch,
            'jml_produk_jadi' => 84,
        ]);

        LogPengiriman::create([
            'Tanggal' => now()->toDateString(),
            'no_po_produk' => $poDikirim->no_po_produk,
            'status_pengiriman' => 'DIKIRIM',
        ]);

        $this->actingAs($user)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Beras Ketan Super')
            ->assertSee('Toko Berkah');

        $this->actingAs($user)->get('/raw-materials')
            ->assertStatus(200)
            ->assertSee('Beras Ketan Super')
            ->assertSee('Agen Murni');

        $this->actingAs($user)->get('/finished-goods')
            ->assertStatus(200)
            ->assertSee('Rengginang Bawang');

        $this->actingAs($user)->get('/po-produk')
            ->assertStatus(200)
            ->assertSee('Toko Berkah')
            ->assertSee('EC-260918');

        $this->actingAs($user)->get('/log-produksi')
            ->assertStatus(200)
            ->assertSee('Toko Berkah')
            ->assertSee('Shift Pagi');

        $this->actingAs($user)->get('/shipments')
            ->assertStatus(200)
            ->assertSee('Agen Kramat Jati')
            ->assertSee('Kurir Toko');
    }

    public function test_secondary_views_render_cleanly_as_blade_templates(): void
    {
        $user = $this->makeOwner();
        $this->be($user);

        $this->view('reports.index', [
            'logCetakList' => collect(),
        ])->assertSee('Pusat laporan operasional');

        $this->view('production.bom_recipes', [
            'products' => collect(),
            'rawMaterials' => collect(),
            'totalFormula' => 0,
            'totalBahanBaku' => 0,
        ])->assertSee('Master formula &amp; standar BoM', false);

        $this->view('production.work_orders', [
            'workOrders' => collect(),
            'products' => collect(),
            'rawMaterials' => collect(),
            'spkProsesCount' => 0,
            'spkSelesaiCount' => 0,
            'totalTargetBatch' => 0,
        ])->assertSee('Perintah kerja penggorengan');
    }
}
