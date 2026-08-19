<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    /**
     * Update user profile details.
     */
    public function updateProfile(User $user, array $data): void
    {
        $user->name = $data['name'];
        $user->email = $data['email'];

        // Cập nhật mật khẩu nếu có nhập mật khẩu mới
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
    }
}
