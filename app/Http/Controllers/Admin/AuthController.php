<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


class AuthController extends Controller
{
    public function showLogin(CaptchaService $captcha)
    {
        return view('admin.auth.login', $captcha->generate());
    }


    public function login(LoginRequest $request, CaptchaService $captcha)
    {
        $request->ensureIsNotRateLimited();

        if (!$captcha->check($request->input('captcha'))) {
            RateLimiter::hit($request->throttleKey(), 60);

            return back()->withErrors([
                'captcha' => __('document_ui.contact.captcha_error'),
            ])->onlyInput('email');
        }

        $credentials = $request->only('email', 'password');


        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($request->throttleKey());
            $request->session()->regenerate();
            $request->session()->forget('admin_two_factor_verified_id');
            $request->session()->forget([
                'admin_sensitive_confirmed_at',
                'admin_sensitive_confirmed_id',
                'admin_sensitive_confirmed_version',
            ]);
            $request->session()->put('admin_auth_version', (int) $request->user('admin')->auth_version);

            if ($request->user('admin')->two_factor_confirmed_at) {
                return redirect()->route('admin.two-factor.challenge');
            }

            if ($request->user('admin')->must_change_password) {
                return redirect()->route('admin.password.change');
            }

            return redirect()->intended(route('admin.dashboard'));
        }


        RateLimiter::hit($request->throttleKey(), 60);

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }


    public function showPasswordChange(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->twoFactorChallengeRedirect($request)) {
            return $redirect;
        }

        return view('admin.auth.change-password');
    }


    public function updatePassword(Request $request)
    {
        if ($redirect = $this->twoFactorChallengeRedirect($request)) {
            return $redirect;
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->letters()->numbers()->symbols(),
            ],
        ]);

        $admin = $request->user('admin');

        if (Hash::check($validated['password'], $admin->password)) {
            return back()->withErrors([
                'password' => 'Your new password must be different from your current password.',
            ]);
        }

        $admin->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'password_changed_at' => now(),
            'remember_token' => Str::random(60),
            'auth_version' => (int) $admin->auth_version + 1,
        ])->save();

        $request->session()->regenerate();
        $request->session()->put('admin_auth_version', (int) $admin->auth_version);
        $request->session()->forget([
            'admin_sensitive_confirmed_at',
            'admin_sensitive_confirmed_id',
            'admin_sensitive_confirmed_version',
        ]);

        $route = $admin->two_factor_confirmed_at
            ? 'admin.dashboard'
            : 'admin.two-factor.settings';

        return redirect()->route($route)->with('success', 'Password updated successfully.');
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    private function twoFactorChallengeRedirect(Request $request): ?RedirectResponse
    {
        $admin = $request->user('admin');

        if ($admin?->two_factor_confirmed_at
            && (int) $request->session()->get('admin_two_factor_verified_id') !== (int) $admin->getAuthIdentifier()) {
            return redirect()->route('admin.two-factor.challenge');
        }

        return null;
    }
}
