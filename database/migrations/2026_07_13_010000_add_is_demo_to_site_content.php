<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Retained as a no-op so existing migration histories stay valid.
    }

    public function down(): void
    {
        foreach (['products', 'posts', 'coupons', 'flash_sales'] as $table) {
            if (! Schema::hasColumn($table, 'is_demo')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropIndex(['is_demo']);
                $blueprint->dropColumn('is_demo');
            });
        }
    }
};
