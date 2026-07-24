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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_category_id')->constrained()->onDelete('cascade');
            $table->json('name'); // Tiêu đề bài viết đa ngôn ngữ
            $table->json('summary')->nullable();
            $table->json('content')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
