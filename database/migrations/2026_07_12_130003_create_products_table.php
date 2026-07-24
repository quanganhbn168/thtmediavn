<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->string('slug', 255)->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->longText('usage')->nullable();
            $table->unsignedInteger('sold_count')->default(0);
            $table->string('status', 30)->default('active');
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_preorder')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_category_id', 'is_active']);
            $table->index(['brand_id', 'is_active']);
            $table->index(['status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
