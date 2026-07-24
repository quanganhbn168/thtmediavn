<?php

namespace Database\Seeders;

use App\Support\AdminPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = AdminPermission::all();

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Khởi tạo vai trò
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Gán tất cả quyền cho vai trò admin
        $adminRole->syncPermissions(AdminPermission::all());
    }
}
