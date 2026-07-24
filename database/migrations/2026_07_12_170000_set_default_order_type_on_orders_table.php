<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY order_type VARCHAR(50) NOT NULL DEFAULT 'website'");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE orders MODIFY order_type VARCHAR(50) NOT NULL');
    }
};
