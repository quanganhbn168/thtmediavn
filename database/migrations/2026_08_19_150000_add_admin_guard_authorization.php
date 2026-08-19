<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ADMIN_GUARD = 'admin';

    /**
     * Copy the existing web authorization data into the dedicated admin guard.
     * Old web rows are intentionally retained so this migration does not
     * disturb any frontend authorization that may exist later.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names', []);
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $rolesTable = $tableNames['roles'] ?? 'roles';
        $modelHasPermissionsTable = $tableNames['model_has_permissions'] ?? 'model_has_permissions';
        $modelHasRolesTable = $tableNames['model_has_roles'] ?? 'model_has_roles';
        $roleHasPermissionsTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        foreach ([$permissionsTable, $rolesTable, $modelHasPermissionsTable, $modelHasRolesTable, $roleHasPermissionsTable] as $table) {
            if (! Schema::hasTable($table)) {
                return;
            }
        }

        $rolePivot = config('permission.column_names.role_pivot_key') ?: 'role_id';
        $permissionPivot = config('permission.column_names.permission_pivot_key') ?: 'permission_id';
        $modelMorphKey = config('permission.column_names.model_morph_key') ?: 'model_id';

        DB::table($permissionsTable)
            ->where('guard_name', 'web')
            ->get(['name'])
            ->each(function (object $permission) use ($permissionsTable): void {
                DB::table($permissionsTable)->insertOrIgnore([
                    'name' => $permission->name,
                    'guard_name' => self::ADMIN_GUARD,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        $adminPermissionIds = DB::table($permissionsTable)
            ->where('guard_name', self::ADMIN_GUARD)
            ->pluck('id', 'name');

        $adminRoleIds = [];
        foreach (['admin', 'super_admin'] as $roleName) {
            $adminRole = DB::table($rolesTable)
                ->where('name', $roleName)
                ->where('guard_name', self::ADMIN_GUARD)
                ->first(['id']);

            $adminRoleId = $adminRole?->id;
            if ($adminRoleId === null) {
                $adminRoleId = DB::table($rolesTable)->insertGetId([
                    'name' => $roleName,
                    'guard_name' => self::ADMIN_GUARD,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $adminRoleIds[$roleName] = $adminRoleId;

            $webRole = DB::table($rolesTable)
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first(['id']);

            if (! $webRole) {
                continue;
            }

            DB::table($roleHasPermissionsTable)
                ->join($permissionsTable, $permissionsTable.'.id', '=', $roleHasPermissionsTable.'.'.$permissionPivot)
                ->where($roleHasPermissionsTable.'.'.$rolePivot, $webRole->id)
                ->where($permissionsTable.'.guard_name', 'web')
                ->get([$permissionsTable.'.name'])
                ->each(function (object $permission) use ($roleHasPermissionsTable, $rolePivot, $permissionPivot, $adminRoleId, $adminPermissionIds): void {
                    $adminPermissionId = $adminPermissionIds[$permission->name] ?? null;
                    if ($adminPermissionId === null) {
                        return;
                    }

                    DB::table($roleHasPermissionsTable)->insertOrIgnore([
                        $permissionPivot => $adminPermissionId,
                        $rolePivot => $adminRoleId,
                    ]);
                });
        }

        $oldAdminRoles = DB::table($rolesTable)
            ->where('guard_name', 'web')
            ->whereIn('name', ['admin', 'super_admin'])
            ->get(['id', 'name']);

        foreach ($oldAdminRoles as $oldRole) {
            DB::table($modelHasRolesTable)
                ->where($rolePivot, $oldRole->id)
                ->get([$modelMorphKey, 'model_type'])
                ->each(function (object $assignment) use ($modelHasRolesTable, $rolePivot, $modelMorphKey, $oldRole, $adminRoleIds): void {
                    $roleNames = $oldRole->name === 'admin' ? ['admin', 'super_admin'] : ['super_admin'];

                    foreach ($roleNames as $roleName) {
                        DB::table($modelHasRolesTable)->insertOrIgnore([
                            $rolePivot => $adminRoleIds[$roleName],
                            $modelMorphKey => $assignment->{$modelMorphKey},
                            'model_type' => $assignment->model_type,
                        ]);
                    }
                });
        }

        DB::table($modelHasPermissionsTable)
            ->join($permissionsTable, $permissionsTable.'.id', '=', $modelHasPermissionsTable.'.'.$permissionPivot)
            ->where($permissionsTable.'.guard_name', 'web')
            ->get([
                $modelHasPermissionsTable.'.'.$modelMorphKey,
                $modelHasPermissionsTable.'.model_type',
                $permissionsTable.'.name',
            ])
            ->each(function (object $assignment) use ($modelHasPermissionsTable, $permissionPivot, $modelMorphKey, $adminPermissionIds): void {
                $adminPermissionId = $adminPermissionIds[$assignment->name] ?? null;
                if ($adminPermissionId === null) {
                    return;
                }

                DB::table($modelHasPermissionsTable)->insertOrIgnore([
                    $permissionPivot => $adminPermissionId,
                    $modelMorphKey => $assignment->{$modelMorphKey},
                    'model_type' => $assignment->model_type,
                ]);
            });
    }

    public function down(): void
    {
        // Keep copied authorization data on rollback; deleting it could remove
        // permissions created in the admin guard after this migration ran.
    }
};
