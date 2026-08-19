<?php

namespace Database\Seeders;

use App\Enums\SliderType;
use App\Models\Client;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestingContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(['email' => 'admin@thtmedia.test'], [
            'name' => 'THTMedia Admin',
            'phone' => '0900000000',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        Language::query()->updateOrCreate(['code' => 'vi'], [
            'name' => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $category = PostCategory::query()->where('name->vi', 'Tin tức')->firstOrFail();
        if (! Post::query()->where('name->vi', 'Bài viết kiểm thử THTMedia')->exists()) {
            Post::query()->create([
                'post_category_id' => $category->id,
                'name' => ['vi' => 'Bài viết kiểm thử THTMedia'],
                'summary' => ['vi' => 'Nội dung mẫu dành cho kiểm thử khung CMS THTMedia.'],
                'content' => ['vi' => '<p>Nội dung bài viết kiểm thử.</p>'],
                'is_featured' => true,
                'is_active' => true,
                'created_by' => $admin->id,
                'published_at' => now(),
            ]);
        }

        if (! Page::query()->where('name->vi', 'Trang nội dung kiểm thử')->exists()) {
            Page::query()->create([
                'template' => 'default',
                'name' => ['vi' => 'Trang nội dung kiểm thử'],
                'content' => ['vi' => '<p>Nội dung trang kiểm thử.</p>'],
                'is_active' => true,
                'sort_order' => 10,
                'published_at' => now(),
            ]);
        }

        Slider::query()->firstOrCreate(['key' => SliderType::HomepageHero->value], [
            'name' => ['vi' => 'Slider chính trang chủ'],
            'is_active' => true,
        ]);

        $client = Client::query()->where('name->vi', 'Khách hàng kiểm thử')->first() ?? Client::query()->create([
            'name' => ['vi' => 'Khách hàng kiểm thử'],
            'industry' => 'Doanh nghiệp',
            'description' => ['vi' => 'Dữ liệu phục vụ kiểm thử luồng case study.'],
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 10,
        ]);
        $project = Project::query()->where('name->vi', 'Dự án kiểm thử THT Media')->first() ?? Project::query()->create([
            'client_id' => $client->id,
            'name' => ['vi' => 'Dự án kiểm thử THT Media'],
            'summary' => ['vi' => 'Case study mẫu phục vụ kiểm thử giao diện dự án.'],
            'context' => ['vi' => '<p>Khách hàng cần xây dựng nội dung truyền thông.</p>'],
            'solution' => ['vi' => '<p>THT Media làm rõ mục tiêu và đề xuất phạm vi triển khai.</p>'],
            'work_items' => ['vi' => ['Tư vấn nội dung', 'Tổ chức sản xuất']],
            'results' => ['vi' => ['Bàn giao nội dung theo phạm vi thống nhất']],
            'industry' => 'Doanh nghiệp',
            'completed_year' => (int) date('Y'),
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 10,
            'published_at' => now(),
        ]);
        $project->services()->syncWithoutDetaching(Service::query()->orderBy('sort_order')->limit(2)->pluck('id'));
    }
}
