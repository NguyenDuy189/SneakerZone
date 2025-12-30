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
            
            // Khóa ngoại
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            
            $table->integer('quantity');
            
            // --- CHÚ Ý: Chỉ để 1 dòng này thôi ---
            $table->decimal('import_price', 15, 2); 
            // -------------------------------------

            $table->decimal('subtotal', 15, 2)->nullable(); // Nên để nullable đề phòng
            
            $table->timestamps();
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