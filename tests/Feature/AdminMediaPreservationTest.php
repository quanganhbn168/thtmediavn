<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaPreservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_post_without_a_new_image_preserves_its_existing_media(): void
    {
        Storage::fake('public_media');
        $this->seed();
        $admin = User::role('admin')->firstOrFail();
        $post = Post::query()->firstOrFail();
        File::ensureDirectoryExists(public_path('uploads/tmp'));
        File::put(public_path('uploads/tmp/post-existing.png'), 'existing image');
        $post->addMedia(public_path('uploads/tmp/post-existing.png'))->toMediaCollection('post_image', 'public_media');
        $mediaId = $post->getFirstMedia('post_image')->getKey();

        $this->actingAs($admin)->put(route('admin.posts.update', $post), [
            'post_category_id' => $post->post_category_id,
            'name' => ['vi' => 'Bài viết kiểm thử'],
            'summary' => ['vi' => ''],
            'content' => ['vi' => ''],
            'seo_title' => ['vi' => ''],
            'seo_description' => ['vi' => ''],
            'seo_keywords' => ['vi' => ''],
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($mediaId, $post->fresh()->getFirstMedia('post_image')->getKey());
    }
}
