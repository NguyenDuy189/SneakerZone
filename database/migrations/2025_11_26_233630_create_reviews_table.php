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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // User và Product
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // --- PHẦN SỬA LẠI ---
            // Chỉ cần dòng này là đủ (vừa tạo cột, vừa nối khóa ngoại)
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null'); 
            // Lưu ý: Nên thêm onDelete('set null') để nếu xóa đơn hàng thì review vẫn còn nhưng order_id = null
            // --------------------

            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};