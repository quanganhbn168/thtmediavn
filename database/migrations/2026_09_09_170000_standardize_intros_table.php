<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intros', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->longText('content')->nullable();
            $table->string('link')->nullable();
            $table->string('link_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->string('kind')->default('article')->index();
            $table->string('slug')->nullable()->unique();
            $table->text('summary')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('keywords')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intros');
    }
};
