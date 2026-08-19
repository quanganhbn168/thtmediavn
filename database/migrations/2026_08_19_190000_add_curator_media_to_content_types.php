<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->uuid('thumbnail_id')->nullable()->after('id');
            $table->uuid('banner_id')->nullable()->after('thumbnail_id');
            $table->uuid('share_image_id')->nullable()->after('banner_id');

            $table->foreign('thumbnail_id')->references('id')->on('curator')->nullOnDelete();
            $table->foreign('banner_id')->references('id')->on('curator')->nullOnDelete();
            $table->foreign('share_image_id')->references('id')->on('curator')->nullOnDelete();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->uuid('cover_id')->nullable()->after('id');
            $table->uuid('share_image_id')->nullable()->after('cover_id');

            $table->foreign('cover_id')->references('id')->on('curator')->nullOnDelete();
            $table->foreign('share_image_id')->references('id')->on('curator')->nullOnDelete();
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->uuid('image_id')->nullable()->after('id');

            $table->foreign('image_id')->references('id')->on('curator')->nullOnDelete();
        });

        Schema::create('project_gallery_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->uuid('media_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('media_id')->references('id')->on('curator')->cascadeOnDelete();
            $table->unique(['project_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_gallery_media');

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropForeign(['image_id']);
            $table->dropColumn('image_id');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['cover_id']);
            $table->dropForeign(['share_image_id']);
            $table->dropColumn(['cover_id', 'share_image_id']);
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropForeign(['thumbnail_id']);
            $table->dropForeign(['banner_id']);
            $table->dropForeign(['share_image_id']);
            $table->dropColumn(['thumbnail_id', 'banner_id', 'share_image_id']);
        });
    }
};
