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
        Schema::create('slider_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slider_id')->constrained()->onDelete('cascade');
            $table->json('title')->nullable(); // Tiêu đề phụ đề đa ngôn ngữ
            $table->json('sub_title')->nullable();
            $table->json('buttons')->nullable(); // Lưu trữ mảng tối đa 2 nút bấm có dịch thuật
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slider_items');
    }
};
