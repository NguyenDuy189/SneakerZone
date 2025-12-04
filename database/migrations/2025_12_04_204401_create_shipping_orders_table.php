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
        Schema::create('shipping_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipper_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, assigned, picking, delivering, delivered, failed, returned
            $table->string('current_location')->nullable(); // JSON hoặc string "lat,lng"
            $table->string('tracking_code')->nullable()->unique();
            $table->date('expected_delivery_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_orders');
    }
};
