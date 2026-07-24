<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa sạch user cũ
        User::query()->delete();

        // Lấy vai trò tương ứng
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'customer')->first();

        // Tạo tài khoản admin mẫu
        if ($adminRole) {
            $admin = User::factory()->create([
                'name' => 'Quản trị viên',
                'email' => 'admin@example.com',
            ]);
            $admin->assignRole($adminRole);
        }

        // Tạo tài khoản user mẫu
        if ($userRole) {
            $user = User::factory()->create([
                'name' => 'Khách hàng mẫu',
                'email' => 'khachhang@example.com',
                'phone' => '0901234567',
            ]);
            $user->assignRole($userRole);
        }
    }
}
