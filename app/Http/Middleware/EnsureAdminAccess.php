<?php

namespace App\Http\Middleware;

use App\Support\AdminPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin');

        if (! $user) {
            abort(403, 'Tài khoản không có quyền truy cập khu vực quản trị.');
        }

        $requiredPermissions = AdminPermission::requiredFor($request);

        // Route admin mới phải được khai báo tường minh trong ma trận quyền.
        if ($requiredPermissions === null || ! AdminPermission::can($user, $requiredPermissions)) {
            abort(403, 'Tài khoản không có quyền thực hiện chức năng quản trị này.');
        }

        return $next($request);
    }
}
