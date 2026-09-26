<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable; // ← important

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_SALES = 'sales';

    public const ROLE_SUPPORT = 'support';

    public const ROLE_CONTENT = 'content';

    public const ROLES = [
        self::ROLE_OWNER,
        self::ROLE_SALES,
        self::ROLE_SUPPORT,
        self::ROLE_CONTENT,
    ];

    protected $fillable = [
        'name', 'email', 'password', 'role', 'must_change_password', 'password_changed_at', 'remember_token',
        'auth_version',
    ];

    protected $hidden = [
        'password', 'remember_token', 'auth_version', 'two_factor_secret', 'two_factor_recovery_codes',
        'two_factor_last_used_step',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'password_changed_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_last_used_step' => 'integer',
        'auth_version' => 'integer',
    ];

    public function hasRole(string ...$roles): bool
    {
        return in_array((string) $this->role, $roles, true);
    }

    public function isOwner(): bool
    {
        return $this->hasRole(self::ROLE_OWNER);
    }
}
