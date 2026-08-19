<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 50)->index();
            $table->json('name');
            $table->json('summary')->nullable();
            $table->json('intro')->nullable();
            $table->json('problems')->nullable();
            $table->json('audiences')->nullable();
            $table->json('work_items')->nullable();
            $table->json('deliverables')->nullable();
            $table->json('benefits')->nullable();
            $table->json('process_steps')->nullable();
            $table->json('faqs')->nullable();
            $table->string('video_url')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
