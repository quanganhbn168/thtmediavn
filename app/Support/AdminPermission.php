<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class AdminPermission
{
    public static function all(): array
    {
        return array_keys(config('admin.permissions', []));
    }

    public static function requiredFor(Request $request): ?array
    {
        $routeName = $request->route()?->getName();

        if (! is_string($routeName)) {
            return null;
        }

        if (in_array($routeName, ['admin.common.bulk-action', 'admin.common.reorder'], true)) {
            $permission = config('admin.resource_permissions.'.(string) $request->input('resource'));

            return $permission ? [$permission] : null;
        }

        if ($routeName === 'admin.common.toggle-field') {
            $permission = config('admin.model_permissions.'.(string) $request->input('model'));

            return $permission ? [$permission] : null;
        }

        if (in_array($routeName, ['admin.media.upload.editor', 'admin.media.list'], true)) {
            return ['manage content'];
        }

        if ($routeName === 'admin.media.upload.temp') {
            return config('admin.media_permissions', []);
        }

        return self::requiredForRouteName($routeName);
    }

    public static function requiredForRouteName(string $routeName): ?array
    {
        foreach (config('admin.route_permissions', []) as $routePrefix => $permissions) {
            if ($routeName === $routePrefix || str_starts_with($routeName, $routePrefix)) {
                return (array) $permissions;
            }
        }

        return null;
    }

    public static function can(User $user, string|array|null $permissions): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $permissions = array_values(array_filter((array) $permissions));

        return $permissions === []
            ? $user->getAllPermissions()->isNotEmpty()
            : $user->hasAnyPermission($permissions);
    }
}
