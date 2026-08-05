<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexComboCategoryRequest;
use App\Http\Requests\Admin\StoreComboCategoryRequest;
use App\Http\Requests\Admin\UpdateComboCategoryRequest;
use App\Models\ComboCategory;
use App\Services\ComboCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComboCategoryController extends Controller
{
    public function __construct(private readonly ComboCategoryService $categoryService) {}

    public function index(IndexComboCategoryRequest $request): View
    {
        return view('admin.combo_categories.index', ['categories' => $this->categoryService->paginate($request->validated())]);
    }

    public function create(): View
    {
        return view('admin.combo_categories.create', ['category' => new ComboCategory]);
    }

    public function store(StoreComboCategoryRequest $request): RedirectResponse
    {
        $category = $this->categoryService->create($request->validated());

        return redirect()->route('admin.combo-categories.edit', $category)->with('success', 'Đã tạo danh mục Combo.');
    }

    public function edit(ComboCategory $comboCategory): View
    {
        return view('admin.combo_categories.edit', ['category' => $comboCategory]);
    }

    public function update(UpdateComboCategoryRequest $request, ComboCategory $comboCategory): RedirectResponse
    {
        $this->categoryService->update($comboCategory, $request->validated());

        return back()->with('success', 'Đã cập nhật danh mục Combo.');
    }

    public function destroy(ComboCategory $comboCategory): RedirectResponse
    {
        abort_if($comboCategory->combos()->exists(), 422, 'Danh mục đang có Combo liên quan.');
        $this->categoryService->delete($comboCategory);

        return back()->with('success', 'Đã xóa danh mục Combo.');
    }
}
