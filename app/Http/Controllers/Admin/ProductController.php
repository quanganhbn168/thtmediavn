<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexProductRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(IndexProductRequest $request): View
    {
        return view('admin.products.index', [
            'products' => $this->productService->paginate($request->validated()),
            'categories' => $this->productService->categoriesForFilter(),
            'brands' => $this->productService->brandsForFilter(),
        ]);
    }

    public function create(): View
    {
        return $this->editor(new Product);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->create($request->validated());

        if ($request->input('submit_action') === 'save_and_create') {
            return redirect()
                ->route('admin.products.create')
                ->with('success', 'Đã tạo sản phẩm và sẵn sàng tạo sản phẩm mới.');
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Đã tạo sản phẩm.');
    }

    public function edit(Product $product): View
    {
        return $this->editor($product->load(['variants.values', 'options', 'media']));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update($product, $request->validated());

        return back()->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);
        return back()->with('success', 'Đã đưa sản phẩm vào thùng rác.');
    }

    private function editor(Product $product): View
    {
        return view($product->exists ? 'admin.products.edit' : 'admin.products.create', $this->productService->editorContext($product));
    }
}
