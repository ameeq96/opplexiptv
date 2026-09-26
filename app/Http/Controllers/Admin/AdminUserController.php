<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.admin-users.index', [
            'admins' => Admin::query()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.admin-users.create', [
            'adminUser' => new Admin,
            'roles' => Admin::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['password'] = Hash::make($data['password']);
        $data['must_change_password'] = true;
        $data['password_changed_at'] = null;

        Admin::create($data);

        return redirect()->route('admin.admin-users.index')->with('success', 'Admin user created.');
    }

    public function edit(Admin $admin_user)
    {
        return view('admin.admin-users.edit', [
            'adminUser' => $admin_user,
            'roles' => Admin::ROLES,
        ]);
    }

    public function update(Request $request, Admin $admin_user)
    {
        $data = $this->validateData($request, $admin_user);

        if ($admin_user->is(auth('admin')->user()) && $data['role'] !== Admin::ROLE_OWNER) {
            throw ValidationException::withMessages([
                'role' => 'You cannot remove your own Owner role.',
            ]);
        }

        if ($admin_user->is(auth('admin')->user()) && !empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => 'Use Account Security to change your own password.',
            ]);
        }

        DB::transaction(function () use ($admin_user, $data) {
            $ownerIds = $this->lockOwnerIds();
            $lockedAdmin = Admin::query()->lockForUpdate()->findOrFail($admin_user->id);

            if ($lockedAdmin->isOwner() && $data['role'] !== Admin::ROLE_OWNER) {
                $this->ensureAnotherOwnerExists($lockedAdmin, $ownerIds);
            }

            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
                $data['must_change_password'] = true;
                $data['password_changed_at'] = null;
                $data['remember_token'] = Str::random(60);
                $data['auth_version'] = (int) $lockedAdmin->auth_version + 1;
            } else {
                unset($data['password']);
            }

            $lockedAdmin->update($data);
        });

        return redirect()->route('admin.admin-users.index')->with('success', 'Admin user updated.');
    }

    public function destroy(Admin $admin_user)
    {
        if ($admin_user->is(auth('admin')->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        DB::transaction(function () use ($admin_user) {
            $ownerIds = $this->lockOwnerIds();
            $lockedAdmin = Admin::query()->lockForUpdate()->findOrFail($admin_user->id);

            if ($lockedAdmin->isOwner()) {
                $this->ensureAnotherOwnerExists($lockedAdmin, $ownerIds);
            }

            $lockedAdmin->delete();
        });

        return redirect()->route('admin.admin-users.index')->with('success', 'Admin user deleted.');
    }

    public function resetTwoFactor(Request $request, Admin $admin_user)
    {
        if ($admin_user->is(auth('admin')->user())) {
            throw ValidationException::withMessages([
                'temporary_password' => 'Use your own Two-Factor Authentication page to reset your account.',
            ]);
        }

        $data = $request->validate([
            'temporary_password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $admin_user->forceFill([
            'password' => Hash::make($data['temporary_password']),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
            'must_change_password' => true,
            'password_changed_at' => null,
            'remember_token' => Str::random(60),
            'auth_version' => (int) $admin_user->auth_version + 1,
        ])->save();

        return back()->with('success', 'Two-factor authentication reset. This admin must set it up again.');
    }

    private function validateData(Request $request, ?Admin $admin = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin?->id),
            ],
            'role' => ['required', Rule::in(Admin::ROLES)],
            'password' => [
                $admin ? 'nullable' : 'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);
    }

    private function lockOwnerIds(): Collection
    {
        return Admin::query()
            ->where('role', Admin::ROLE_OWNER)
            ->orderBy('id')
            ->lockForUpdate()
            ->pluck('id');
    }

    private function ensureAnotherOwnerExists(Admin $admin, Collection $ownerIds): void
    {
        $anotherOwnerExists = $ownerIds->contains(fn ($id) => (int) $id !== (int) $admin->id);

        if (! $anotherOwnerExists) {
            throw ValidationException::withMessages([
                'role' => 'At least one Owner account must remain.',
            ]);
        }
    }
}
