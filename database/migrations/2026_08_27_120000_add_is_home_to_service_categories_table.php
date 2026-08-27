<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->boolean('is_home')->default(false)->after('is_active')->index();
        });

        DB::table('service_categories')
            ->where('is_active', true)
            ->update(['is_home' => true]);
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->dropIndex(['is_home']);
            $table->dropColumn('is_home');
        });
    }
};
