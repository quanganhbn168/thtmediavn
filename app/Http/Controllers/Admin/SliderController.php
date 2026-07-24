<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SliderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexSliderRequest;
use App\Http\Requests\Admin\StoreSliderRequest;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Models\Slider;
use App\Services\SliderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function __construct(private readonly SliderService $sliderService) {}

    /**
     * Danh sách Sliders.
     */
    public function index(IndexSliderRequest $request): View
    {
        $sliders = $this->sliderService->paginate($request->validated());

        $sliderTypes = SliderType::options();

        return view('admin.sliders.index', compact('sliders', 'sliderTypes'));
    }

    /**
     * Form tạo mới Slider.
     */
    public function create(): View
    {
        return view('admin.sliders.create', ['sliderTypes' => SliderType::options()]);
    }

    /**
     * Lưu Slider mới.
     */
    public function store(StoreSliderRequest $request): RedirectResponse
    {
        $slider = $this->sliderService->create($request->validated());

        return redirect()
            ->route('admin.sliders.edit', $slider->id)
            ->with('success', 'Tạo bộ trình chiếu thành công! Hãy thêm các ảnh slide bên dưới.');
    }

    /**
     * Form sửa Slider & danh sách Slider Items.
     */
    public function edit(Slider $slider): View
    {
        $items = $this->sliderService->items($slider);

        return view('admin.sliders.edit', [
            'slider' => $slider,
            'items' => $items,
            'sliderTypes' => SliderType::options(),
        ]);
    }

    /**
     * Cập nhật thông tin Slider.
     */
    public function update(UpdateSliderRequest $request, Slider $slider): RedirectResponse
    {
        $this->sliderService->update($slider, $request->validated());

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Cập nhật bộ trình chiếu thành công!');
    }

    /**
     * Xóa Slider.
     */
    public function destroy(Slider $slider): RedirectResponse
    {
        $this->sliderService->delete($slider);

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Xóa bộ trình chiếu thành công!');
    }
}
