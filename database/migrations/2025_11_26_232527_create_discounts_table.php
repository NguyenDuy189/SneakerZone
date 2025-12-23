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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
        
            // --- BẠN ĐANG THIẾU HOẶC SAI DÒNG NÀY ---
            $table->string('code')->unique(); 
            // ----------------------------------------

            $table->string('type')->default('percentage'); 
            $table->decimal('value', 15, 2);
            $table->integer('max_usage')->default(0);
            $table->integer('used_count')->default(0);
            $table->decimal('min_order_amount', 15, 2)->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
