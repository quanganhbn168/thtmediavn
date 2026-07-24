<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|in:10,25,50',
        ]);
        $query = Role::query()->withCount(['permissions', 'users']);

        if ($search = trim((string) ($data['search'] ?? ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        return view('admin.roles.index', [
            'roles' => $query->latest('id')->paginate((int) ($data['per_page'] ?? 10))->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', ['permissions' => $this->permissions()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($this->selectedPermissions($request));

        return redirect()->route('admin.roles.edit', $role)->with('success', 'Tạo vai trò thành công.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $this->permissions(),
            'selected' => $role->permissions->pluck('id')->all(),
            'isSystemRole' => $role->name === 'admin',
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'admin') {
            $role->syncPermissions(AdminPermission::all());

            return redirect()->route('admin.roles.index')
                ->with('success', 'Vai trò hệ thống admin đã được đồng bộ đầy đủ quyền.');
        }

        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($this->selectedPermissions($request));

        return redirect()->route('admin.roles.index')->with('success', 'Cập nhật vai trò thành công.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->loadCount('users');

        if ($role->name === 'admin' || $role->users_count > 0) {
            return back()->with('error', 'Không thể xóa vai trò hệ thống hoặc vai trò đang được sử dụng.');
        }

        $role->delete();

        return back()->with('success', 'Đã xóa vai trò.');
    }

    private function selectedPermissions(Request $request): array
    {
        $selected = Permission::query()
            ->whereKey($request->input('permissions', []))
            ->pluck('name')
            ->push('view dashboard')
            ->unique()
            ->all();

        return array_values(array_intersect($selected, AdminPermission::all()));
    }

    private function permissions(): Collection
    {
        $definitions = config('admin.permissions', []);

        return Permission::query()
            ->whereIn('name', array_keys($definitions))
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Permission $permission) => $definitions[$permission->name]['group'] ?? 'Khác');
    }
}
