<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flash_sale_products', function (Blueprint $table): void {
            $table->string('discount_type', 20)->default('fixed')->after('sale_price');
            $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('flash_sale_products', function (Blueprint $table): void {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
