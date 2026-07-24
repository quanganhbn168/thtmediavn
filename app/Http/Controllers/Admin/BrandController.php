<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexBrandRequest;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brandService) {}

    public function index(IndexBrandRequest $request): View
    {
        return view('admin.brands.index', [
            'brands' => $this->brandService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.brands.create', $this->brandService->formContext(new Brand));
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $brand = $this->brandService->create($request->validated());

        return redirect()->route('admin.brands.edit', $brand)->with('success', 'Đã tạo thương hiệu.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', $this->brandService->formContext($brand));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brandService->update($brand, $request->validated());

        return back()->with('success', 'Đã cập nhật thương hiệu.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        abort_if($brand->products()->exists(), 422, 'Thương hiệu đang có sản phẩm.');

        $this->brandService->delete($brand);

        return back()->with('success', 'Đã xóa thương hiệu.');
    }
}
