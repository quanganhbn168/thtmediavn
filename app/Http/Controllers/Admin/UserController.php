<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'role' => 'nullable|exists:roles,name',
            'per_page' => 'nullable|integer|in:10,25,50',
        ]);
        $query = User::query()->with('roles.permissions');
        $search = trim((string) ($data['search'] ?? ''));

        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }
        if (! empty($data['role'])) {
            $query->role($data['role']);
        }

        return view('admin.users.index', [
            'users' => $query->latest()->paginate((int) ($data['per_page'] ?? 10))->withQueryString(),
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role'], $data['password_confirmation']);

        $user = User::create($data);
        $user->syncRoles([$role]);

        return redirect()->route('admin.users.edit', $user)->with('success', 'Tạo tài khoản thành công.');
    }

    public function edit(User $user): View
    {
        $this->ensureCanManage($user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureCanManage($user);
        $data = $request->validated();
        $role = $data['role'];

        if ($user->hasRole('admin') && $role !== 'admin' && User::role('admin')->count() <= 1) {
            return back()->withInput()->with('error', 'Phải giữ lại ít nhất một tài khoản có vai trò admin.');
        }

        unset($data['role'], $data['password_confirmation']);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles([$role]);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureCanManage($user);

        if ($user->is(auth()->user())) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('error', 'Phải giữ lại ít nhất một tài khoản có vai trò admin.');
        }

        $user->delete();

        return back()->with('success', 'Đã xóa tài khoản.');
    }

    private function assignableRoles(): Collection
    {
        $actor = auth()->user();
        $roles = Role::query()->with('permissions')->orderBy('name')->get()
            ->filter(fn (Role $role) => $role->name === 'admin' || $role->permissions->isNotEmpty());

        if (! $actor->hasRole('admin')) {
            $actorPermissions = $actor->getAllPermissions()->pluck('name');
            $roles = $roles->filter(fn (Role $role) => $role->name !== 'admin'
                && $role->permissions->pluck('name')->diff($actorPermissions)->isEmpty());
        }

        return $roles->pluck('name', 'name');
    }

    private function ensureCanManage(User $target): void
    {
        $actor = auth()->user();

        if (! $target->canBeManagedBy($actor)) {
            abort(403, 'Bạn không thể quản lý tài khoản có phạm vi quyền cao hơn mình.');
        }
    }
}
