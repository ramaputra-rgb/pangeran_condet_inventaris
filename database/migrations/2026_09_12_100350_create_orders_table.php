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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('order_number', 50)->unique();
        $table->enum('channel', ['B2B_GROSIR', 'WEBSTORE', 'POS_WALKIN']);
        $table->string('customer_name', 100);
        $table->enum('physical_status', ['PO_ALLOCATED', 'SPK_PROCESS', 'QC_PASS', 'SHIPPED', 'DELIVERED'])->default('PO_ALLOCATED');
        $table->enum('payment_status', ['UNPAID', 'DP_50', 'PAID'])->default('UNPAID');
        $table->decimal('total_amount', 12, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
