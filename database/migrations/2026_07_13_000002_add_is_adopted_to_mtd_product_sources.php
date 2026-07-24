<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mtd_product_sources', function (Blueprint $table) {
            $table->boolean('is_adopted')->default(false)->after('source_stock_status');
        });

        $existingProductIds = DB::table('products')
            ->where('status', '!=', 'draft')
            ->orWhere('is_active', true)
            ->pluck('id');

        DB::table('mtd_product_sources')
            ->whereIn('product_id', $existingProductIds)
            ->update(['is_adopted' => true]);
    }

    public function down(): void
    {
        Schema::table('mtd_product_sources', function (Blueprint $table) {
            $table->dropColumn('is_adopted');
        });
    }
};
