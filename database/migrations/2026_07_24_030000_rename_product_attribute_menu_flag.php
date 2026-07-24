<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_attributes')
            || ! Schema::hasColumn('product_attributes', 'show_in_skin_menu')
            || Schema::hasColumn('product_attributes', 'show_in_product_menu')) {
            return;
        }

        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->renameColumn('show_in_skin_menu', 'show_in_product_menu');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_attributes')
            || ! Schema::hasColumn('product_attributes', 'show_in_product_menu')
            || Schema::hasColumn('product_attributes', 'show_in_skin_menu')) {
            return;
        }

        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->renameColumn('show_in_product_menu', 'show_in_skin_menu');
        });
    }
};
