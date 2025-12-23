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
        Schema::create('shipping_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_order_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // same statuses
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // ai cập nhật
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_logs');
    }
};
