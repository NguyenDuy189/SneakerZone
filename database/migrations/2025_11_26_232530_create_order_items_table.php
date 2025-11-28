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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Set Null khi SP bị xóa cứng để giữ lịch sử
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            
            // Các cột Snapshot (Lưu cứng dữ liệu)
            $table->string('product_name');
            $table->string('sku');
            $table->string('thumbnail')->nullable();
            
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('total_line', 15, 2);
            // Không cần timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
