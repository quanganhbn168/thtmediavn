<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_contents', function (Blueprint $table): void {
            $table->uuid('share_image_id')->nullable()->after('banner_id');
            $table->foreign('share_image_id')->references('id')->on('curator')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('company_contents', function (Blueprint $table): void {
            $table->dropForeign(['share_image_id']);
            $table->dropColumn('share_image_id');
        });
    }
};
