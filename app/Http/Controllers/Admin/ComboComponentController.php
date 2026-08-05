<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexComboComponentRequest;
use App\Http\Requests\Admin\StoreComboComponentRequest;
use App\Http\Requests\Admin\UpdateComboComponentRequest;
use App\Models\Combo;
use App\Models\ComboItem;
use App\Services\ComboComponentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComboComponentController extends Controller
{
    public function __construct(private readonly ComboComponentService $componentService) {}

    public function index(IndexComboComponentRequest $request, Combo $combo): View
    {
        return view('admin.combo_components.index', [
            'combo' => $combo,
            'components' => $this->componentService->paginate($combo, $request->validated()),
        ]);
    }

    public function create(Combo $combo): View
    {
        return view('admin.combo_components.create', $this->componentService->editorContext($combo, new ComboItem));
    }

    public function store(StoreComboComponentRequest $request, Combo $combo): RedirectResponse
    {
        $this->componentService->create($combo, $request->validated());

        return redirect()->route('admin.combos.components.index', $combo)->with('success', 'Đã thêm thành phần vào Combo.');
    }

    public function edit(Combo $combo, ComboItem $comboItem): View
    {
        $this->ensureBelongsToCombo($combo, $comboItem);

        return view('admin.combo_components.edit', $this->componentService->editorContext($combo, $comboItem));
    }

    public function update(UpdateComboComponentRequest $request, Combo $combo, ComboItem $comboItem): RedirectResponse
    {
        $this->ensureBelongsToCombo($combo, $comboItem);
        $this->componentService->update($comboItem, $request->validated());

        return back()->with('success', 'Đã cập nhật thành phần Combo.');
    }

    public function destroy(Combo $combo, ComboItem $comboItem): RedirectResponse
    {
        $this->ensureBelongsToCombo($combo, $comboItem);
        $this->componentService->delete($comboItem);

        return back()->with('success', 'Đã xóa thành phần khỏi Combo.');
    }

    private function ensureBelongsToCombo(Combo $combo, ComboItem $comboItem): void
    {
        abort_unless((int) $comboItem->combo_id === (int) $combo->id, 404);
    }
}
