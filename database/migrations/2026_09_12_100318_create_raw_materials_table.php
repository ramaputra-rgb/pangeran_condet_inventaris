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
    Schema::create('raw_materials', function (Blueprint $table) {
        $table->id();
        $table->string('code', 20)->unique();
        $table->string('name', 100);
        $table->decimal('stock_qty', 10, 2)->default(0);
        $table->decimal('mss_limit', 10, 2)->default(0); // Minimum Safety Stock
        $table->string('unit', 10); // Kg, Ltr, Pcs
        $table->enum('status', ['STOK_AMAN', 'PERHATIAN', 'STOK_KRITIS'])->default('STOK_AMAN');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_materials');
    }
};
