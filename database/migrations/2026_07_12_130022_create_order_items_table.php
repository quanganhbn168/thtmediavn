<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('item_type');
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('product_variant_id')->nullable();
                $table->string('item_name', 255);
                $table->string('product_name', 255)->nullable();
                $table->string('variant_name', 255)->nullable();
                $table->string('sku', 100)->nullable();
                $table->string('image', 255)->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_price', 15, 2)->default(0);
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
                $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
                $table->index(['item_type', 'item_id']);
            });
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable();
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            }
            if (! Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->unsignedBigInteger('product_variant_id')->nullable();
                $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
            }
            if (! Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name', 255)->nullable();
            }
            if (! Schema::hasColumn('order_items', 'variant_name')) {
                $table->string('variant_name', 255)->nullable();
            }
            if (! Schema::hasColumn('order_items', 'sku')) {
                $table->string('sku', 100)->nullable();
            }
            if (! Schema::hasColumn('order_items', 'image')) {
                $table->string('image', 255)->nullable();
            }
            if (! Schema::hasColumn('order_items', 'total_price')) {
                $table->decimal('total_price', 15, 2)->default(0);
            }
            if (! Schema::hasColumn('order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0);
            }
            if (! Schema::hasColumn('order_items', 'note')) {
                $table->text('note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
