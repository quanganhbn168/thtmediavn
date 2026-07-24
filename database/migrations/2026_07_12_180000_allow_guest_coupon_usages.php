<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE coupon_usages MODIFY user_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (! DB::table('coupon_usages')->whereNull('user_id')->exists()) {
            DB::statement('ALTER TABLE coupon_usages MODIFY user_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
