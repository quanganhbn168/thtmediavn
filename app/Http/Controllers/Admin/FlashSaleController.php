<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexFlashSaleRequest;
use App\Http\Requests\Admin\StoreFlashSaleRequest;
use App\Http\Requests\Admin\UpdateFlashSaleRequest;
use App\Models\FlashSale;
use App\Services\FlashSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlashSaleController extends Controller
{
    public function __construct(private readonly FlashSaleService $flashSaleService) {}

    public function index(IndexFlashSaleRequest $request): View
    {
        return view('admin.flash_sales.index', [
            'sales' => $this->flashSaleService->paginate($request->validated()),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->editor(new FlashSale, $request);
    }

    public function products(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:24'],
        ]);

        return response()->json($this->flashSaleService->productPicker($filters));
    }

    public function store(StoreFlashSaleRequest $request): RedirectResponse
    {
        $sale = $this->flashSaleService->create($request->validated());
        if ($request->input('submit_action') === 'save_and_create') {
            return redirect()->route('admin.flash-sales.create')->with('success', 'Đã tạo Flash Sale.');
        }

        return redirect()->route('admin.flash-sales.edit', $sale)->with('success', 'Đã tạo Flash Sale.');
    }

    public function edit(FlashSale $flashSale, Request $request): View
    {
        return $this->editor($flashSale, $request);
    }

    public function update(UpdateFlashSaleRequest $request, FlashSale $flashSale): RedirectResponse
    {
        $this->flashSaleService->update($flashSale, $request->validated());

        return back()->with('success', 'Đã cập nhật Flash Sale.');
    }

    public function destroy(FlashSale $flashSale): RedirectResponse
    {
        $this->flashSaleService->delete($flashSale);

        return back()->with('success', 'Đã xóa Flash Sale.');
    }

    private function editor(FlashSale $sale, Request $request): View
    {
        $oldItems = $request->session()->hasOldInput('items')
            ? (array) $request->session()->getOldInput('items')
            : null;

        return view(
            $sale->exists ? 'admin.flash_sales.edit' : 'admin.flash_sales.create',
            $this->flashSaleService->editorContext($sale, $oldItems)
        );
    }
}
