<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('combos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('combo_category_id')->nullable()->constrained('combo_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug', 255)->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('compare_price', 15, 2)->nullable();
            $table->unsignedInteger('sold_count')->default(0);
            $table->string('status', 30)->default('active');
            $table->boolean('allow_preorder')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['combo_category_id', 'is_active']);
            $table->index(['status', 'is_active']);
        });

        Schema::create('combo_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['combo_id', 'sort_order']);
            $table->index(['product_id', 'product_variant_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('combo_items');
        Schema::dropIfExists('combos');
        Schema::dropIfExists('combo_categories');
    }
};
