<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->foreignId('service_category_id')->nullable()->after('group')->constrained('service_categories')->nullOnDelete();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('project_category_id')->nullable()->after('client_id')->constrained('project_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['project_category_id']);
            $table->dropColumn('project_category_id');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropForeign(['service_category_id']);
            $table->dropColumn('service_category_id');
        });
    }
};
