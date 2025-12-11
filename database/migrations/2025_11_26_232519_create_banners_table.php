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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_url');
            $table->string('link')->nullable();
            $table->string('position')->default('home_slider'); // home_slider, home_mid, sidebar...
            $table->integer('priority')->default(0); // Dùng để sắp xếp
            $table->boolean('is_active')->default(true);
            $table->text('content')->nullable(); // Mô tả thêm nếu cần
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
