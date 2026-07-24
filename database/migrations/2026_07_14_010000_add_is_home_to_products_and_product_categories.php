<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table): void {
            $table->boolean('is_home')->default(false)->after('is_featured')->index();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_home')->default(false)->after('is_featured')->index();
        });

        DB::table('product_categories')->where('is_active', true)->update(['is_home' => true]);
        DB::table('products')->where('is_featured', true)->update(['is_home' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('is_home');
        });

        Schema::table('product_categories', function (Blueprint $table): void {
            $table->dropColumn('is_home');
        });
    }
};
