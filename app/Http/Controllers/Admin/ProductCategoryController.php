<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexProductCategoryRequest;
use App\Http\Requests\Admin\StoreProductCategoryRequest;
use App\Http\Requests\Admin\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly ProductCategoryService $productCategoryService) {}

    public function index(IndexProductCategoryRequest $request): View
    {
        return view('admin.product_categories.index', [
            'categories' => $this->productCategoryService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.product_categories.create', $this->productCategoryService->formContext(new ProductCategory));
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        $category = $this->productCategoryService->create($request->validated());

        return redirect()
            ->route('admin.product-categories.edit', $category)
            ->with('success', 'Đã tạo danh mục sản phẩm.');
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('admin.product_categories.edit', $this->productCategoryService->formContext($productCategory));
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->productCategoryService->update($productCategory, $request->validated());

        return back()->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        abort_if(
            $productCategory->products()->exists() || $productCategory->children()->exists(),
            422,
            'Danh mục đang có dữ liệu liên quan.'
        );

        $this->productCategoryService->delete($productCategory);

        return back()->with('success', 'Đã xóa danh mục.');
    }
}
