<?php

namespace Database\Seeders;

use App\Enums\SliderType;
use App\Models\Client;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slider;
use App\Models\SliderItem;
use App\Services\WebsiteSettingsService;
use App\Settings\AboutSettings;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use App\Settings\WebsiteSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThtMediaFoundationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedSettings();

            $newsCategory = PostCategory::query()->where('name->vi', 'Tin tức')->first()
                ?? new PostCategory;
            $newsCategory->fill([
                'name' => ['vi' => 'Tin tức'],
                'description' => ['vi' => 'Tin tức và góc nhìn từ THT MEDIA VN.'],
                'is_home' => true,
                'is_active' => true,
                'sort_order' => 10,
            ])->save();

            $this->seedCategories();
            $this->seedServices();
            $this->seedPortfolio();
            $this->seedPosts($newsCategory);
            $this->seedHomepageSlider();
        });

        app(WebsiteSettingsService::class)->refresh();
    }

    private function seedSettings(): void
    {
        $company = app(CompanySettings::class);
        $company->company_name ??= 'THT MEDIA VN';
        $company->save();

        $website = app(WebsiteSettings::class);
        $website->timezone = $website->timezone ?: 'Asia/Ho_Chi_Minh';
        $website->site_name = $website->site_name ?: ['vi' => 'THT MEDIA VN'];
        $website->site_description = $website->site_description ?: ['vi' => 'Nền tảng truyền thông, nội dung và giải pháp thương hiệu của THT MEDIA VN.'];
        $website->copyright = $website->copyright ?: ['vi' => '© '.date('Y').' THT MEDIA VN. Tất cả quyền được bảo lưu.'];
        $website->save();

        $contact = app(ContactSettings::class);
        $contact->save();

        $seo = app(SeoSettings::class);
        $seo->seo_title = $seo->seo_title ?: ['vi' => 'THT MEDIA VN'];
        $seo->seo_description = $seo->seo_description ?: ['vi' => 'Thông tin, nội dung và các giải pháp truyền thông được phát triển bởi THT MEDIA VN.'];
        $seo->seo_keywords = $seo->seo_keywords ?: ['vi' => 'THT MEDIA VN, truyền thông, nội dung, thương hiệu'];
        $seo->save();

        $homepage = app(HomepageSettings::class);
        $homepage->homepage_banner_type ??= 'slider';
        $homepage->homepage_sections = $homepage->homepage_sections ?: [
            'intro', 'services', 'projects', 'featured_case', 'reasons', 'process',
            'clients', 'capacity', 'testimonials', 'posts', 'consultation',
        ];
        $homepage->homepage_section_titles = $homepage->homepage_section_titles ?: [
            'intro' => ['vi' => 'Một đội ngũ đồng hành từ ý tưởng đến sản phẩm truyền thông'],
            'services' => ['vi' => 'Dịch vụ của THT Media'],
            'projects' => ['vi' => 'Dự án đã thực hiện'],
            'featured_case' => ['vi' => 'Case study nổi bật'],
            'reasons' => ['vi' => 'Vì sao doanh nghiệp chọn THT Media'],
            'process' => ['vi' => 'Quy trình hợp tác'],
            'clients' => ['vi' => 'Khách hàng và đối tác'],
            'capacity' => ['vi' => 'Năng lực triển khai'],
            'testimonials' => ['vi' => 'Khách hàng nói về chúng tôi'],
            'posts' => ['vi' => 'Tin tức và góc nhìn'],
            'consultation' => ['vi' => 'Trao đổi về dự án của anh/chị'],
        ];
        $homepage->homepage_stats = $homepage->homepage_stats ?: [
            ['value' => '8', 'suffix' => '+', 'label' => 'Năm kinh nghiệm', 'icon' => 'fa-solid fa-calendar-check'],
            ['value' => '2000', 'suffix' => '+', 'label' => 'Khách hàng', 'icon' => 'fa-solid fa-users'],
            ['value' => '1000', 'suffix' => '+', 'label' => 'Dự án truyền thông', 'icon' => 'fa-solid fa-film'],
            ['value' => '100', 'suffix' => '%', 'label' => 'Đồng hành xuyên suốt', 'icon' => 'fa-solid fa-handshake'],
        ];
        $homepage->homepage_about_title = $homepage->homepage_about_title ?: ['vi' => 'Công ty TNHH THT Media'];
        $homepage->homepage_about_text = $homepage->homepage_about_text ?: ['vi' => 'THT Media xây dựng một hệ sinh thái sản xuất truyền thông thực tế cho doanh nghiệp, tổ chức và thương hiệu cá nhân.'];
        $homepage->homepage_about_supporting_text = $homepage->homepage_about_supporting_text ?: ['vi' => 'Nhân sự in-house, thiết bị chủ động và quy trình rõ ràng giúp mỗi brief được chuyển thành nội dung có thể sử dụng ngay.'];
        $homepage->homepage_intro_title = $homepage->homepage_intro_title ?: ['vi' => 'THT Media giúp doanh nghiệp biến mục tiêu truyền thông thành nội dung có thể triển khai.'];
        $homepage->homepage_intro_text = $homepage->homepage_intro_text ?: ['vi' => 'Chúng tôi tập trung vào ba nhóm năng lực: sản xuất hình ảnh, truyền thông và marketing, sự kiện và thương hiệu. Mỗi dự án bắt đầu từ việc làm rõ mục tiêu, đối tượng và đầu ra cần bàn giao.'];
        $homepage->homepage_reasons = $homepage->homepage_reasons ?: ['vi' => "Giải pháp bám sát mục tiêu và bối cảnh thực tế\nMột đầu mối phối hợp xuyên suốt quá trình triển khai\nPhạm vi công việc và đầu ra được thống nhất rõ ràng\nCó dự án thực tế để đối chiếu năng lực"];
        $homepage->homepage_process = $homepage->homepage_process ?: ['vi' => "Tiếp nhận brief và làm rõ mục tiêu\nĐề xuất giải pháp, phạm vi và kế hoạch\nTổ chức sản xuất và phối hợp triển khai\nNghiệm thu, bàn giao và đồng hành sau dự án"];
        $homepage->homepage_capacity = $homepage->homepage_capacity ?: ['vi' => "Tư vấn định hướng nội dung và hình thức triển khai\nTổ chức sản xuất hình ảnh, video và nội dung truyền thông\nPhối hợp các hạng mục sự kiện và nhận diện thương hiệu\nQuản lý đầu việc, tiến độ và tài sản bàn giao"];
        $homepage->homepage_consultation_title = $homepage->homepage_consultation_title ?: ['vi' => 'Anh/chị đang chuẩn bị một dự án truyền thông?'];
        $homepage->homepage_consultation_text = $homepage->homepage_consultation_text ?: ['vi' => 'Hãy gửi mục tiêu, phạm vi và thời gian dự kiến. THT Media sẽ liên hệ để cùng làm rõ hướng triển khai phù hợp.'];
        $homepage->save();

        $about = app(AboutSettings::class);
        $about->about_page_label = $about->about_page_label ?: ['vi' => 'Giới thiệu'];
        $about->about_page_title = $about->about_page_title ?: ['vi' => 'Năng lực, cách làm và những giá trị THT Media theo đuổi'];
        $about->about_page_intro = $about->about_page_intro ?: ['vi' => 'THT Media đồng hành cùng doanh nghiệp từ định hướng, sản xuất đến bàn giao sản phẩm truyền thông.'];
        $about->about_story = $about->about_story ?: ['vi' => '<p>THT MEDIA VN xây dựng nội dung và giải pháp truyền thông trên một nền tảng quản trị thống nhất, minh bạch và dễ mở rộng.</p>'];
        $about->about_history = $about->about_history ?: ['vi' => ''];
        $about->about_mission = $about->about_mission ?: ['vi' => 'Tạo ra nội dung rõ ràng, hữu ích và phù hợp với mục tiêu của từng dự án.'];
        $about->about_vision = $about->about_vision ?: ['vi' => 'Phát triển một hệ sinh thái truyền thông linh hoạt, bền vững và có khả năng mở rộng.'];
        $about->about_core_values = $about->about_core_values ?: ['vi' => <<<'HTML'
<div class="row g-4">
<div class="col-md-4"><h3>Sáng tạo</h3><p>Luôn tìm cách kể câu chuyện rõ ràng, khác biệt và phù hợp với từng bối cảnh.</p></div>
<div class="col-md-4"><h3>Trách nhiệm</h3><p>Minh bạch trong thông tin, quy trình và cam kết của mỗi dự án.</p></div>
<div class="col-md-4"><h3>Linh hoạt</h3><p>Xây dựng giải pháp có thể thích ứng và mở rộng theo nhu cầu thực tế.</p></div>
</div>
HTML];
        $about->save();
    }

    private function seedServices(): void
    {
        foreach ($this->serviceDefinitions() as $sortOrder => $definition) {
            $service = Service::query()->where('name->vi', $definition['name'])->first();
            $categoryId = ServiceCategory::query()
                ->where('name->vi', Service::GROUPS[$definition['group']] ?? $definition['group'])
                ->value('id');

            if ($service) {
                if ($service->service_category_id === null && $categoryId) {
                    $service->update(['service_category_id' => $categoryId]);
                }

                continue;
            }

            $service = new Service;
            $service->fill([
                'group' => $definition['group'],
                'service_category_id' => $categoryId,
                'name' => ['vi' => $definition['name']],
                'summary' => ['vi' => $definition['summary']],
                'intro' => ['vi' => '<p>'.$definition['intro'].'</p>'],
                'problems' => ['vi' => $definition['problems']],
                'audiences' => ['vi' => $definition['audiences']],
                'work_items' => ['vi' => $definition['work_items']],
                'deliverables' => ['vi' => $definition['deliverables']],
                'benefits' => ['vi' => $definition['benefits']],
                'process_steps' => ['vi' => ['Tiếp nhận brief và mục tiêu', 'Đề xuất phương án và phạm vi', 'Tổ chức triển khai', 'Nghiệm thu và bàn giao']],
                'faqs' => ['vi' => [
                    ['question' => 'THT Media cần những thông tin gì để tư vấn?', 'answer' => 'Doanh nghiệp nên cung cấp mục tiêu, đối tượng, kênh sử dụng, thời gian dự kiến và ngân sách tham chiếu nếu đã có.'],
                    ['question' => 'Phạm vi công việc được xác định khi nào?', 'answer' => 'Phạm vi và đầu ra được làm rõ sau khi hai bên trao đổi brief và trước khi bắt đầu triển khai.'],
                ]],
                'seo_title' => ['vi' => $definition['name'].' | THT Media'],
                'seo_description' => ['vi' => $definition['summary']],
                'seo_keywords' => ['vi' => 'THT Media, '.$definition['name']],
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => ($sortOrder + 1) * 10,
            ])->save();
        }
    }

    private function seedCategories(): void
    {
        foreach (Service::GROUPS as $sortOrder => $name) {
            ServiceCategory::query()->firstOrCreate(
                ['name->vi' => $name],
                [
                    'name' => ['vi' => $name],
                    'sort_order' => array_search($sortOrder, array_keys(Service::GROUPS), true) * 10,
                    'is_active' => true,
                    'is_home' => true,
                ],
            );
        }

        foreach ([
            'Phim doanh nghiệp',
            'Nhiếp ảnh',
            'Sự kiện',
            'Profile & Đồ họa',
            'Website & SEO',
            'Đào tạo',
        ] as $sortOrder => $name) {
            ProjectCategory::query()->firstOrCreate(
                ['name->vi' => $name],
                [
                    'name' => ['vi' => $name],
                    'sort_order' => ($sortOrder + 1) * 10,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedHomepageSlider(): void
    {
        $slider = Slider::query()->firstOrCreate(
            ['key' => SliderType::HomepageHero->value],
            [
                'name' => ['vi' => SliderType::HomepageHero->label()],
                'is_active' => true,
            ],
        );

        if ($slider->items()->exists()) {
            return;
        }

        $item = SliderItem::query()->create([
            'slider_id' => $slider->id,
            'title' => ['vi' => 'Biến mục tiêu truyền thông thành sản phẩm có thể triển khai.'],
            'sub_title' => ['vi' => 'Từ chiến lược, hình ảnh đến video và sự kiện, THT Media giúp doanh nghiệp có một đội ngũ đồng hành xuyên suốt.'],
            'buttons' => [
                [
                    'text' => ['vi' => 'Xem dịch vụ'],
                    'link' => '/dich-vu',
                ],
                [
                    'text' => ['vi' => 'Xem dự án'],
                    'link' => '/du-an',
                ],
            ],
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $heroImage = public_path('assets/images/home-demo/hero.jpg');

        if (is_file($heroImage)) {
            $item->addMedia($heroImage)->toMediaCollection('slide_image');
        }
    }

    private function seedPortfolio(): void
    {
        $clients = [];

        foreach ([
            ['name' => 'Amphenol', 'industry' => 'Sản xuất công nghiệp'],
            ['name' => 'Yadea', 'industry' => 'Xe điện và tiêu dùng'],
            ['name' => 'Goertek', 'industry' => 'Công nghệ và điện tử'],
        ] as $sortOrder => $definition) {
            $client = Client::query()->where('name->vi', $definition['name'])->first();

            if (! $client) {
                $client = Client::query()->create([
                    'name' => ['vi' => $definition['name']],
                    'industry' => $definition['industry'],
                    'description' => ['vi' => 'Đối tác trong các dự án nội dung, hình ảnh và thương hiệu của THT Media.'],
                    'is_featured' => true,
                    'is_active' => true,
                    'sort_order' => ($sortOrder + 1) * 10,
                ]);
            }

            $clients[$definition['name']] = $client;
        }

        $categories = ProjectCategory::query()
            ->whereIn('name->vi', ['Phim doanh nghiệp', 'Nhiếp ảnh', 'Profile & Đồ họa'])
            ->get()
            ->keyBy(fn (ProjectCategory $category): string => $category->getTranslation('name', 'vi'));
        $services = Service::query()->get()->keyBy(fn (Service $service): string => $service->getTranslation('name', 'vi'));

        foreach ([
            [
                'name' => 'Amphenol – Phim doanh nghiệp',
                'client' => 'Amphenol',
                'category' => 'Phim doanh nghiệp',
                'service' => 'Sản xuất video doanh nghiệp và quảng cáo',
                'industry' => 'Sản xuất công nghiệp',
                'summary' => 'Phim doanh nghiệp giới thiệu năng lực, con người và môi trường sản xuất.',
                'context' => '<p>Doanh nghiệp cần một nội dung hình ảnh rõ ràng để giới thiệu năng lực sản xuất và văn hóa vận hành.</p>',
                'solution' => '<p>THT Media phát triển kịch bản, tổ chức ghi hình và hoàn thiện phim theo mục tiêu sử dụng của doanh nghiệp.</p>',
            ],
            [
                'name' => 'Yadea – Hình ảnh thương hiệu',
                'client' => 'Yadea',
                'category' => 'Nhiếp ảnh',
                'service' => 'Chụp ảnh thương hiệu và sản phẩm',
                'industry' => 'Xe điện và tiêu dùng',
                'summary' => 'Bộ hình ảnh sản phẩm và không gian thương hiệu phục vụ truyền thông đa kênh.',
                'context' => '<p>Thương hiệu cần thư viện hình ảnh đồng nhất cho các hoạt động truyền thông và giới thiệu sản phẩm.</p>',
                'solution' => '<p>THT Media xây dựng shot list, tổ chức chụp và bàn giao bộ ảnh theo các tỷ lệ sử dụng đã thống nhất.</p>',
            ],
            [
                'name' => 'Goertek – Profile doanh nghiệp',
                'client' => 'Goertek',
                'category' => 'Profile & Đồ họa',
                'service' => 'Nhận diện và tài liệu thương hiệu',
                'industry' => 'Công nghệ và điện tử',
                'summary' => 'Hồ sơ năng lực và tài liệu hình ảnh giúp doanh nghiệp trình bày năng lực nhất quán.',
                'context' => '<p>Doanh nghiệp cần chuẩn hóa cách trình bày năng lực và các điểm mạnh trong tài liệu giới thiệu.</p>',
                'solution' => '<p>THT Media tổ chức nội dung, định hướng hình ảnh và thiết kế bộ tài liệu theo phạm vi dự án.</p>',
            ],
        ] as $sortOrder => $definition) {
            $project = Project::query()->where('name->vi', $definition['name'])->first();

            if (! $project) {
                $project = Project::query()->create([
                    'client_id' => $clients[$definition['client']]->id,
                    'project_category_id' => ($categories[$definition['category']] ?? null)?->id,
                    'name' => ['vi' => $definition['name']],
                    'summary' => ['vi' => $definition['summary']],
                    'context' => ['vi' => $definition['context']],
                    'solution' => ['vi' => $definition['solution']],
                    'work_items' => ['vi' => ['Làm rõ mục tiêu và thông điệp', 'Tổ chức sản xuất theo phạm vi', 'Hoàn thiện và bàn giao tài sản']],
                    'results' => ['vi' => ['Có bộ nội dung sẵn sàng sử dụng', 'Thông điệp và hình ảnh nhất quán hơn']],
                    'industry' => $definition['industry'],
                    'completed_year' => (int) date('Y'),
                    'seo_title' => ['vi' => $definition['name'].' | THT Media'],
                    'seo_description' => ['vi' => $definition['summary']],
                    'is_featured' => true,
                    'is_active' => true,
                    'sort_order' => ($sortOrder + 1) * 10,
                    'published_at' => now(),
                ]);
            }

            if ($services[$definition['service']] ?? null) {
                $project->services()->syncWithoutDetaching([$services[$definition['service']]->id]);
            }
        }
    }

    private function seedPosts(PostCategory $newsCategory): void
    {
        foreach ([
            [
                'name' => 'Bắt đầu một dự án truyền thông từ đâu?',
                'summary' => 'Một brief rõ ràng giúp doanh nghiệp thống nhất mục tiêu, phạm vi và đầu ra trước khi triển khai.',
                'content' => '<p>Một dự án truyền thông hiệu quả thường bắt đầu từ việc làm rõ mục tiêu, đối tượng, kênh sử dụng và thời gian cần bàn giao.</p><p>Khi những thông tin này được thống nhất, đội ngũ có thể đề xuất phạm vi phù hợp và chủ động hơn trong quá trình sản xuất.</p>',
            ],
            [
                'name' => 'Từ ý tưởng đến sản phẩm hình ảnh có thể sử dụng',
                'summary' => 'Quy trình tiền kỳ, sản xuất và hậu kỳ cần được kết nối để nội dung cuối cùng phục vụ đúng mục tiêu.',
                'content' => '<p>Ý tưởng chỉ trở thành tài sản truyền thông khi được chuyển thành kịch bản, kế hoạch sản xuất và đầu ra cụ thể.</p><p>THT Media tập trung vào sự liên kết giữa thông điệp, hình ảnh và bối cảnh sử dụng thực tế.</p>',
            ],
            [
                'name' => 'Xây dựng thư viện nội dung cho thương hiệu',
                'summary' => 'Một thư viện hình ảnh và video được chuẩn bị đúng cách sẽ giúp thương hiệu chủ động hơn trong truyền thông dài hạn.',
                'content' => '<p>Doanh nghiệp có thể bắt đầu bằng việc xác định các điểm chạm thường xuyên và nhóm tài sản cần dùng lặp lại.</p><p>Từ đó, kế hoạch sản xuất được chia thành các đợt phù hợp với nguồn lực và lịch truyền thông.</p>',
            ],
        ] as $sortOrder => $definition) {
            if (Post::query()->where('name->vi', $definition['name'])->exists()) {
                continue;
            }

            Post::query()->create([
                'post_category_id' => $newsCategory->id,
                'name' => ['vi' => $definition['name']],
                'summary' => ['vi' => $definition['summary']],
                'content' => ['vi' => $definition['content']],
                'seo_title' => ['vi' => $definition['name'].' | THT Media'],
                'seo_description' => ['vi' => $definition['summary']],
                'seo_keywords' => ['vi' => 'THT Media, truyền thông, nội dung'],
                'is_featured' => $sortOrder === 0,
                'is_active' => true,
                'published_at' => now()->subDays($sortOrder),
            ]);
        }
    }

    private function serviceDefinitions(): array
    {
        return [
            [
                'group' => 'production',
                'name' => 'Sản xuất video doanh nghiệp và quảng cáo',
                'summary' => 'Phát triển video giới thiệu, quảng cáo và nội dung thương hiệu theo mục tiêu sử dụng cụ thể.',
                'intro' => 'THT Media cùng doanh nghiệp xác định thông điệp, bối cảnh sử dụng và hình thức thể hiện trước khi tổ chức sản xuất.',
                'problems' => ['Thông điệp chưa được chuyển thành kịch bản rõ ràng', 'Nội dung hình ảnh chưa đồng nhất với định vị thương hiệu'],
                'audiences' => ['Doanh nghiệp cần video giới thiệu năng lực', 'Thương hiệu chuẩn bị chiến dịch truyền thông'],
                'work_items' => ['Tư vấn ý tưởng và cấu trúc nội dung', 'Tiền kỳ, ghi hình và hậu kỳ', 'Điều chỉnh phiên bản theo kênh sử dụng'],
                'deliverables' => ['Video hoàn thiện theo tỷ lệ đã thống nhất', 'Tệp bàn giao và phiên bản rút gọn theo phạm vi'],
                'benefits' => ['Thông điệp rõ ràng và nhất quán', 'Nội dung sẵn sàng sử dụng trên các kênh đã xác định'],
            ],
            [
                'group' => 'production',
                'name' => 'Chụp ảnh thương hiệu và sản phẩm',
                'summary' => 'Xây dựng bộ ảnh phục vụ nhận diện, bán hàng, truyền thông và hồ sơ năng lực.',
                'intro' => 'Mỗi buổi chụp được chuẩn bị dựa trên danh sách đầu ra, phong cách hình ảnh và các điểm chạm doanh nghiệp cần sử dụng.',
                'problems' => ['Thiếu thư viện ảnh đồng bộ', 'Ảnh hiện có chưa phù hợp với website và nội dung truyền thông'],
                'audiences' => ['Thương hiệu ra mắt sản phẩm', 'Doanh nghiệp cần cập nhật hình ảnh năng lực'],
                'work_items' => ['Xây dựng moodboard và shot list', 'Tổ chức chụp tại studio hoặc địa điểm', 'Chọn lọc và hậu kỳ ảnh'],
                'deliverables' => ['Bộ ảnh đã hậu kỳ', 'Định dạng bàn giao theo mục đích sử dụng'],
                'benefits' => ['Có thư viện ảnh chủ động cho nhiều kênh', 'Hình ảnh thống nhất với tinh thần thương hiệu'],
            ],
            [
                'group' => 'production',
                'name' => 'Livestream và ghi hình sự kiện',
                'summary' => 'Ghi hình, phát trực tiếp và sản xuất nội dung sau sự kiện theo phạm vi kỹ thuật đã thống nhất.',
                'intro' => 'Giải pháp được thiết kế theo địa điểm, nền tảng phát, số điểm máy và yêu cầu nội dung sau chương trình.',
                'problems' => ['Cần phối hợp nhiều đầu việc kỹ thuật tại sự kiện', 'Thiếu nội dung hậu kỳ để tiếp tục truyền thông'],
                'audiences' => ['Hội nghị và lễ ra mắt', 'Chương trình nội bộ hoặc phát trực tuyến'],
                'work_items' => ['Khảo sát và lập phương án kỹ thuật', 'Ghi hình hoặc phát trực tiếp', 'Hậu kỳ nội dung theo phạm vi'],
                'deliverables' => ['Tệp ghi hình chương trình', 'Video recap hoặc phiên bản cắt dựng theo thỏa thuận'],
                'benefits' => ['Phối hợp kỹ thuật qua một đầu mối', 'Kéo dài giá trị nội dung sau sự kiện'],
            ],
            [
                'group' => 'marketing',
                'name' => 'Chiến lược nội dung và kế hoạch truyền thông',
                'summary' => 'Xác định thông điệp, nhóm nội dung, kênh triển khai và nhịp truyền thông cho từng giai đoạn.',
                'intro' => 'Kế hoạch bắt đầu từ mục tiêu kinh doanh, đối tượng cần tiếp cận và nguồn lực thực tế của doanh nghiệp.',
                'problems' => ['Nội dung triển khai rời rạc', 'Khó xác định ưu tiên giữa nhiều kênh truyền thông'],
                'audiences' => ['Doanh nghiệp cần chuẩn hóa hoạt động nội dung', 'Thương hiệu chuẩn bị chiến dịch hoặc giai đoạn mới'],
                'work_items' => ['Phân tích bối cảnh và mục tiêu', 'Xây dựng trụ cột nội dung', 'Lập kế hoạch kênh và lịch triển khai'],
                'deliverables' => ['Khung chiến lược nội dung', 'Kế hoạch triển khai theo phạm vi thống nhất'],
                'benefits' => ['Đội ngũ có định hướng chung', 'Nguồn lực được phân bổ theo ưu tiên rõ ràng'],
            ],
            [
                'group' => 'marketing',
                'name' => 'Sản xuất và quản trị nội dung đa kênh',
                'summary' => 'Triển khai nội dung định kỳ cho website và các kênh xã hội dựa trên kế hoạch đã thống nhất.',
                'intro' => 'THT Media tổ chức đầu việc từ kế hoạch, sản xuất nội dung đến theo dõi lịch đăng và tài sản bàn giao.',
                'problems' => ['Thiếu nguồn lực duy trì nội dung đều đặn', 'Nội dung giữa các kênh chưa nhất quán'],
                'audiences' => ['Doanh nghiệp cần đội ngũ nội dung đồng hành', 'Thương hiệu vận hành nhiều kênh'],
                'work_items' => ['Lập lịch nội dung', 'Biên tập, thiết kế và sản xuất', 'Quản lý phiên bản và lịch xuất bản'],
                'deliverables' => ['Bộ nội dung theo kỳ', 'Tài sản thiết kế và báo cáo công việc theo phạm vi'],
                'benefits' => ['Duy trì nhịp truyền thông ổn định', 'Thông điệp nhất quán trên các điểm chạm'],
            ],
            [
                'group' => 'marketing',
                'name' => 'Truyền thông chiến dịch tích hợp',
                'summary' => 'Phối hợp nội dung, hình ảnh và kênh truyền thông cho một mục tiêu hoặc giai đoạn cụ thể.',
                'intro' => 'Giải pháp tập trung vào sự liên kết giữa thông điệp, tài sản nội dung và từng điểm chạm của chiến dịch.',
                'problems' => ['Các hạng mục chiến dịch thiếu kết nối', 'Khó phối hợp giữa nhiều bên sản xuất'],
                'audiences' => ['Thương hiệu ra mắt sản phẩm', 'Doanh nghiệp thực hiện chiến dịch theo mùa hoặc cột mốc'],
                'work_items' => ['Xây dựng concept và thông điệp', 'Phối hợp sản xuất tài sản nội dung', 'Tổ chức lịch triển khai theo kênh'],
                'deliverables' => ['Bộ tài sản chiến dịch theo phạm vi', 'Kế hoạch và hồ sơ bàn giao'],
                'benefits' => ['Các hạng mục cùng phục vụ một mục tiêu', 'Giảm phân tán trong phối hợp triển khai'],
            ],
            [
                'group' => 'event_brand',
                'name' => 'Tổ chức và truyền thông sự kiện',
                'summary' => 'Xây dựng concept, phối hợp vận hành và sản xuất nội dung truyền thông cho sự kiện.',
                'intro' => 'Phạm vi được thiết kế theo mục tiêu chương trình, quy mô, địa điểm và các hạng mục doanh nghiệp cần THT Media đảm nhiệm.',
                'problems' => ['Nhiều đầu việc cần phối hợp trong thời gian ngắn', 'Truyền thông trước và sau sự kiện chưa được kết nối'],
                'audiences' => ['Sự kiện doanh nghiệp', 'Ra mắt sản phẩm, hội nghị và hoạt động thương hiệu'],
                'work_items' => ['Phát triển concept và kế hoạch', 'Phối hợp hạng mục sản xuất và vận hành', 'Sản xuất nội dung trước, trong và sau sự kiện'],
                'deliverables' => ['Hồ sơ kế hoạch theo phạm vi', 'Các hạng mục sự kiện và nội dung đã thống nhất'],
                'benefits' => ['Đầu việc được phối hợp qua một kế hoạch chung', 'Nội dung sự kiện tiếp tục được sử dụng sau chương trình'],
            ],
            [
                'group' => 'event_brand',
                'name' => 'Nhận diện và tài liệu thương hiệu',
                'summary' => 'Phát triển các hạng mục nhận diện và tài liệu giúp thương hiệu xuất hiện nhất quán hơn.',
                'intro' => 'THT Media xác định phạm vi từ hiện trạng nhận diện, mục tiêu sử dụng và những điểm chạm cần ưu tiên.',
                'problems' => ['Tài liệu thương hiệu thiếu đồng bộ', 'Cách sử dụng hình ảnh và thông điệp chưa thống nhất'],
                'audiences' => ['Doanh nghiệp mới chuẩn hóa thương hiệu', 'Thương hiệu cần cập nhật tài liệu truyền thông'],
                'work_items' => ['Rà soát nhu cầu và điểm chạm', 'Phát triển định hướng hình ảnh', 'Thiết kế các hạng mục trong phạm vi'],
                'deliverables' => ['Bộ tài liệu nhận diện theo thỏa thuận', 'Tệp thiết kế và hướng dẫn sử dụng tương ứng'],
                'benefits' => ['Hình ảnh nhất quán hơn', 'Đội ngũ có tài liệu để áp dụng vào vận hành'],
            ],
        ];
    }
}
