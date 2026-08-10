<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use App\Services\WebsiteSettingsService;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThtMediaFoundationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedSettings();

            $newsCategory = PostCategory::query()->where('name->vi', 'Tin tức')->first()
                ?? new PostCategory();
            $newsCategory->fill([
                'name' => ['vi' => 'Tin tức'],
                'description' => ['vi' => 'Tin tức và góc nhìn từ THT MEDIA VN.'],
                'is_home' => true,
                'is_active' => true,
                'sort_order' => 10,
            ])->save();
        });

        app(WebsiteSettingsService::class)->refresh();
    }

    private function seedSettings(): void
    {
        $general = app(GeneralSettings::class);
        $general->site_status = true;
        $general->timezone = 'Asia/Ho_Chi_Minh';
        $general->site_name = ['vi' => 'THT MEDIA VN'];
        $general->site_description = ['vi' => 'Nền tảng truyền thông, nội dung và giải pháp thương hiệu của THT MEDIA VN.'];
        $general->copyright = ['vi' => '© '.date('Y').' THT MEDIA VN. Tất cả quyền được bảo lưu.'];
        $general->save();

        $contact = app(ContactSettings::class);
        $contact->company_name = 'THT MEDIA VN';
        $contact->address = null;
        $contact->phone = null;
        $contact->email = null;
        $contact->tax_code = null;
        $contact->map_embed = null;
        $contact->working_hours = null;
        $contact->facebook = null;
        $contact->instagram = null;
        $contact->youtube = null;
        $contact->tiktok = null;
        $contact->zalo = null;
        $contact->save();

        $seo = app(SeoSettings::class);
        $seo->seo_title = ['vi' => 'THT MEDIA VN'];
        $seo->seo_description = ['vi' => 'Thông tin, nội dung và các giải pháp truyền thông được phát triển bởi THT MEDIA VN.'];
        $seo->seo_keywords = ['vi' => 'THT MEDIA VN, truyền thông, nội dung, thương hiệu'];
        $seo->google_analytics_code = null;
        $seo->save();

        $homepage = app(HomepageSettings::class);
        $homepage->homepage_banner_type = 'slider';
        $homepage->homepage_sections = ['posts'];
        $homepage->homepage_section_titles = [
            'categories' => ['vi' => 'Danh mục'],
            'flash_sale' => ['vi' => 'Chương trình nổi bật'],
            'featured_products' => ['vi' => 'Nội dung nổi bật'],
            'brands' => ['vi' => 'Đối tác'],
            'testimonials' => ['vi' => 'Khách hàng nói về chúng tôi'],
            'posts' => ['vi' => 'Tin tức và góc nhìn'],
        ];
        $homepage->save();

        $about = app(AboutSettings::class);
        $about->about_story = ['vi' => '<p>THT MEDIA VN xây dựng nội dung và giải pháp truyền thông trên một nền tảng quản trị thống nhất, minh bạch và dễ mở rộng.</p>'];
        $about->about_history = ['vi' => ''];
        $about->about_mission = ['vi' => 'Tạo ra nội dung rõ ràng, hữu ích và phù hợp với mục tiêu của từng dự án.'];
        $about->about_vision = ['vi' => 'Phát triển một hệ sinh thái truyền thông linh hoạt, bền vững và có khả năng mở rộng.'];
        $about->about_core_values = ['vi' => <<<'HTML'
<div class="row g-4">
<div class="col-md-4"><h3>Sáng tạo</h3><p>Luôn tìm cách kể câu chuyện rõ ràng, khác biệt và phù hợp với từng bối cảnh.</p></div>
<div class="col-md-4"><h3>Trách nhiệm</h3><p>Minh bạch trong thông tin, quy trình và cam kết của mỗi dự án.</p></div>
<div class="col-md-4"><h3>Linh hoạt</h3><p>Xây dựng giải pháp có thể thích ứng và mở rộng theo nhu cầu thực tế.</p></div>
</div>
HTML];
        $about->save();
    }
}
