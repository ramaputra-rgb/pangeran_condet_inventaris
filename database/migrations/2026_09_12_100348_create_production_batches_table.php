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
    Schema::create('production_batches', function (Blueprint $table) {
        $table->id();
        $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
        $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
        $table->string('batch_code');
        $table->integer('total_dapur_qty')->default(0);
        $table->integer('net_good_qty')->default(0);
        $table->integer('actual_net_good_qty')->default(0);
        $table->date('production_date')->nullable();
        $table->date('expired_date')->nullable();
        
        // GANTI TIPE ENUM MENJADI STRING AGAR TIDAK BOUNDED ERROR
        $table->string('handover_status', 50)->default('PENDING'); 
        
        $table->timestamp('received_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};
