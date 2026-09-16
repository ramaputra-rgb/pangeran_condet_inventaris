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
    Schema::create('suppliers', function (Blueprint $table) {
        $table->id();
        $table->string('supplier_code', 20)->unique();
        $table->string('name', 100);
        $table->string('phone', 20)->nullable();
        $table->string('payment_terms', 50)->default('Cash on Delivery');
        $table->decimal('rating', 4, 1)->default(100.0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
