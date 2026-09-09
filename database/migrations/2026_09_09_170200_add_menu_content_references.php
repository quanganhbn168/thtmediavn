<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->string('linked_source_type')->nullable();
            $table->unsignedBigInteger('linked_source_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', fn (Blueprint $table) => $table->dropColumn(['linked_source_type', 'linked_source_id']));
    }
};
