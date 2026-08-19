<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_contents', function (Blueprint $table): void {
            $table->uuid('image_id')->nullable()->after('id');
            $table->uuid('banner_id')->nullable()->after('image_id');

            $table->foreign('image_id')->references('id')->on('curator')->nullOnDelete();
            $table->foreign('banner_id')->references('id')->on('curator')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('company_contents', function (Blueprint $table): void {
            $table->dropForeign(['image_id']);
            $table->dropForeign(['banner_id']);
            $table->dropColumn(['image_id', 'banner_id']);
        });
    }
};
