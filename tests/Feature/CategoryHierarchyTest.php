<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use App\Rules\LeafCategory;
use App\Rules\ValidCategoryParent;
use App\Services\CategoryHierarchyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_leaf_selector_keeps_the_post_category_path(): void
    {
        $root = $this->postCategory('Danh mục gốc');
        $leaf = $this->postCategory('Danh mục lá', $root->id);

        $options = app(CategoryHierarchyService::class)->selectOptions(
            collect([$root, $leaf]),
            leafOnly: true,
            activeOnly: true,
        );

        $this->assertTrue(collect($options)->firstWhere('id', $root->id)['disabled']);
        $this->assertFalse(collect($options)->firstWhere('id', $leaf->id)['disabled']);
        $this->assertSame('Danh mục gốc › Danh mục lá', collect($options)->firstWhere('id', $leaf->id)['path']);
    }

    public function test_posts_only_accept_an_active_leaf_category(): void
    {
        $root = $this->postCategory('Nhóm bài viết');
        $leaf = $this->postCategory('Bài viết lá', $root->id);
        $rule = [new LeafCategory(PostCategory::class, 'Danh mục bài viết')];

        $this->assertTrue(Validator::make(['category_id' => $root->id], ['category_id' => $rule])->fails());
        $this->assertFalse(Validator::make(['category_id' => $leaf->id], ['category_id' => $rule])->fails());

        $leaf->update(['is_active' => false]);
        $this->assertTrue(Validator::make(['category_id' => $leaf->id], ['category_id' => $rule])->fails());
    }

    public function test_post_category_parent_rejects_a_fifth_level_and_descendant_loop(): void
    {
        $levelOne = $this->postCategory('Cấp 1');
        $levelTwo = $this->postCategory('Cấp 2', $levelOne->id);
        $levelThree = $this->postCategory('Cấp 3', $levelTwo->id);
        $levelFour = $this->postCategory('Cấp 4', $levelThree->id);

        $this->assertTrue(Validator::make(
            ['parent_id' => $levelFour->id],
            ['parent_id' => [new ValidCategoryParent(PostCategory::class, null, 'posts', 'bài viết')]],
        )->fails());
        $this->assertTrue(Validator::make(
            ['parent_id' => $levelFour->id],
            ['parent_id' => [new ValidCategoryParent(PostCategory::class, $levelOne->id, 'posts', 'bài viết')]],
        )->fails());
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
