<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // --- Thông tin đăng nhập & định danh ---
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->unique()->nullable();
            
            // --- CÁC CỘT BẠN ĐANG THIẾU (Cần thêm vào đây) ---
            $table->string('avatar')->nullable();       // Ảnh đại diện
            $table->string('gender')->nullable();       // Giới tính (male, female, other)
            $table->date('birthday')->nullable();       // Ngày sinh
            $table->string('address')->nullable();      // Địa chỉ (nếu muốn lưu trực tiếp vào bảng user)
            // -------------------------------------------------

            // --- Phân quyền & Trạng thái ---
            $table->string('role')->default('customer'); // admin, staff, customer
            $table->string('status')->default('active'); // active, banned
            
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};