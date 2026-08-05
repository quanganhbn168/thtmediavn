<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexComboRequest;
use App\Http\Requests\Admin\StoreComboRequest;
use App\Http\Requests\Admin\UpdateComboRequest;
use App\Models\Combo;
use App\Services\ComboService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComboController extends Controller
{
    public function __construct(private readonly ComboService $comboService) {}

    public function index(IndexComboRequest $request): View
    {
        return view('admin.combos.index', [
            'combos' => $this->comboService->paginate($request->validated()),
            'categories' => $this->comboService->categoriesForFilter(),
        ]);
    }

    public function create(): View
    {
        return view('admin.combos.create', $this->comboService->formContext(new Combo));
    }

    public function store(StoreComboRequest $request): RedirectResponse
    {
        $combo = $this->comboService->create($request->validated());

        return redirect()->route('admin.combos.edit', $combo)->with('success', 'Đã tạo Combo.');
    }

    public function edit(Combo $combo): View
    {
        return view('admin.combos.edit', $this->comboService->formContext($combo));
    }

    public function update(UpdateComboRequest $request, Combo $combo): RedirectResponse
    {
        $this->comboService->update($combo, $request->validated());

        return back()->with('success', 'Đã cập nhật Combo.');
    }

    public function destroy(Combo $combo): RedirectResponse
    {
        $this->comboService->delete($combo);

        return back()->with('success', 'Đã đưa Combo vào thùng rác.');
    }
}
