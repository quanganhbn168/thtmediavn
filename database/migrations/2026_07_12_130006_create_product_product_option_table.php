<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_product_option', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_id')->constrained('product_options')->cascadeOnDelete();
            $table->primary(['product_id', 'product_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_option');
    }
};
