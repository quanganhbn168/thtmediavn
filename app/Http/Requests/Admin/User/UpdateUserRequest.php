<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends StoreUserRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))];
        $rules['password'] = 'nullable|string|min:8|confirmed';

        return $rules;
    }
}
