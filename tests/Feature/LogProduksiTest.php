<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DetailAlokasiPo;
use App\Models\DetailProduksi;
use App\Models\LogProduksi;
use App\Models\PoProduk;
use App\Models\ProdukJadi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogProduksiTest extends TestCase
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

    public function test_po_batch_rejects_lowering_qty_below_po_order(): void
    {
        $user = $this->makeOwner();

        $produk = ProdukJadi::create([
            'nama_produk' => 'Rengginang Bawang',
            'varian' => '100g',
            'netto' => '100g',
            'harga_jual' => 15000,
            'stok' => 0,
        ]);

        $po = PoProduk::create([
            'nama_pelanggan' => 'Toko Berkah',
            'nama_penerima' => 'Budi',
            'tipe_jual' => 'Manual',
            'jumlah_po' => 100,
            'tgl_po' => now()->toDateString(),
            'tgl_jatuh_tempo_po' => now()->addDays(3)->toDateString(),
            'status_po' => 'PENDING',
            'alamat_pelanggan' => 'Condet',
            'sumber_transaksi' => 'MANUAL',
        ]);

        DetailAlokasiPo::create([
            'id_produk_jadi' => $produk->id_produk_jadi,
            'no_po_produk' => $po->no_po_produk,
            'jml_alokasi' => 100,
        ]);

        // Attempt to lower qty from 100 down to 40 -> must be rejected
        $response = $this->actingAs($user)
            ->from('/log-produksi')
            ->post('/log-produksi/store', [
                'mode_produksi' => 'po',
                'no_po_produk' => $po->no_po_produk,
                'tgl_produksi' => now()->toDateString(),
                'produk_ids' => [$produk->id_produk_jadi],
                'produk_qty' => [40],
            ]);

        $response->assertRedirect('/log-produksi');
        $response->assertSessionHasErrors('produk_qty');
        $this->assertSame(0, LogProduksi::count());
        $this->assertSame(0, DetailProduksi::count());

        // Scheduling with full locked PO qty (100) succeeds
        $this->actingAs($user)
            ->post('/log-produksi/store', [
                'mode_produksi' => 'po',
                'no_po_produk' => $po->no_po_produk,
                'tgl_produksi' => now()->toDateString(),
                'produk_ids' => [$produk->id_produk_jadi],
                'produk_qty' => [100],
            ])
            ->assertRedirect(route('log-produksi.index'));

        $this->assertDatabaseHas('log_produksi', [
            'no_po_produk' => $po->no_po_produk,
        ]);
        $this->assertDatabaseHas('detail_produksi', [
            'id_produk_jadi' => $produk->id_produk_jadi,
            'jml_produk_jadi' => 100,
        ]);
    }

    public function test_po_batch_enforces_remaining_qty_when_partial_approved_batch_exists(): void
    {
        $user = $this->makeOwner();

        $produk = ProdukJadi::create([
            'nama_produk' => 'Rengginang Udang',
            'varian' => '100g',
            'netto' => '100g',
            'harga_jual' => 18000,
            'stok' => 0,
        ]);

        $po = PoProduk::create([
            'nama_pelanggan' => 'Agen Jaya',
            'nama_penerima' => 'Sari',
            'tipe_jual' => 'Konsinyasi',
            'jumlah_po' => 100,
            'tgl_po' => now()->toDateString(),
            'tgl_jatuh_tempo_po' => now()->addDays(2)->toDateString(),
            'status_po' => 'PROSES',
            'alamat_pelanggan' => 'Kramat Jati',
            'sumber_transaksi' => 'MANUAL',
        ]);

        DetailAlokasiPo::create([
            'id_produk_jadi' => $produk->id_produk_jadi,
            'no_po_produk' => $po->no_po_produk,
            'jml_alokasi' => 100,
        ]);

        $batchDone = LogProduksi::create([
            'no_po_produk' => $po->no_po_produk,
            'tgl_produksi' => now()->subDay()->toDateString(),
            'jml_grade_A' => 40,
            'jml_grade_B' => 0,
            'status_approval' => 'APPROVED',
        ]);

        DetailProduksi::create([
            'no_batch' => $batchDone->no_batch,
            'id_produk_jadi' => $produk->id_produk_jadi,
            'jml_produk_jadi' => 40,
        ]);

        // Remaining needed is 60; trying 50 must fail
        $this->actingAs($user)
            ->from('/log-produksi')
            ->post('/log-produksi/store', [
                'mode_produksi' => 'po',
                'no_po_produk' => $po->no_po_produk,
                'tgl_produksi' => now()->toDateString(),
                'produk_ids' => [$produk->id_produk_jadi],
                'produk_qty' => [50],
            ])
            ->assertRedirect('/log-produksi')
            ->assertSessionHasErrors('produk_qty');

        // Exact remaining 60 succeeds
        $this->actingAs($user)
            ->post('/log-produksi/store', [
                'mode_produksi' => 'po',
                'no_po_produk' => $po->no_po_produk,
                'tgl_produksi' => now()->toDateString(),
                'produk_ids' => [$produk->id_produk_jadi],
                'produk_qty' => [60],
            ])
            ->assertRedirect(route('log-produksi.index'));

        $this->assertSame(2, LogProduksi::count());
    }

    public function test_log_produksi_page_includes_po_stock_lock_controls(): void
    {
        $user = $this->makeOwner();

        $this->actingAs($user)->get('/log-produksi')
            ->assertStatus(200)
            ->assertSee('Terkunci sesuai PO')
            ->assertSee('id="batch-po-lock-badge"', false)
            ->assertSee('id="batch-po-lock-note"', false)
            ->assertSee('qtyInput.readOnly = true', false);
    }
}
