<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('admin/*')
                ? route('admin.login')
                : route('login');
        });
        $middleware->redirectUsersTo(function () {
            if (Auth::guard('admin')->check()) {
                return route('admin.dashboard');
            }

            return route('account.index');
        });
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdminAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
