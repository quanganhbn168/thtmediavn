<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255)->nullable();
            $table->string('sku', 100)->nullable()->unique();
            $table->string('barcode', 100)->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('compare_price', 15, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('weight')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
            $table->index(['product_id', 'is_default']);
            $table->index('is_active');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
