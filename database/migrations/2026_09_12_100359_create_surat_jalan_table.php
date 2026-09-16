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
    Schema::create('surat_jalan', function (Blueprint $table) {
        $table->id();
        $table->string('sj_number', 50)->unique();
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        $table->timestamp('shipped_at')->nullable();
        $table->enum('status', ['DEPARTED', 'DELIVERED_POD'])->default('DEPARTED');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalan');
    }
};
