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
        ]);
    }

    public function create(): View
    {
        return $this->form(new Product);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->create($request->validated());
        return redirect()->route('admin.products.edit', $product)->with('success', 'Đã tạo sản phẩm.');
    }

    public function edit(Product $product): View
    {
        return $this->form($product->load(['variants.values', 'options', 'media']));
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

    private function form(Product $product): View
    {
        return view($product->exists ? 'admin.products.edit' : 'admin.products.create', $this->productService->formContext($product));
    }
}

