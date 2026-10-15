<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class DataApiAccess
{
    public const ABILITIES = ['customers:read', 'orders:read', 'support:read'];

    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $bearer = $request->bearerToken();
        $token = is_string($bearer) && $bearer !== '' ? PersonalAccessToken::findToken($bearer) : null;
        $admin = $token?->tokenable;
        $abilities = $token?->abilities ?? [];
        $expiration = (int) config('sanctum.expiration');

        if (!$token || !$admin instanceof Admin || !$admin->isOwner() || $admin->must_change_password
            || !$token->created_at || !$token->expires_at || $token->expires_at->isPast()
            || $token->expires_at->gt($token->created_at->copy()->addDays(30))
            || ($expiration > 0 && $token->created_at->lte(now()->subMinutes($expiration)))
            || !in_array('data-api', $abilities, true)
            || !in_array('auth-version:'.(int) $admin->auth_version, $abilities, true)) {
            return response()->json(['message' => 'A valid, unexpired data API token is required.'], 401)
                ->header('Cache-Control', 'private, no-store');
        }

        if (!in_array($ability, self::ABILITIES, true) || !in_array($ability, $abilities, true)) {
            return response()->json(['message' => 'This token does not have the required permission.'], 403)
                ->header('Cache-Control', 'private, no-store');
        }

        $admin->withAccessToken($token);
        $request->setUserResolver(static fn ($guard = null) => $admin);

        return $next($request);
    }
}
