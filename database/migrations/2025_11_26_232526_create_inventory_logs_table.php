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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users'); // Mới: Ai làm thay đổi kho?
            
            $table->integer('old_quantity')->default(0); // Mới: Tồn trước khi đổi (để đối soát)
            $table->integer('change_amount'); // +10 hoặc -5
            $table->integer('new_quantity');  // Tồn sau khi đổi (remaining_stock)
            
            $table->string('type'); // sale (bán), import (nhập), return (trả), check (kiểm kê)
            $table->unsignedBigInteger('reference_id')->nullable(); // ID đơn hàng hoặc PO
            $table->string('note')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
