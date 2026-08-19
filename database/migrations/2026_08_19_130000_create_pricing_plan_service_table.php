<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pricing_plan_service')) {
            return;
        }

        Schema::create('pricing_plan_service', function (Blueprint $table): void {
            $table->foreignId('pricing_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['pricing_plan_id', 'service_id']);
        });

        $serviceId = DB::table('services')
            ->join('slugs', function ($join): void {
                $join->on('slugs.sluggable_id', '=', 'services.id')
                    ->where('slugs.sluggable_type', '=', 'App\\Models\\Service')
                    ->where('slugs.locale', '=', 'vi');
            })
            ->where('slugs.slug', 'san-xuat-video-doanh-nghiep-va-quang-cao')
            ->value('services.id');
        $pricingPlanId = DB::table('pricing_plans')->where('name', 'Video doanh nghiệp')->value('id');

        if ($serviceId && $pricingPlanId) {
            DB::table('pricing_plan_service')->insert([
                'pricing_plan_id' => $pricingPlanId,
                'service_id' => $serviceId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plan_service');
    }
};
