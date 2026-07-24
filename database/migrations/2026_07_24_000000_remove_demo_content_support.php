<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['products', 'posts', 'coupons', 'flash_sales'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'is_demo')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropIndex(['is_demo']);
                $blueprint->dropColumn('is_demo');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'is_demo')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->boolean('is_demo')->default(false)->index();
            });
        }
    }
};
