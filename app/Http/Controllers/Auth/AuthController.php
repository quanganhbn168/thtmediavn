<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AdminPermission;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(Request $request): View
    {
        $isAdmin = $request->routeIs('admin.login');
        $redirectTo = (string) $request->query('redirect', '');

        if (! $isAdmin && str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//')) {
            $request->session()->put('url.intended', url($redirectTo));
        }

        return view($isAdmin ? 'admin.auth' : 'frontend.auth', [
            'isAdmin' => $isAdmin,
        ]);
    }

    public function showRegister(): View { return view('frontend.auth'); }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $isAdminLogin = $request->routeIs('admin.login*');
        $guard = $isAdminLogin ? 'admin' : 'web';

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);
        $credentials = $this->addActiveFilter($credentials);

        $remember = $request->boolean('remember');

        $sessionId = app(CartService::class)->guestToken();

        if (Auth::guard($guard)->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::guard($guard)->user();

            if (! $user) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Có lỗi xảy ra khi xác thực tài khoản.',
                ])->onlyInput('email', 'remember');
            }

            app(CartService::class)->mergeGuestCart($user, $sessionId);

            if ($isAdminLogin && ! AdminPermission::can($user, [])) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Tài khoản này không có quyền quản trị.',
                ])->onlyInput('email', 'remember');
            }

            if ($isAdminLogin) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('account.index'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email', 'remember');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $sessionId = app(CartService::class)->guestToken();
        $payload = $data + ['password' => Hash::make($data['password'])];

        if (Schema::hasColumn('users', 'is_active')) {
            $payload['is_active'] = true;
        }

        $user = User::create($payload);
        $user->assignRole('customer');
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        app(CartService::class)->mergeGuestCart($user, $sessionId);
        return redirect()->route('account.index')->with('success', 'Tạo tài khoản thành công.');
    }

    private function addActiveFilter(array $credentials): array
    {
        if (Schema::hasColumn('users', 'is_active')) {
            return $credentials + ['is_active' => true];
        }

        return $credentials;
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
