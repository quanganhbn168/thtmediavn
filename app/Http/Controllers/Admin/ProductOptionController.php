<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexProductOptionRequest;
use App\Http\Requests\Admin\StoreProductOptionRequest;
use App\Http\Requests\Admin\UpdateProductOptionRequest;
use App\Models\ProductOption;
use App\Services\ProductOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductOptionController extends Controller
{
    public function __construct(private readonly ProductOptionService $productOptionService) {}

    public function index(IndexProductOptionRequest $request): View
    {
        return view('admin.product_options.index', [
            'options' => $this->productOptionService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.product_options.create', $this->productOptionService->formContext(new ProductOption));
    }

    public function store(StoreProductOptionRequest $request): RedirectResponse
    {
        $option = $this->productOptionService->create($request->validated());

        return redirect()->route('admin.product-options.edit', $option)->with('success', 'Đã tạo thuộc tính sản phẩm.');
    }

    public function edit(ProductOption $productOption): View
    {
        return view('admin.product_options.edit', $this->productOptionService->formContext($productOption->load('values')));
    }

    public function update(UpdateProductOptionRequest $request, ProductOption $productOption): RedirectResponse
    {
        $this->productOptionService->update($productOption, $request->validated());

        return back()->with('success', 'Đã cập nhật thuộc tính.');
    }

    public function destroy(ProductOption $productOption): RedirectResponse
    {
        abort_if($productOption->products()->exists(), 422, 'Thuộc tính đang được dùng.');

        $this->productOptionService->delete($productOption);

        return back()->with('success', 'Đã xóa thuộc tính.');
    }
}
