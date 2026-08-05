<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('order_item_combo_components');

        Schema::create('order_item_combo_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('combo_id')->nullable()->constrained('combos')->nullOnDelete();
            $table->foreignId('component_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('component_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('component_product_name')->nullable();
            $table->string('component_variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('stock_reserved')->default(false);
            $table->timestamps();
            $table->index(['order_item_id', 'stock_reserved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_combo_components');
    }
};
