<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->morphs('reviewable');
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
                $table->string('name', 120);
                $table->string('email', 150)->nullable();
                $table->unsignedTinyInteger('rating');
                $table->text('content');
                $table->json('images')->nullable();
                $table->string('status', 20)->default('pending');
                $table->boolean('is_verified')->default(false);
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'reviewable_id')) {
                $table->unsignedBigInteger('reviewable_id')->nullable();
                $table->string('reviewable_type', 255)->nullable();
                $table->index(['reviewable_id', 'reviewable_type']);
            }

            if (! Schema::hasColumn('reviews', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('reviews', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('reviews', 'order_item_id')) {
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            }
            if (! Schema::hasColumn('reviews', 'images')) {
                $table->json('images')->nullable();
            }
            if (! Schema::hasColumn('reviews', 'status')) {
                $table->string('status', 20)->default('pending');
            }
            if (! Schema::hasColumn('reviews', 'is_verified')) {
                $table->boolean('is_verified')->default(false);
            }
            if (! Schema::hasColumn('reviews', 'rating')) {
                $table->unsignedTinyInteger('rating')->default(0);
            }
            if (! Schema::hasColumn('reviews', 'content')) {
                $table->text('content')->nullable();
            }
            if (! Schema::hasColumn('reviews', 'email')) {
                $table->string('email', 150)->nullable();
            }
            if (! Schema::hasColumn('reviews', 'created_at')) {
                $table->timestamps();
                $table->index(['status', 'created_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
