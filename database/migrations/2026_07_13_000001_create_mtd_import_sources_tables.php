<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mtd_product_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('external_id', 255)->unique();
            $table->text('source_url')->nullable();
            $table->char('payload_hash', 64)->nullable();
            $table->string('source_stock_status', 50)->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mtd_variant_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mtd_product_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('external_id', 255);
            $table->string('source_sku', 255)->nullable();
            $table->boolean('source_available')->nullable();
            $table->char('payload_hash', 64)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['mtd_product_source_id', 'external_id'], 'mtd_variant_source_external_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mtd_variant_sources');
        Schema::dropIfExists('mtd_product_sources');
    }
};
