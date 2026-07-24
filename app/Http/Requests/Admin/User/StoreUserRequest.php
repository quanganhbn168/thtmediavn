<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'exists:roles,name',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $role = Role::query()->with('permissions')->where('name', $value)->first();
                    $actor = $this->user();
                    $exceedsActor = $role && ! $actor->hasRole('admin') && (
                        $role->name === 'admin'
                        || $role->permissions->pluck('name')->diff($actor->getAllPermissions()->pluck('name'))->isNotEmpty()
                    );

                    if (! $role || $exceedsActor) {
                        $fail('Bạn không được phép gán vai trò đã chọn.');
                    } elseif ($role->name !== 'admin' && $role->permissions->isEmpty()) {
                        $fail('Vai trò đã chọn không có quyền truy cập khu vực quản trị.');
                    }
                },
            ],
        ];
    }
}
