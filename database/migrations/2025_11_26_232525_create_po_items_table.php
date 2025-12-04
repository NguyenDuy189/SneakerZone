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
        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            // Dùng cascade để khi xóa phiếu nhập thì xóa luôn chi tiết
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            
            $table->integer('quantity');
            $table->decimal('import_price', 15, 0); // Giá nhập đơn vị
            $table->decimal('total', 15, 0); // Mới: Tổng tiền dòng này (qty * price)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_items');
    }
};
