<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->boolean('show_in_skin_menu')->default(false)->after('is_active');
        });

        DB::table('product_attributes')
            ->whereIn('slug', ['loai-da', 'van-de'])
            ->update(['show_in_skin_menu' => true]);
    }

    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->dropColumn('show_in_skin_menu');
        });
    }
};
