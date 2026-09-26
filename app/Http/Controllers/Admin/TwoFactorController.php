<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminTotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TwoFactorController extends Controller
{
    public function __construct(private AdminTotpService $totp)
    {
    }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        $admin = $this->admin();
        if (!$admin->two_factor_confirmed_at) {
            return redirect()->route('admin.two-factor.settings');
        }

        if ((int) $request->session()->get('admin_two_factor_verified_id') === (int) $admin->id) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return view('admin.auth.two-factor-challenge', ['admin' => $admin]);
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $admin = $this->admin();
        $key = 'admin-two-factor:' . $admin->id . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        if (!$this->validCode($admin, $data['code'])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['code' => 'The authentication code is invalid.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('admin_two_factor_verified_id', $admin->id);

        if ($admin->must_change_password) {
            return redirect()->route('admin.password.change');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function settings(Request $request): View|RedirectResponse
    {
        $admin = $this->admin();

        if ($admin->two_factor_confirmed_at
            && (int) $request->session()->get('admin_two_factor_verified_id') !== (int) $admin->id) {
            return redirect()->route('admin.two-factor.challenge');
        }

        if (!$admin->two_factor_secret) {
            $admin->forceFill(['two_factor_secret' => $this->totp->generateSecret()])->save();
        }

        return view('admin.security.two-factor', [
            'admin' => $admin->fresh(),
            'provisioningUri' => $this->totp->provisioningUri(
                (string) $admin->two_factor_secret,
                (string) $admin->email
            ),
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $admin = $this->admin();

        if ($admin->two_factor_confirmed_at) {
            return redirect()->route('admin.two-factor.challenge');
        }

        $data = $request->validate(['code' => ['required', 'digits:6']]);

        if (!$admin->two_factor_secret || !$this->consumeTotp($admin, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'The authentication code is invalid.']);
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes();
        $admin->forceFill([
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();
        $request->session()->regenerate();
        $request->session()->put('admin_two_factor_verified_id', $admin->id);
        $request->session()->flash('admin_recovery_codes', $recoveryCodes);

        return redirect()
            ->route('admin.two-factor.settings')
            ->with('success', 'Two-factor authentication is now enabled. Save your recovery codes.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);
        $admin = $this->admin();
        $this->validatePasswordAndTotp($admin, $data['password'], $data['code']);

        $recoveryCodes = $this->totp->generateRecoveryCodes();
        $admin->forceFill([
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();
        $request->session()->flash('admin_recovery_codes', $recoveryCodes);

        return back()->with('success', 'New recovery codes generated. Previous codes no longer work.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);
        $admin = $this->admin();
        $this->validatePasswordAndTotp($admin, $data['password'], $data['code']);

        $admin->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
            'auth_version' => (int) $admin->auth_version + 1,
        ])->save();
        $request->session()->regenerate();
        $request->session()->put('admin_auth_version', (int) $admin->auth_version);
        $request->session()->forget('admin_two_factor_verified_id');
        $request->session()->forget([
            'admin_sensitive_confirmed_at',
            'admin_sensitive_confirmed_id',
            'admin_sensitive_confirmed_version',
        ]);

        return redirect()
            ->route('admin.two-factor.settings')
            ->with('warning', 'Two-factor authentication was disabled. Set it up again to continue.');
    }

    public function showSensitiveConfirmation(): View
    {
        return view('admin.auth.confirm-sensitive');
    }

    public function confirmSensitiveAccess(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'current_password:admin'],
            'code' => ['required', 'digits:6'],
        ]);
        $admin = $this->admin();

        if (!$this->consumeTotp($admin, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'The authentication code is invalid or was already used.']);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'admin_sensitive_confirmed_at' => time(),
            'admin_sensitive_confirmed_id' => $admin->id,
            'admin_sensitive_confirmed_version' => (int) $admin->auth_version,
        ]);

        return redirect()->intended(route('admin.admin-users.index'));
    }

    private function admin(): Admin
    {
        $admin = auth('admin')->user();
        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }

    private function validatePasswordAndTotp(Admin $admin, string $password, string $code): void
    {
        if (!Hash::check($password, (string) $admin->password)) {
            throw ValidationException::withMessages(['password' => 'The current password is incorrect.']);
        }

        if (!$this->consumeTotp($admin, $code)) {
            throw ValidationException::withMessages(['code' => 'The authentication code is invalid or was already used.']);
        }
    }

    private function validCode(Admin $admin, string $code): bool
    {
        if ($this->consumeTotp($admin, $code)) {
            return true;
        }

        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            return false;
        }

        return DB::transaction(function () use ($admin, $normalized) {
            $lockedAdmin = Admin::query()->lockForUpdate()->findOrFail($admin->id);
            $recoveryCodes = array_values((array) ($lockedAdmin->two_factor_recovery_codes ?? []));

            foreach ($recoveryCodes as $index => $recoveryCode) {
                if (!hash_equals(strtoupper((string) $recoveryCode), $normalized)) {
                    continue;
                }

                unset($recoveryCodes[$index]);
                $lockedAdmin->forceFill([
                    'two_factor_recovery_codes' => array_values($recoveryCodes),
                ])->save();

                return true;
            }

            return false;
        });
    }

    private function consumeTotp(Admin $admin, string $code): bool
    {
        try {
            return DB::transaction(function () use ($admin, $code) {
                $lockedAdmin = Admin::query()->lockForUpdate()->findOrFail($admin->id);

                if (!$lockedAdmin->two_factor_secret) {
                    return false;
                }

                $counter = $this->totp->matchingCounter((string) $lockedAdmin->two_factor_secret, $code);
                if ($counter === null || $counter <= (int) ($lockedAdmin->two_factor_last_used_step ?? -1)) {
                    return false;
                }

                $lockedAdmin->forceFill(['two_factor_last_used_step' => $counter])->save();

                return true;
            });
        } catch (Throwable) {
            return false;
        }
    }

}
