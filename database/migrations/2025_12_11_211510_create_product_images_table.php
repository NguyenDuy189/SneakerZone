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
    Schema::create('product_images', function (Blueprint $table) {
        $table->id();
        // Liên kết khóa ngoại với bảng products
        // onDelete('cascade'): Nếu xóa sản phẩm, toàn bộ ảnh con sẽ tự xóa theo -> Tránh rác database
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        
        $table->string('image_path'); // Đường dẫn ảnh
        $table->integer('sort_order')->default(0); // Để sắp xếp thứ tự hiển thị ảnh nếu cần
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
