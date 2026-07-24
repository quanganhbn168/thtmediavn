<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Validation\Rule;

class UpdateRoleRequest extends StoreRoleRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['name'] = ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($this->route('role'))];
        if ($this->route('role')?->name === 'admin') {
            $rules['permissions'] = 'nullable|array';
            $rules['permissions.*'] = 'integer|distinct|exists:permissions,id';
        }

        return $rules;
    }
}
