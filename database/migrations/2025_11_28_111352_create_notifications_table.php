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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tiêu đề thông báo
            $table->text('message')->nullable(); // Nội dung chi tiết
            
            // Loại thông báo để hiển thị màu sắc (info: xanh dương, success: xanh lá, warning: cam, danger: đỏ)
            $table->string('type')->default('info'); 
            
            $table->boolean('is_read')->default(false); // Trạng thái đã đọc hay chưa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
