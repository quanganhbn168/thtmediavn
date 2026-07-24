<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_value_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_attribute_value_id');
            $table->primary(['product_id', 'product_attribute_value_id']);

            $table->foreign('product_id', 'fk_pavp_product')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_attribute_value_id', 'fk_pavp_value')
                ->references('id')
                ->on('product_attribute_values')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value_product');
    }
};
