<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->foreignId('combo_id')->nullable()->after('product_variant_id')->constrained('combos')->cascadeOnDelete();
            $table->index(['cart_id', 'combo_id']);
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('product_variant_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropForeign(['combo_id']);
            $table->dropIndex(['cart_id', 'combo_id']);
            $table->dropColumn('combo_id');
        });
    }
};
