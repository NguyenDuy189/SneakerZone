<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_code')->unique();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Trạng thái đơn
            $table->string('status')->default('pending');
            $table->string('cancel_reason')->nullable();

            // Thanh toán
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->default('cod');
            $table->string('payment_gateway')->nullable(); // vnpay, momo, zalopay, shopeepay
            $table->string('payment_transaction_code')->nullable();

            // TIỀN – BẮT BUỘC THEO THỨ TỰ LOGIC
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->string('voucher_code')->nullable();
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            // Địa chỉ
            $table->foreignId('shipping_address_id')->nullable()->constrained('user_addresses');
            $table->foreignId('billing_address_id')->nullable()->constrained('user_addresses');
            $table->json('shipping_address');

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
