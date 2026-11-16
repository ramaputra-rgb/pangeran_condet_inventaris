<?php

namespace Tests\Feature;

use App\Models\BahanProduksi;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawMaterialsTest extends TestCase
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

    public function test_raw_material_name_is_stored_lowercase_and_displayed_pascal_case(): void
    {
        $user = $this->makeOwner();

        $response = $this->actingAs($user)->post('/raw-materials/material/store', [
            'nama_bahan' => '  gAs   pInK  ',
            'tipe' => 'kering',
            'ukuran' => '5kg/gas',
            'qty_stok' => 4,
        ]);

        $response->assertRedirect(route('raw-materials.index'));

        $this->assertDatabaseHas('bahan_produksi', [
            'nama_bahan' => 'gas pink',
            'ukuran' => '5kg/gas',
        ]);

        $rawValue = DB::table('bahan_produksi')->value('nama_bahan');
        $this->assertSame('gas pink', $rawValue);

        $model = BahanProduksi::firstOrFail();
        $this->assertSame('Gas Pink', $model->nama_bahan);

        $this->actingAs($user)->get('/raw-materials')
            ->assertStatus(200)
            ->assertSee('Gas Pink')
            ->assertDontSee('>gas pink<', false);

        $this->actingAs($user)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Gas Pink');
    }

    public function test_duplicate_raw_material_name_cannot_be_created_regardless_of_case_or_unit(): void
    {
        $user = $this->makeOwner();

        BahanProduksi::create([
            'nama_bahan' => 'Gas Pink',
            'tipe' => 'kering',
            'ukuran' => '5kg/gas',
            'qty_stok' => 2,
        ]);

        foreach (['gas pink', 'GAS PINK', 'Gas Pink', '  gas   pink  '] as $duplicateInput) {
            $response = $this->actingAs($user)
                ->from('/raw-materials')
                ->post('/raw-materials/material/store', [
                    'nama_bahan' => $duplicateInput,
                    'tipe' => 'basah',
                    'ukuran' => 'kg',
                    'qty_stok' => 10,
                ]);

            $response->assertRedirect('/raw-materials');
            $response->assertSessionHasErrors('nama_bahan');
        }

        $this->assertSame(1, BahanProduksi::count());
    }

    public function test_update_material_allows_same_name_for_self_and_blocks_duplicate_other_name(): void
    {
        $user = $this->makeOwner();

        $gasPink = BahanProduksi::create([
            'nama_bahan' => 'gas pink',
            'tipe' => 'kering',
            'ukuran' => '5kg/gas',
            'qty_stok' => 2,
        ]);

        $gasMelon = BahanProduksi::create([
            'nama_bahan' => 'gas melon',
            'tipe' => 'kering',
            'ukuran' => '3kg/gas',
            'qty_stok' => 5,
        ]);

        // Updating self with same name (different casing) & updated stock succeeds
        $this->actingAs($user)
            ->put('/raw-materials/material/'.$gasPink->id_bahan, [
                'nama_bahan' => 'GAS PINK',
                'tipe' => 'kering',
                'ukuran' => '5kg/tabung',
                'qty_stok' => 8,
            ])
            ->assertRedirect(route('raw-materials.index'));

        $this->assertDatabaseHas('bahan_produksi', [
            'id_bahan' => $gasPink->id_bahan,
            'nama_bahan' => 'gas pink',
            'ukuran' => '5kg/tabung',
            'qty_stok' => 8,
        ]);

        // Renaming to another existing material's name fails
        $this->actingAs($user)
            ->from('/raw-materials')
            ->put('/raw-materials/material/'.$gasPink->id_bahan, [
                'nama_bahan' => 'Gas Melon',
                'tipe' => 'kering',
                'ukuran' => '5kg/tabung',
                'qty_stok' => 8,
            ])
            ->assertRedirect('/raw-materials')
            ->assertSessionHasErrors('nama_bahan');

        $this->assertSame('gas pink', DB::table('bahan_produksi')->where('id_bahan', $gasPink->id_bahan)->value('nama_bahan'));
        $this->assertSame('gas melon', DB::table('bahan_produksi')->where('id_bahan', $gasMelon->id_bahan)->value('nama_bahan'));
    }

    public function test_database_unique_constraint_prevents_duplicate_lowercase_names(): void
    {
        BahanProduksi::create([
            'nama_bahan' => 'Gas Pink',
            'tipe' => 'kering',
            'ukuran' => '5kg/gas',
            'qty_stok' => 2,
        ]);

        $this->expectException(QueryException::class);

        BahanProduksi::create([
            'nama_bahan' => 'gas pink',
            'tipe' => 'kering',
            'ukuran' => 'kg',
            'qty_stok' => 3,
        ]);
    }
}
