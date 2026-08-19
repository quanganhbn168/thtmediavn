<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table): void {
            $table->id();
            $table->uuid('image_id')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 2048)->nullable();
            $table->boolean('button_target_blank')->default(false);
            $table->string('display_scope', 20)->default('all')->index();
            $table->unsignedSmallInteger('display_delay')->default(0);
            $table->boolean('show_once')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('image_id')->references('id')->on('curator')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
