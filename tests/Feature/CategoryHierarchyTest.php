<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use App\Models\ProductCategory;
use App\Rules\LeafCategory;
use App\Rules\ValidCategoryParent;
use App\Services\CategoryHierarchyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_leaf_selector_keeps_the_tree_path_and_disables_parent_categories(): void
    {
        $root = $this->productCategory('Danh mục gốc');
        $leaf = $this->productCategory('Danh mục lá', $root->id);

        $options = app(CategoryHierarchyService::class)->selectOptions(
            collect([$root, $leaf]),
            leafOnly: true,
            activeOnly: true,
        );

        $rootOption = collect($options)->firstWhere('id', $root->id);
        $leafOption = collect($options)->firstWhere('id', $leaf->id);

        $this->assertTrue($rootOption['disabled']);
        $this->assertFalse($leafOption['disabled']);
        $this->assertSame('Danh mục gốc › Danh mục lá', $leafOption['path']);
    }

    public function test_products_and_posts_only_accept_active_leaf_categories(): void
    {
        $productRoot = $this->productCategory('Nhóm sản phẩm');
        $productLeaf = $this->productCategory('Sản phẩm lá', $productRoot->id);
        $postRoot = $this->postCategory('Nhóm bài viết');
        $postLeaf = $this->postCategory('Bài viết lá', $postRoot->id);

        $this->assertTrue(Validator::make(
            ['category_id' => $productRoot->id],
            ['category_id' => [new LeafCategory(ProductCategory::class, 'Danh mục sản phẩm')]],
        )->fails());
        $this->assertFalse(Validator::make(
            ['category_id' => $productLeaf->id],
            ['category_id' => [new LeafCategory(ProductCategory::class, 'Danh mục sản phẩm')]],
        )->fails());

        $this->assertTrue(Validator::make(
            ['category_id' => $postRoot->id],
            ['category_id' => [new LeafCategory(PostCategory::class, 'Danh mục bài viết')]],
        )->fails());
        $this->assertFalse(Validator::make(
            ['category_id' => $postLeaf->id],
            ['category_id' => [new LeafCategory(PostCategory::class, 'Danh mục bài viết')]],
        )->fails());

        $postLeaf->update(['is_active' => false]);
        $this->assertTrue(Validator::make(
            ['category_id' => $postLeaf->id],
            ['category_id' => [new LeafCategory(PostCategory::class, 'Danh mục bài viết')]],
        )->fails());
    }

    public function test_category_parent_rule_rejects_a_fifth_level_and_descendant_parent_but_allows_a_populated_parent(): void
    {
        $levelOne = $this->productCategory('Cấp 1');
        $levelTwo = $this->productCategory('Cấp 2', $levelOne->id);
        $levelThree = $this->productCategory('Cấp 3', $levelTwo->id);
        $levelFour = $this->productCategory('Cấp 4', $levelThree->id);

        $fifthLevel = Validator::make(
            ['parent_id' => $levelFour->id],
            ['parent_id' => [new ValidCategoryParent(ProductCategory::class, null, 'products', 'sản phẩm')]],
        );
        $this->assertTrue($fifthLevel->fails());

        $loop = Validator::make(
            ['parent_id' => $levelFour->id],
            ['parent_id' => [new ValidCategoryParent(ProductCategory::class, $levelOne->id, 'products', 'sản phẩm')]],
        );
        $this->assertTrue($loop->fails());

        $assignedLeaf = ProductCategory::query()
            ->doesntHave('children')
            ->has('products')
            ->firstOrFail();
        $contentParent = Validator::make(
            ['parent_id' => $assignedLeaf->id],
            ['parent_id' => [new ValidCategoryParent(ProductCategory::class, null, 'products', 'sản phẩm')]],
        );
        $this->assertFalse($contentParent->fails());
    }

    private function productCategory(string $name, ?int $parentId = null): ProductCategory
    {
        return ProductCategory::query()->create([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ]);
    }

    private function postCategory(string $name, ?int $parentId = null): PostCategory
    {
        return PostCategory::query()->create([
            'parent_id' => $parentId,
            'name' => ['vi' => $name],
            'is_active' => true,
        ]);
    }
}
