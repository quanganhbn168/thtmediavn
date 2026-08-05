<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_combo_items');

        if (Schema::hasTable('product_categories') && Schema::hasTable('products')) {
            $legacyCategoryIds = DB::table('product_categories')->whereIn('slug', ['combo', 'combo-tri-mun', 'combo-duong-trang'])->pluck('id');
            if ($legacyCategoryIds->isNotEmpty() && ! DB::table('products')->whereIn('product_category_id', $legacyCategoryIds)->exists()) {
                DB::table('product_categories')->whereIn('parent_id', $legacyCategoryIds)->delete();
                DB::table('product_categories')->whereIn('id', $legacyCategoryIds)->delete();
            }
        }

        if (! Schema::hasColumn('products', 'product_type')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_product_type_index');
            $table->dropColumn('product_type');
        });
    }

    public function down(): void
    {
        // The product_type field belonged to the removed product-as-combo design.
    }
};
