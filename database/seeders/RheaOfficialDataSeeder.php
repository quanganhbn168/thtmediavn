<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ContactChannel;
use App\Models\PostCategory;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Services\WebsiteSettingsService;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RheaOfficialDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedSettings();
            $categories = $this->seedCategories();
            $this->seedBrands();
            $this->seedSkinFilters($categories);
            $this->seedPostCategories();
            $this->seedContactChannels();
        });

        app(WebsiteSettingsService::class)->refresh();
    }

    private function seedSettings(): void
    {
        $general = app(GeneralSettings::class);
        $general->site_status = true;
        $general->timezone = 'Asia/Ho_Chi_Minh';
        $general->site_name = ['vi' => 'RHEA SKINLAB'];
        $general->site_description = [
            'vi' => 'Sản phẩm mỹ phẩm và chăm sóc cá nhân chính hãng được tuyển chọn từ các thương hiệu uy tín Hàn Quốc, Nhật Bản và Châu Âu.',
        ];
        $general->copyright = ['vi' => '© 2026 RHEA SKINLAB. Tất cả quyền được bảo lưu.'];
        $general->save();

        $contact = app(ContactSettings::class);
        $contact->company_name = 'Công ty TNHH Quốc tế RHEA SKINLAB';
        $contact->address = 'Khối 5, Sóc Sơn, Hà Nội, Việt Nam';
        $contact->phone = '0395 686 598';
        $contact->email = 'rheaskinlab@gmail.com';
        $contact->tax_code = '0110395713';
        $contact->map_embed = null;
        $contact->working_hours = null;
        $contact->facebook = null;
        $contact->instagram = null;
        $contact->youtube = null;
        $contact->tiktok = null;
        $contact->zalo = 'https://zalo.me/0395686598';
        $contact->save();

        $seo = app(SeoSettings::class);
        $seo->seo_title = ['vi' => 'RHEA SKINLAB - Dược mỹ phẩm Á Âu chính hãng'];
        $seo->seo_description = [
            'vi' => 'RHEA SKINLAB phân phối sản phẩm chăm sóc da, hóa mỹ phẩm và dược mỹ phẩm chính hãng, đồng hành cùng khách hàng lựa chọn giải pháp phù hợp.',
        ];
        $seo->seo_keywords = ['vi' => 'chăm sóc da, hóa mỹ phẩm, dược mỹ phẩm, mỹ phẩm chính hãng'];
        $seo->google_analytics_code = null;
        $seo->save();

        $homepage = app(HomepageSettings::class);
        $homepage->homepage_banner_type = 'slider';
        $homepage->homepage_sections = ['categories', 'flash_sale', 'featured_products', 'brands', 'posts'];
        $homepage->homepage_section_titles = [
            'categories' => ['vi' => 'Danh mục sản phẩm'],
            'flash_sale' => ['vi' => 'Chương trình ưu đãi'],
            'featured_products' => ['vi' => 'Sản phẩm bán chạy'],
            'brands' => ['vi' => 'Thương hiệu phân phối'],
            'posts' => ['vi' => 'Blog'],
        ];
        $homepage->save();

        $about = app(AboutSettings::class);
        $about->about_story = ['vi' => <<<'HTML'
<p>Chúng tôi tin rằng vẻ đẹp bền vững bắt đầu từ một làn da khỏe mạnh và sự tự tin của mỗi người phụ nữ.</p>
<p>Với mong muốn đồng hành cùng phụ nữ trên hành trình chăm sóc sắc đẹp, RHEA SKINLAB mang đến những sản phẩm mỹ phẩm và chăm sóc cá nhân chính hãng được tuyển chọn kỹ lưỡng từ các thương hiệu uy tín đến từ Hàn Quốc, Nhật Bản và Châu Âu.</p>
<p>Không chỉ cung cấp sản phẩm, chúng tôi hướng đến xây dựng một hệ sinh thái chăm sóc sắc đẹp toàn diện, nơi mỗi khách hàng được lắng nghe, tư vấn đúng nhu cầu và lựa chọn giải pháp phù hợp với tình trạng da cũng như phong cách sống.</p>
HTML];
        $about->about_history = ['vi' => <<<'HTML'
<p>RHEA SKINLAB được hình thành từ niềm đam mê làm đẹp và những trải nghiệm thực tế trong quá trình tìm kiếm các sản phẩm chăm sóc da an toàn, hiệu quả.</p>
<p>Trước một thị trường có chất lượng sản phẩm không đồng đều, chúng tôi lựa chọn con đường phát triển dựa trên sự chọn lọc kỹ lưỡng, chỉ hợp tác với các thương hiệu chính hãng có nguồn gốc rõ ràng và được người tiêu dùng trên thế giới tin tưởng.</p>
<p>Từ những đơn hàng đầu tiên đến hôm nay, điều chúng tôi theo đuổi không phải là bán nhiều sản phẩm hơn mà là giúp nhiều phụ nữ tìm được sản phẩm thực sự phù hợp với mình.</p>
HTML];
        $about->about_vision = [
            'vi' => 'Trở thành thương hiệu chăm sóc sắc đẹp uy tín tại Việt Nam, được khách hàng tin tưởng không chỉ bởi những sản phẩm chính hãng mà còn bởi sự tận tâm trong tư vấn và đồng hành trên hành trình làm đẹp của mỗi người phụ nữ.',
        ];
        $about->about_mission = ['vi' => <<<'HTML'
<ul>
<li>Mang đến những sản phẩm chăm sóc da và làm đẹp chính hãng từ các thương hiệu uy tín trên thế giới.</li>
<li>Cung cấp kiến thức làm đẹp khoa học, giúp khách hàng lựa chọn sản phẩm phù hợp thay vì chạy theo xu hướng.</li>
<li>Đồng hành cùng phụ nữ xây dựng vẻ đẹp khỏe mạnh, tự nhiên và bền vững.</li>
<li>Đặt sự hài lòng và niềm tin của khách hàng làm nền tảng cho mọi hoạt động.</li>
</ul>
HTML];
        $about->about_core_values = ['vi' => <<<'HTML'
<div class="row g-4">
<div class="col-md-6"><h3>Chính hãng</h3><p>Cam kết 100% sản phẩm có nguồn gốc rõ ràng, minh bạch và được lựa chọn từ các thương hiệu uy tín.</p></div>
<div class="col-md-6"><h3>Tận tâm</h3><p>Luôn lắng nghe, tư vấn đúng nhu cầu và đồng hành cùng khách hàng trong suốt quá trình sử dụng sản phẩm.</p></div>
<div class="col-md-6"><h3>Chất lượng</h3><p>Ưu tiên những sản phẩm đã được kiểm chứng về hiệu quả, độ an toàn và phù hợp với nhiều loại da.</p></div>
<div class="col-md-6"><h3>Trung thực</h3><p>Không quảng cáo quá mức, không hứa hẹn những kết quả phi thực tế; sự trung thực là nền tảng của niềm tin.</p></div>
<div class="col-md-6"><h3>Phát triển bền vững</h3><p>Xây dựng một cộng đồng phụ nữ yêu bản thân, hiểu làn da của mình và làm đẹp một cách khoa học.</p></div>
</div>
HTML];
        $about->save();
    }

    /** @return array<string, ProductCategory> */
    private function seedCategories(): array
    {
        $skin = ProductCategory::query()->updateOrCreate(
            ['slug' => 'cham-soc-mat'],
            ['name' => 'Chăm sóc da', 'sort_order' => 0, 'is_featured' => true, 'is_home' => true, 'is_active' => true],
        );
        $body = ProductCategory::query()->updateOrCreate(
            ['slug' => 'cham-soc-co-the'],
            ['name' => 'Chăm sóc cơ thể', 'sort_order' => 1, 'is_featured' => true, 'is_home' => true, 'is_active' => true],
        );

        $definitions = [
            ['tay-trang', 'Tẩy trang', $skin, 10],
            ['sua-rua-mat', 'Sữa rửa mặt', $skin, 20],
            ['tay-te-bao-chet', 'Tẩy tế bào chết', $skin, 30],
            ['mat-na', 'Mặt nạ', $skin, 40],
            ['toner', 'Toner', $skin, 50],
            ['serum', 'Serum', $skin, 60],
            ['kem-duong', 'Kem dưỡng', $skin, 70],
            ['chong-nang', 'Kem chống nắng', $skin, 80],
            ['sua-tam', 'Sữa tắm', $body, 110],
            ['duong-the', 'Kem dưỡng ẩm', $body, 120],
            ['khu-mui', 'Khử mùi', $body, 130],
            ['tay-te-bao-chet-co-the', 'Tẩy tế bào chết', $body, 140],
            ['kem-chong-nang-co-the', 'Kem chống nắng', $body, 150],
            ['u-trang', 'Ủ trắng', $body, 160],
        ];

        $categories = ['cham-soc-mat' => $skin, 'cham-soc-co-the' => $body];
        foreach ($definitions as [$slug, $name, $parent, $sortOrder]) {
            $categories[$slug] = ProductCategory::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'parent_id' => $parent->id,
                    'name' => $name,
                    'sort_order' => $sortOrder,
                    'is_featured' => false,
                    'is_home' => true,
                    'is_active' => true,
                ],
            );
        }

        return $categories;
    }

    private function seedBrands(): void
    {
        Brand::query()->update(['is_featured' => false]);

        $brands = [
            ['cnp-laboratory', 'CNP', 10],
            ['belif', 'Belif', 20],
            ['kyunglab', 'KyungLab', 30],
            ['coklear', "Cok'lear", 40],
            ['bioderma', 'Bioderma', 50],
            ['topla', 'Topla', 60],
            ['aa-cosmetic', 'AA Cosmetic', 70],
        ];

        foreach ($brands as [$slug, $name, $sortOrder]) {
            Brand::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $sortOrder, 'is_featured' => true, 'is_active' => true],
            );
        }
    }

    /** @param array<string, ProductCategory> $categories */
    private function seedSkinFilters(array $categories): void
    {
        $skinType = ProductAttribute::query()->updateOrCreate(
            ['slug' => 'loai-da'],
            ['name' => 'Loại da phù hợp', 'sort_order' => 10, 'is_active' => true, 'show_in_product_menu' => true],
        );
        $concern = ProductAttribute::query()->updateOrCreate(
            ['slug' => 'van-de'],
            ['name' => 'Vấn đề da', 'sort_order' => 20, 'is_active' => true, 'show_in_product_menu' => true],
        );

        foreach ([
            ['da-dau', 'Da dầu', 10],
            ['da-kho', 'Da khô', 20],
            ['da-hon-hop', 'Da hỗn hợp', 30],
            ['da-nhay-cam', 'Da nhạy cảm', 40],
        ] as [$slug, $value, $sortOrder]) {
            $skinType->values()->updateOrCreate(
                ['slug' => $slug],
                ['value' => $value, 'sort_order' => $sortOrder],
            );
        }

        $existingToneValue = $concern->values()
            ->where(fn ($query) => $query
                ->where('slug', 'xin-mau')
                ->orWhereIn('value', ['Da sỉ màu', 'Sỉ màu', 'Da xỉn màu', 'Xỉn màu']))
            ->orderByRaw("slug = 'xin-mau' desc")
            ->first();

        foreach ([
            ['mun', 'Mụn', 10, null],
            ['lao-hoa', 'Lão hóa', 20, null],
            ['xin-mau', 'Xỉn màu', 30, $existingToneValue?->id],
            ['thieu-nuoc', 'Thiếu nước', 40, null],
        ] as [$slug, $value, $sortOrder, $existingId]) {
            $concern->values()->updateOrCreate(
                $existingId ? ['id' => $existingId] : ['slug' => $slug],
                ['slug' => $slug, 'value' => $value, 'sort_order' => $sortOrder],
            );
        }

        foreach ($categories as $category) {
            $category->attributes()->syncWithoutDetaching([$skinType->id, $concern->id]);
        }
    }

    private function seedPostCategories(): void
    {
        $knowledge = PostCategory::query()->orderBy('id')->first();
        if ($knowledge) {
            $knowledge->update([
                'name' => ['vi' => 'Kiến thức chăm sóc da'],
                'description' => ['vi' => 'Kiến thức khoa học giúp khách hàng hiểu và chăm sóc làn da phù hợp.'],
                'is_active' => true,
            ]);
        }
    }

    private function seedContactChannels(): void
    {
        $channels = [
            'phone' => [
                'name' => 'Hotline tư vấn',
                'value' => '0395 686 598',
                'url' => null,
                'is_primary' => true,
                'show_topbar' => true,
                'show_footer' => true,
                'show_floating' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
            'zalo' => [
                'name' => 'Zalo tư vấn',
                'value' => '0395 686 598',
                'url' => 'https://zalo.me/0395686598',
                'is_primary' => false,
                'show_topbar' => false,
                'show_footer' => true,
                'show_floating' => true,
                'is_active' => true,
                'sort_order' => 20,
            ],
            'email' => [
                'name' => 'Email',
                'value' => 'rheaskinlab@gmail.com',
                'url' => null,
                'is_primary' => false,
                'show_topbar' => false,
                'show_footer' => true,
                'show_floating' => false,
                'is_active' => true,
                'sort_order' => 30,
            ],
        ];

        foreach ($channels as $type => $payload) {
            $channel = ContactChannel::query()->where('type', $type)->orderBy('id')->first()
                ?? new ContactChannel(['type' => $type]);
            $channel->fill($payload + ['type' => $type])->save();
        }
    }
}
