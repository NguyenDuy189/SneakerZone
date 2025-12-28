<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa bảng cũ nếu tồn tại để tránh lỗi trùng lặp
        Schema::dropIfExists('cart_items');

        // Tạo lại bảng mới với cấu trúc chuẩn
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');

            // QUAN TRỌNG: Tên bảng đúng là 'product_variants'
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            
            $table->decimal('price', 15, 0)->default(0); // Đã có cột price
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};