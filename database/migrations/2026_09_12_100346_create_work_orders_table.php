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
    Schema::create('work_orders', function (Blueprint $table) {
        $table->id();
        $table->string('spk_number', 50)->unique();
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        $table->integer('target_qty');
        $table->string('kitchen_area', 50);
        // UBAH DARI ENUM MENJADI STRING (SANGAT DIREKOMENDASIKAN AGAR TIDAK BOUNDED ERROR LAGI)
        $table->string('status', 50)->default('SELESAI');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
