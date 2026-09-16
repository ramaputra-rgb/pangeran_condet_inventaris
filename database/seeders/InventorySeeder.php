<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\StockInventory;
use App\Models\Bom;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Supplier
        $supplier = Supplier::create([
            'supplier_code' => 'SUP-001',
            'name' => 'Toko Hasil Laut Muara Baru',
            'phone' => '0812-9899-2211',
            'payment_terms' => 14,
            'rating' => 98.0,
        ]);

        // 2. Seed Raw Material (Bahan Baku)
        $ikan = RawMaterial::create([
            'code' => 'BB-001',
            'name' => 'Ikan Tenggiri Segar Giling',
            'stock_qty' => 120.00,
            'mss_limit' => 200.00,
            'unit' => 'Kg',
            'status' => 'STOK_KRITIS',
        ]);

        // 3. Seed Product (Finished Goods)
        $product = Product::create([
            'sku' => 'RNG-TNG-250G',
            'name' => 'Rengginang Ikan Tenggiri Gurih 250g (Pouch)',
            'gramature' => 250,
            'price' => 25000.00,
        ]);

        // 4. Seed Stock Inventory
        StockInventory::create([
            'product_id' => $product->id,
            'total_physical_stock' => 5000,
            'reserved_stock' => 1400,
            'atp_stock' => 3600,
            'status_gudang' => 'STOK_AMAN',
        ]);

        // 5. Seed BoM (Resep: 1 Pcs Rengginang butuh 0.15 Kg Ikan Tenggiri)
        Bom::create([
            'product_id' => $product->id,
            'raw_material_id' => $ikan->id,
            'required_qty' => 0.150,
        ]);
    }
}
