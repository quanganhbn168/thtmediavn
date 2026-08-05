<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('combos', function (Blueprint $table): void {
            $table->longText('ingredients')->nullable()->after('description');
            $table->longText('usage')->nullable()->after('ingredients');
            $table->longText('product_notes')->nullable()->after('usage');
        });
    }

    public function down(): void
    {
        Schema::table('combos', function (Blueprint $table): void {
            $table->dropColumn(['ingredients', 'usage', 'product_notes']);
        });
    }
};
