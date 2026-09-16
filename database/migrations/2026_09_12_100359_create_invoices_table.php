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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->string('invoice_number', 50)->unique();
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        $table->decimal('amount_due', 12, 2);
        $table->enum('payment_status', ['UNPAID', 'PARTIAL_DP', 'PAID_GATEWAY', 'PAID_CONFIRMED'])->default('UNPAID');
        $table->timestamp('issued_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
