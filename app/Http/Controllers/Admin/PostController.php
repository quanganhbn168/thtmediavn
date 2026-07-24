<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPostRequest;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PostService;
use Illuminate\View\View;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(IndexPostRequest $request): View
    {
        $posts = $this->postService->paginate($request->validated());
        $categories = $this->postService->categories();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categories = PostCategory::all();

        return view('admin.posts.create', compact('categories'));
    }

    public function store(StorePostRequest $request)
    {
        $this->postService->create($request->validated());

        if ($request->input('submit_action') === 'save_and_create') {
            return redirect()
                ->route('admin.posts.create')
                ->with('success', 'Tạo bài viết mới thành công và tiếp tục tạo mới!');
        }

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Tạo bài viết mới thành công!');
    }

    public function edit(Post $post)
    {
        $categories = PostCategory::all();

        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->postService->update($post, $request->validated());

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Cập nhật bài viết thành công!');
    }

    public function destroy(Post $post)
    {
        $this->postService->delete($post);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Xóa bài viết thành công!');
    }
}
