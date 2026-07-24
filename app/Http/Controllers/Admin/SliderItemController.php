<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSliderItemRequest;
use App\Http\Requests\Admin\UpdateSliderItemRequest;
use App\Models\SliderItem;
use App\Models\Slider;
use App\Services\SliderItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SliderItemController extends Controller
{
    public function __construct(private readonly SliderItemService $sliderItemService) {}

    public function create(Slider $slider): View
    {
        return view('admin.slider_items.create', compact('slider'));
    }

    public function edit(SliderItem $item): View
    {
        $item->load(['slider', 'media']);

        return view('admin.slider_items.edit', compact('item'));
    }

    /**
     * Lưu ảnh slide mới.
     */
    public function store(StoreSliderItemRequest $request): RedirectResponse
    {
        $item = $this->sliderItemService->create($request->validated());

        return redirect()
            ->route('admin.sliders.edit', $item->slider_id)
            ->with('success', 'Thêm ảnh slide thành công!');
    }

    /**
     * Cập nhật thông tin ảnh slide.
     */
    public function update(UpdateSliderItemRequest $request, SliderItem $item): RedirectResponse
    {
        $this->sliderItemService->update($item, $request->validated());

        return redirect()
            ->route('admin.sliders.edit', $item->slider_id)
            ->with('success', 'Cập nhật ảnh slide thành công!');
    }

    /**
     * Xóa ảnh slide.
     */
    public function destroy(SliderItem $item): RedirectResponse
    {
        $this->sliderItemService->delete($item);

        return redirect()
            ->back()
            ->with('success', 'Xóa ảnh slide thành công!');
    }
}
