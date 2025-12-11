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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
            ->constrained('categories')
            ->restrictOnDelete(); // CẤM XÓA danh mục nếu có sản phẩm

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku_code')->unique()->nullable(); // Mã quản lý chung
            $table->foreignId('brand_id')->constrained('brands'); // Chỉ link Brand, Category tách riêng
            
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('gallery')->nullable();
            
            $table->decimal('price_min', 15, 2)->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
