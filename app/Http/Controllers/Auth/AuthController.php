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
    private const FRONTEND_GUARD = 'web';

    private const ADMIN_GUARD = 'admin';

    /**
     * Show the login form.
     */
    public function showFrontendLogin(Request $request): View
    {
        $redirectTo = (string) $request->query('redirect', '');

        if (str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//')) {
            $request->session()->put('url.intended', url($redirectTo));
        }

        return view('frontend.auth', ['isAdmin' => false]);
    }

    public function showAdminLogin(): View
    {
        return view('admin.auth', ['isAdmin' => true]);
    }

    public function showRegister(): View
    {
        return view('frontend.auth');
    }

    /**
     * Handle authentication attempt.
     */
    public function loginFrontend(Request $request): RedirectResponse
    {
        return $this->attemptLogin($request, self::FRONTEND_GUARD, false);
    }

    public function loginAdmin(Request $request): RedirectResponse
    {
        return $this->attemptLogin($request, self::ADMIN_GUARD, true);
    }

    private function attemptLogin(Request $request, string $guard, bool $isAdminLogin): RedirectResponse
    {
        $loginField = $isAdminLogin ? 'email' : 'login';

        $validated = $request->validate([
            $loginField => $isAdminLogin ? ['required', 'email'] : ['required', 'string', 'max:150'],
            'password' => ['required'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'login.required' => 'Vui lòng nhập email hoặc số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);
        $credentials = $isAdminLogin
            ? $validated
            : $this->credentialsForFrontendLogin($validated['login'], $validated['password']);
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
                    $loginField => 'Có lỗi xảy ra khi xác thực tài khoản.',
                ])->onlyInput($loginField, 'remember');
            }

            app(CartService::class)->mergeGuestCart($user, $sessionId);

            if ($isAdminLogin && ! AdminPermission::can($user, [])) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    $loginField => 'Tài khoản này không có quyền quản trị.',
                ])->onlyInput($loginField, 'remember');
            }

            if ($isAdminLogin) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('account.index'));
        }

        return back()->withErrors([
            $loginField => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput($loginField, 'remember');
    }

    private function credentialsForFrontendLogin(string $login, string $password): array
    {
        $login = trim($login);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) !== false ? 'email' : 'phone';

        return [
            $field => $login,
            'password' => $password,
        ];
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
        Auth::guard(self::FRONTEND_GUARD)->login($user);
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
        Auth::guard(self::ADMIN_GUARD)->logout();
        Auth::guard(self::FRONTEND_GUARD)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
