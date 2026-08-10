<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = trim((string) env('ADMIN_EMAIL'));
        $password = (string) env('ADMIN_PASSWORD');

        if ($email === '' || $password === '') {
            return;
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin = User::query()->updateOrCreate(['email' => $email], [
                'name' => trim((string) env('ADMIN_NAME', 'Quản trị THT MEDIA VN')),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            $admin->assignRole($adminRole);
        }
    }
}
