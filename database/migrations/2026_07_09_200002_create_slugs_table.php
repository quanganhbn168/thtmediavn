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
        Schema::create('slugs', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('sluggable_type');
            $table->unsignedBigInteger('sluggable_id');
            $table->string('locale', 10);
            $table->timestamps();

            // Ràng buộc duy nhất theo slug + locale để tối ưu định tuyến SEO
            $table->unique(['slug', 'locale']);
            $table->index(['sluggable_type', 'sluggable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slugs');
    }
};
