<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained('product_options')->cascadeOnDelete();
            $table->string('value', 120);
            $table->string('slug', 120);
            $table->string('color_code', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_option_id', 'slug']);
            $table->index('product_option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_values');
    }
};
