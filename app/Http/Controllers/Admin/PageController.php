<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\IndexPageRequest;
use App\Http\Requests\Admin\Page\StorePageRequest;
use App\Http\Requests\Admin\Page\UpdatePageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageService $service) {}
    public function index(IndexPageRequest $request): View { return view('admin.pages.index', ['pages' => $this->service->paginate($request->validated()), 'templates' => PageService::TEMPLATES]); }
    public function create(): View { return view('admin.pages.create', ['templates' => PageService::TEMPLATES]); }
    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = $this->service->create($request->validated());
        return $request->input('submit_action') === 'save_and_create'
            ? redirect()->route('admin.pages.create')->with('success', 'Đã tạo trang. Có thể tiếp tục thêm mới.')
            : redirect()->route('admin.pages.edit', $page)->with('success', 'Tạo trang thành công.');
    }
    public function edit(Page $page): View { return view('admin.pages.edit', compact('page') + ['templates' => PageService::TEMPLATES]); }
    public function update(UpdatePageRequest $request, Page $page): RedirectResponse { $this->service->update($page, $request->validated()); return redirect()->route('admin.pages.index')->with('success', 'Cập nhật trang thành công.'); }
    public function destroy(Page $page): RedirectResponse { $this->service->delete($page); return back()->with('success', 'Đã xóa trang.'); }
}
