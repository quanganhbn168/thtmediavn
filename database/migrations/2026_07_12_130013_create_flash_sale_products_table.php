<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('sold')->default(0);
            $table->unique(['flash_sale_id', 'product_id', 'product_variant_id'], 'flash_sale_products_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_products');
    }
};
