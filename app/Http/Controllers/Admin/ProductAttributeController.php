<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexProductAttributeRequest;
use App\Http\Requests\Admin\StoreProductAttributeRequest;
use App\Http\Requests\Admin\UpdateProductAttributeRequest;
use App\Models\ProductAttribute;
use App\Services\ProductAttributeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductAttributeController extends Controller
{
    public function __construct(private readonly ProductAttributeService $productAttributeService) {}

    public function index(IndexProductAttributeRequest $request): View
    {
        return view('admin.product_attributes.index', [
            'attributes' => $this->productAttributeService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.product_attributes.create', $this->productAttributeService->editorContext(new ProductAttribute));
    }

    public function store(StoreProductAttributeRequest $request): RedirectResponse
    {
        $attribute = $this->productAttributeService->create($request->validated());

        return redirect()->route('admin.product-attributes.edit', $attribute)->with('success', 'Đã tạo thuộc tính lọc.');
    }

    public function edit(ProductAttribute $productAttribute): View
    {
        return view('admin.product_attributes.edit', $this->productAttributeService->editorContext($productAttribute));
    }

    public function update(UpdateProductAttributeRequest $request, ProductAttribute $productAttribute): RedirectResponse
    {
        $this->productAttributeService->update($productAttribute, $request->validated());

        return back()->with('success', 'Đã cập nhật thuộc tính lọc.');
    }

    public function destroy(ProductAttribute $productAttribute): RedirectResponse
    {
        $this->productAttributeService->delete($productAttribute);

        return back()->with('success', 'Đã xóa thuộc tính lọc.');
    }
}
