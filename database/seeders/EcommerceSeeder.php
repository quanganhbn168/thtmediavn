<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        Language::query()->delete();
        Language::create(['code'=>'vi','name'=>'Tiếng Việt','native_name'=>'Tiếng Việt','is_default'=>true,'is_active'=>true,'sort_order'=>0]);

        $categoryData = [
            ['Chăm sóc mặt','cham-soc-mat','tay-trang.svg'],['Tẩy trang','tay-trang','tay-trang.svg','cham-soc-mat'],['Sữa rửa mặt','sua-rua-mat','sua-rua-mat.svg','cham-soc-mat'],['Mặt nạ','mat-na','mat-na.svg','cham-soc-mat'],['Serum – tinh chất','serum','serum.svg','cham-soc-mat'],['Kem dưỡng','kem-duong','kem-duong.svg','cham-soc-mat'],['Chống nắng','chong-nang','chong-nang.svg','cham-soc-mat'],
            ['Trang điểm','trang-diem','trang-diem.svg'],['Chăm sóc cơ thể','cham-soc-co-the','duong-the.svg'],['Sữa tắm','sua-tam','sua-tam.svg','cham-soc-co-the'],['Dưỡng thể','duong-the','duong-the.svg','cham-soc-co-the'],['Khác','khac','phu-kien.svg'],['Thực phẩm chức năng','thuc-pham-chuc-nang','phu-kien.svg','khac'],
        ];
        $categories=collect();
        foreach($categoryData as $i=>$row){
            $parent = isset($row[3]) ? $categories->get($row[3]) : null;
            $slug = $row[1];
            $category = ProductCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'parent_id' => $parent?->id,
                    'name' => $row[0],
                    'image' => '/assets/images/categories/'.$row[2],
                    'sort_order' => $i,
                    'is_featured' => ! isset($row[3]),
                    'is_home' => true,
                    'is_active' => true,
                ]
            );
            $categories->put($slug, $category);
        }

        $attributeDefinitions = [
            'loai-da' => [
                'name' => 'Loại da phù hợp',
                'sort_order' => 1,
                'show_in_product_menu' => true,
                'values' => ['Mọi loại da', 'Da dầu', 'Da khô', 'Da hỗn hợp', 'Da nhạy cảm'],
            ],
            'van-de' => [
                'name' => 'Vấn đề da',
                'sort_order' => 2,
                'show_in_product_menu' => true,
                'values' => ['Mụn', 'Thâm nám', 'Mất ẩm', 'Sáng da', 'Lão hóa'],
            ],
            'ket-cau' => [
                'name' => 'Kết cấu',
                'sort_order' => 3,
                'show_in_product_menu' => false,
                'values' => ['Dạng gel', 'Dạng sữa', 'Dạng cream', 'Dạng lỏng', 'Dạng bột', 'Dạng xịt'],
            ],
            'thanh-phan' => [
                'name' => 'Thành phần nổi bật',
                'sort_order' => 4,
                'show_in_product_menu' => false,
                'values' => ['Niacinamide', 'Panthenol', 'Ceramide', 'Vitamin C', 'AHA/BHA', 'Retinol', 'Chiết xuất lô hội', 'SPF 50+'],
            ],
        ];

        $optionDefinitions = [
            'dung-luong' => [
                'name' => 'Dung lượng',
                'display_type' => 'button',
                'sort_order' => 1,
                'values' => ['30ml', '50ml', '100ml', '200ml'],
            ],
            'mau-sac' => [
                'name' => 'Màu sắc',
                'display_type' => 'swatch',
                'sort_order' => 2,
                'values' => ['Trắng', 'Hồng', 'Vàng', 'Đen'],
            ],
        ];

        $categoryFilterByAttribute = [
            'cham-soc-mat' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'tay-trang' => ['loai-da', 'van-de', 'thanh-phan'],
            'sua-rua-mat' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'mat-na' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'serum' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'kem-duong' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'chong-nang' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'trang-diem' => ['loai-da', 'van-de', 'thanh-phan'],
            'cham-soc-co-the' => ['van-de', 'ket-cau', 'thanh-phan'],
            'sua-tam' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'duong-the' => ['loai-da', 'van-de', 'ket-cau', 'thanh-phan'],
            'thuc-pham-chuc-nang' => ['van-de'],
        ];

        $categoryFilterByOption = [
            'sua-rua-mat' => ['dung-luong'],
            'sua-tam' => ['dung-luong'],
            'mat-na' => ['dung-luong'],
            'serum' => ['dung-luong'],
            'kem-duong' => ['dung-luong'],
            'chong-nang' => ['dung-luong'],
            'duong-the' => ['dung-luong'],
            'trang-diem' => ['dung-luong', 'mau-sac'],
        ];

        $attributeValues = [];
        foreach($attributeDefinitions as $attributeSlug => $attributeData){
            $attribute = ProductAttribute::updateOrCreate(
                ['slug' => $attributeSlug],
                [
                    'name' => $attributeData['name'],
                    'is_active' => true,
                    'show_in_product_menu' => $attributeData['show_in_product_menu'],
                    'sort_order' => $attributeData['sort_order'],
                ]
            );

            foreach($attributeData['values'] as $index => $valueLabel){
                    $valueSlug = Str::slug($valueLabel);
                $valueModel = $attribute->values()->updateOrCreate(
                    ['product_attribute_id' => $attribute->id, 'slug' => $valueSlug],
                    ['value' => $valueLabel, 'sort_order' => $index]
                );
                $attributeValues[$attributeSlug][$valueSlug] = $valueModel;
            }
        }

        $options = [];
        foreach($optionDefinitions as $optionSlug => $optionData){
            $option = ProductOption::updateOrCreate(
                ['slug' => $optionSlug],
                [
                    'name' => $optionData['name'],
                    'display_type' => $optionData['display_type'],
                    'is_active' => true,
                    'sort_order' => $optionData['sort_order'],
                ]
            );

            $options[$optionSlug] = $option;

            foreach($optionData['values'] as $index => $optionValueLabel){
                $option->values()->updateOrCreate(
                    ['slug' => Str::slug($optionValueLabel)],
                    ['value' => $optionValueLabel, 'sort_order' => $index]
                );
            }
        }

        foreach($categoryFilterByAttribute as $categorySlug => $attributeSlugs){
            if (! isset($categories[$categorySlug])) {
                continue;
            }

            $category = $categories[$categorySlug];
            foreach($attributeSlugs as $attributeSlug){
                if (isset($attributeValues[$attributeSlug])) {
                    $attributeModel = ProductAttribute::query()->where('slug', $attributeSlug)->first();
                    if ($attributeModel) {
                        $category->attributes()->syncWithoutDetaching([$attributeModel->id]);
                    }
                }
            }
        }

        foreach($categoryFilterByOption as $categorySlug => $optionSlugs){
            if (! isset($categories[$categorySlug])) {
                continue;
            }

            $category = $categories[$categorySlug];
            foreach($optionSlugs as $optionSlug){
                if (isset($options[$optionSlug])) {
                    $category->options()->syncWithoutDetaching([$options[$optionSlug]->id]);
                }
            }
        }

        $specByCategory = [
            'sua-rua-mat' => [
                'loai-da' => ['da-dau', 'da-hon-hop'],
                'van-de' => ['mun'],
                'ket-cau' => ['dang-gel'],
                'thanh-phan' => ['niacinamide', 'panthenol'],
            ],
            'mat-na' => [
                'loai-da' => ['moi-loai-da'],
                'van-de' => ['tham-nam'],
                'ket-cau' => ['dang-bot'],
                'thanh-phan' => ['chiet-xuat-tra-xanh', 'panthenol'],
            ],
            'serum' => [
                'loai-da' => ['da-kho', 'da-hon-hop'],
                'van-de' => ['mun', 'tham-nam'],
                'ket-cau' => ['dang-long'],
                'thanh-phan' => ['niacinamide', 'vitamin-c', 'aha-bha'],
            ],
            'kem-duong' => [
                'loai-da' => ['da-kho', 'da-nhay-cam'],
                'van-de' => ['mat-am'],
                'ket-cau' => ['dang-cream', 'dang-sua'],
                'thanh-phan' => ['ceramide', 'niacinamide'],
            ],
            'chong-nang' => [
                'loai-da' => ['da-dau', 'moi-loai-da'],
                'van-de' => ['mat-am', 'sang-da'],
                'ket-cau' => ['dang-cream'],
                'thanh-phan' => ['spf-50'],
            ],
            'trang-diem' => [
                'loai-da' => ['moi-loai-da'],
                'van-de' => ['tham-nam', 'sang-da'],
                'thanh-phan' => ['niacinamide'],
            ],
            'duong-the' => [
                'loai-da' => ['da-kho'],
                'van-de' => ['mat-am'],
                'ket-cau' => ['dang-sua', 'dang-cream'],
                'thanh-phan' => ['chiet-xuat-lo-hoi'],
            ],
            'sua-tam' => [
                'loai-da' => ['da-kho', 'da-nhay-cam'],
                'van-de' => ['mat-am'],
                'ket-cau' => ['dang-long'],
                'thanh-phan' => ['panthenol', 'chiet-xuat-lo-hoi'],
            ],
            'tay-trang' => [
                'loai-da' => ['moi-loai-da', 'da-hon-hop'],
                'van-de' => ['mun'],
                'ket-cau' => ['dang-gel', 'dang-long'],
                'thanh-phan' => ['niacinamide'],
            ],
        ];

        $brands=collect(['ORIHIRO','LUMAREE','DEEPER C','RE:LAB','PHERMEX','VEMONTES','SPIVERA','VELOURS','USOLAB','SHCI','EVIRALAB','RUEE','JM SOLUTION','SOME BY MI','CERAVE','BATIOUS'])->mapWithKeys(function($name,$i){$brand=Brand::create(['name'=>$name,'slug'=>Str::slug($name),'sort_order'=>$i,'is_featured'=>$i<6,'is_active'=>true]);return [$name=>$brand];});

        $products = [
            ['COMBO 2 GÓI TRÀ ORIHIRO GENPI','ORIHIRO',299000,400000,'thuc-pham-chuc-nang'],['Kem chống nắng LUMAREE SUPER B PROTECTION SUN CREAM 50ml','LUMAREE',350000,390000,'chong-nang'],['Kem dưỡng da mặt Differ&Deeper C - Toning Daily Shot 2.5ml','DEEPER C',230000,380000,'serum'],['RE:LAB WHITENING BODY LOTION 200ml','RE:LAB',250000,370000,'duong-the'],['Sữa rửa mặt Phermex Sea Silt Foam Cleanser 150ml','PHERMEX',250000,300000,'sua-rua-mat'],['Phấn trang điểm VEMONTES GLOW POT NO17','VEMONTES',550000,650000,'trang-diem'],['Ki Spivera Micro Texture Ampoule 3ml','SPIVERA',490000,2000000,'serum'],['Ki Velours Silky Cream 10g','VELOURS',399000,550000,'kem-duong'],['USOLAB BIO TONE UP WHITENING FACE MASK 50ml','USOLAB',390000,500000,'mat-na'],['COMBO 2 SHCI TẨY TẾ BÀO CHẾT TOÀN THÂN','SHCI',150000,null,'cham-soc-co-the'],['Eviralab Derma Mela Light Cream 50ml','EVIRALAB',390000,600000,'kem-duong'],['Combo trị nám 3 sản phẩm RUEE','RUEE',1050000,null,'serum'],['COMBO 2 TẨY TRANG JMSOLUTION H9 500ML','JM SOLUTION',299000,null,'tay-trang'],['Some By Mi AHA-BHA-PHA 30 Days Miracle Serum','SOME BY MI',350000,450000,'serum'],['SỮA RỬA MẶT CERAVE HYDRATING FACIAL CLEANSER','CERAVE',320000,390000,'sua-rua-mat'],['Sữa Tắm trắng da Batious B3+ 500ml','BATIOUS',280000,380000,'sua-tam'],
        ];
        $models = collect();
        foreach($products as $i => $row){
            $productSku = 'SP-' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT);
            $product = Product::create([
                'product_category_id' => $categories[$row[4]]->id,
                'brand_id' => $brands[$row[1]]->id,
                'name' => $row[0],
                'slug' => Str::slug($row[0]),
                'summary' => 'Sản phẩm chính hãng, được chọn lọc phù hợp với nhu cầu chăm sóc cá nhân.',
                'description' => '<p>'.$row[0].' là sản phẩm chính hãng được phân phối bởi Phương Trần Cosmetics.</p><h3>Điểm nổi bật</h3><ul><li>Nguồn gốc rõ ràng.</li><li>Hỗ trợ tư vấn trước và sau khi mua.</li><li>Đóng gói cẩn thận.</li></ul>',
                'usage' => '<p>Sử dụng theo hướng dẫn trên bao bì. Ngưng sử dụng nếu có dấu hiệu kích ứng.</p>',
                'sold_count' => 60 + $i * 9,
                'is_featured' => $i < 8,
                'is_home' => $i < 15,
                'is_active' => true,
                'status' => 'active',
                'track_inventory' => true,
                'published_at' => now()->subDays(20 - $i),
            ]);

            $defaultVariant = $product->variants()->create([
                'name' => 'Mặc định',
                'sku' => $productSku.'-DEFAULT',
                'price' => $row[2],
                'compare_price' => $row[3],
                'stock' => 20 + $i,
                'is_default' => true,
                'is_active' => true,
            ]);

            $image = public_path('assets/images/products/product-'.str_pad((string)($i + 1),2,'0',STR_PAD_LEFT).'.svg');
            if(is_file($image)){
                $product->addMedia($image)->preservingOriginal()->toMediaCollection('product_images');
            }

            $categorySlug = $row[4];
            if (isset($specByCategory[$categorySlug])) {
                $attachValueIds = [];
                foreach($specByCategory[$categorySlug] as $attributeSlug => $valueSlugs){
                    foreach((array) $valueSlugs as $valueSlug){
                        if (isset($attributeValues[$attributeSlug][$valueSlug])) {
                            $attachValueIds[] = $attributeValues[$attributeSlug][$valueSlug]->id;
                        }
                    }
                }
                if ($attachValueIds !== []) {
                    $product->attributeValues()->syncWithoutDetaching(array_unique($attachValueIds));
                }
            }

            $models->push($product);
        }

        $capacity = $options['dung-luong'] ?? ProductOption::updateOrCreate(
            ['slug' => 'dung-luong'],
            ['name' => 'Dung lượng', 'display_type' => 'button', 'is_active' => true, 'sort_order' => 1]
        );
        $small = $capacity->values()->firstOrCreate(['slug' => '50ml'], ['value' => '50ml', 'sort_order' => 0]);
        $large = $capacity->values()->firstOrCreate(['slug' => '100ml'], ['value' => '100ml', 'sort_order' => 1]);
        $variantProduct = $models[14];
        $variantProduct->options()->attach($capacity);
        $variantProduct->variants()->where('is_default', true)->update(['is_default' => false]);
        $v1 = $variantProduct->variants()->create([
            'name' => '50ml',
            'sku' => 'SP-0015-50',
            'price' => 320000,
            'stock' => 15,
            'is_default' => true,
            'is_active' => true,
        ]);
        $v1->values()->attach($small);
        $v2 = $variantProduct->variants()->create([
            'name' => '100ml',
            'sku' => 'SP-0015-100',
            'price' => 520000,
            'stock' => 8,
            'is_active' => true,
        ]);
        $v2->values()->attach($large);

        $flash = FlashSale::create(['name'=>'Flash Sale cuối tuần','starts_at'=>now()->subHour(),'ends_at'=>now()->addDays(3),'is_active'=>true]);
        foreach($models->take(5) as $i => $product){
            $defaultVariant = $product->default_variant;
            $flash->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $defaultVariant?->id,
                'sale_price' => round((float)($defaultVariant?->price ?? 0) * (0.75 + $i * 0.03), -3),
                'quantity' => 50,
                'sold' => 10 + $i,
            ]);
        }
        Coupon::create(['code'=>'MTD10','name'=>'Giảm 10% đơn đầu tiên','type'=>'percent','value'=>10,'max_discount'=>100000,'minimum_order'=>300000,'usage_limit'=>1000,'usage_limit_per_user'=>1,'starts_at'=>now()->subDay(),'ends_at'=>now()->addMonths(3),'is_active'=>true]);
        Coupon::create(['code'=>'FREESHIP','name'=>'Miễn phí vận chuyển','type'=>'free_shipping','value'=>0,'minimum_order'=>500000,'starts_at'=>now()->subDay(),'ends_at'=>now()->addMonths(3),'is_active'=>true]);

        $postCategory=PostCategory::create(['name'=>['vi'=>'Kiến thức làm đẹp'],'description'=>['vi'=>'Cẩm nang chăm sóc da và trang điểm.'],'sort_order'=>0,'is_active'=>true]);
        foreach([['Bí quyết giữ lớp makeup bền đẹp suốt cả ngày','Trang điểm đẹp bắt đầu từ cách chuẩn bị da và lựa chọn nền phù hợp.'],['5 bước chăm sóc da cơ bản mỗi ngày','Quy trình đơn giản giúp da sạch, đủ ẩm và ổn định.'],['Cách chọn mỹ phẩm phù hợp với từng loại da','Hiểu đúng làn da để chọn hoạt chất và kết cấu phù hợp.'],['3 sai lầm phổ biến khiến da mãi không đẹp lên','Những thói quen chăm sóc da nên điều chỉnh ngay hôm nay.']] as $i=>$row){$post=Post::create(['post_category_id'=>$postCategory->id,'name'=>['vi'=>$row[0]],'summary'=>['vi'=>$row[1]],'content'=>['vi'=>'<p>'.$row[1].'</p><p>Nội dung chi tiết đang được đội ngũ biên tập cập nhật.</p>'],'is_featured'=>$i===0,'is_active'=>true,'published_at'=>now()->subDays($i)]);$image=public_path('assets/images/news/news-'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT).'.svg');if(is_file($image))$post->addMedia($image)->preservingOriginal()->toMediaCollection('post_image');}
    }
}
