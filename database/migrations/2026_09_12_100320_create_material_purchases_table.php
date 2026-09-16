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
    Schema::create('material_purchases', function (Blueprint $table) {
        $table->id();
        $table->string('po_number', 50)->unique();
        $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
        $table->foreignId('raw_material_id')->constrained('raw_materials')->onDelete('cascade');
        $table->decimal('qty_received', 10, 2);
        $table->decimal('unit_price', 12, 2);
        $table->decimal('total_price', 12, 2);
        $table->date('purchase_date');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_purchases');
    }
};
