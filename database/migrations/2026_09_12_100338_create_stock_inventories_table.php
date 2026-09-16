<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('stock_inventories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->unique()->constrained('products')->onDelete('cascade');
        $table->integer('total_physical_stock')->default(0);
        $table->integer('reserved_stock')->default(0);
        $table->integer('atp_stock')->default(0); // Available to Promise
        $table->enum('status_gudang', ['STOK_AMAN', 'PERHATIAN', 'ORDER_SPK'])->default('STOK_AMAN');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_inventories');
    }
};
