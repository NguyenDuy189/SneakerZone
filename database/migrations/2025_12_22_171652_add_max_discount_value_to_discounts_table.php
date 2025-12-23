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
        Schema::table('discounts', function (Blueprint $table) {
            // Thêm cột max_discount_value, cho phép null (để dùng cho trường hợp giảm theo tiền mặt thì không cần max)
            $table->decimal('max_discount_value', 15, 0)->nullable()->after('value')->comment('Số tiền giảm tối đa cho loại %');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('max_discount_value');
        });
    }
};