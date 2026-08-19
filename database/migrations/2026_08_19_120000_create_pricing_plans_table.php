<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pricing_plans')) {
            return;
        }

        Schema::create('pricing_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->text('summary')->nullable();
            $table->string('price', 120)->nullable();
            $table->string('price_note', 160)->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();
        DB::table('pricing_plans')->insert([
            [
                'name' => 'Profile doanh nghiệp',
                'summary' => 'Bộ nội dung nền tảng cho hình ảnh doanh nghiệp.',
                'price' => '8.900.000đ',
                'price_note' => null,
                'features' => json_encode(['Concept & nội dung', 'Thiết kế profile', 'Bàn giao file hoàn thiện'], JSON_UNESCAPED_UNICODE),
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Video doanh nghiệp',
                'summary' => 'Câu chuyện thương hiệu, nhà máy và đội ngũ.',
                'price' => '18.000.000đ',
                'price_note' => null,
                'features' => json_encode(['Tiền kỳ & kịch bản', 'Quay phim hiện trường', 'Hậu kỳ video'], JSON_UNESCAPED_UNICODE),
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Nhiếp ảnh thương mại',
                'summary' => 'Bộ ảnh sản phẩm, đội ngũ hoặc không gian.',
                'price' => '6.900.000đ',
                'price_note' => null,
                'features' => json_encode(['Set-up & chụp ảnh', 'Chọn ảnh', 'Retouching cơ bản'], JSON_UNESCAPED_UNICODE),
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Marketing thuê ngoài',
                'summary' => 'Đồng hành vận hành truyền thông theo tháng.',
                'price' => '12.000.000đ/tháng',
                'price_note' => null,
                'features' => json_encode(['Định hướng nội dung', 'Quản trị kênh', 'Báo cáo & tối ưu'], JSON_UNESCAPED_UNICODE),
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
