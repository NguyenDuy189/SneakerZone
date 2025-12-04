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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PO-20241025-001
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('user_id')->constrained('users'); // Mới: Ai tạo phiếu này?
            
            $table->string('status')->default('pending'); // pending, completed, cancelled
            
            $table->decimal('total_amount', 15, 0)->default(0); // VND dùng 0 số thập phân
            $table->text('note')->nullable(); // Mới: Ghi chú nhập hàng
            $table->timestamp('expected_at')->nullable(); // Mới: Ngày dự kiến hàng về
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
