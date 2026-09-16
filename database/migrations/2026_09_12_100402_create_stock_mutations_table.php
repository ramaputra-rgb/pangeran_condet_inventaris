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
    Schema::create('stock_mutations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        $table->enum('type', ['IN', 'OUT']);
        $table->integer('qty');
        $table->enum('reference_type', ['PRODUCTION_HANDOVER', 'SURAT_JALAN_DISPATCH', 'ADJUSTMENT']);
        $table->unsignedBigInteger('reference_id');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};
