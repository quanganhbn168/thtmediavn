<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPostCategoryRequest;
use App\Http\Requests\Admin\StorePostCategoryRequest;
use App\Http\Requests\Admin\UpdatePostCategoryRequest;
use App\Models\PostCategory;
use App\Services\PostCategoryService;
use Illuminate\View\View;

class PostCategoryController extends Controller
{
    protected PostCategoryService $categoryService;

    public function __construct(PostCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(IndexPostCategoryRequest $request): View
    {
        $categories = $this->categoryService->paginate($request->validated());

        return view('admin.post_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.post_categories.create', $this->categoryService->editorContext(new PostCategory));
    }

    public function store(StorePostCategoryRequest $request)
    {
        $this->categoryService->create($request->validated());

        if ($request->input('submit_action') === 'save_and_create') {
            return redirect()
                ->route('admin.post-categories.create')
                ->with('success', 'Tạo danh mục mới thành công và tiếp tục tạo mới!');
        }

        return redirect()
            ->route('admin.post-categories.index')
            ->with('success', 'Tạo danh mục bài viết thành công!');
    }

    public function edit(PostCategory $postCategory)
    {
        return view('admin.post_categories.edit', $this->categoryService->editorContext($postCategory));
    }

    public function update(UpdatePostCategoryRequest $request, PostCategory $postCategory)
    {
        $this->categoryService->update($postCategory, $request->validated());

        return redirect()
            ->route('admin.post-categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(PostCategory $postCategory)
    {
        $this->categoryService->delete($postCategory);

        return redirect()
            ->route('admin.post-categories.index')
            ->with('success', 'Xóa danh mục thành công!');
    }
}
