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

            // Liên kết sản phẩm
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');

            // Liên kết người thực hiện (Admin/User)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // --- CÁC CỘT SỐ LƯỢNG (QUAN TRỌNG) ---
            $table->integer('old_quantity')->default(0)->comment('Tồn kho trước khi giao dịch'); // Bổ sung
            $table->integer('change_amount')->comment('Số lượng thay đổi (+/-)'); 
            $table->integer('new_quantity')->comment('Tồn kho sau khi giao dịch'); // Đổi tên từ remaining_stock cho khớp Model

            // --- LOẠI GIAO DỊCH ---
            $table->string('type')->index(); // sale, import, check, export...

            // --- THAM CHIẾU (Polymorphic) ---
            // Bổ sung reference_type để sửa lỗi SQLSTATE[42S22]
            $table->string('reference_type')->nullable()->index()->comment('purchase_order, order, manual...'); 
            $table->unsignedBigInteger('reference_id')->nullable()->index(); // ID của đơn hàng/phiếu nhập

            $table->text('note')->nullable(); // Đổi thành text để lưu ghi chú dài
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