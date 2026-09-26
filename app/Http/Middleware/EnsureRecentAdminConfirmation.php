<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecentAdminConfirmation
{
    private const VALID_FOR_SECONDS = 600;

    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');
        $confirmedAt = (int) $request->session()->get('admin_sensitive_confirmed_at');

        $isCurrent = $admin
            && (int) $request->session()->get('admin_sensitive_confirmed_id') === (int) $admin->id
            && (int) $request->session()->get('admin_sensitive_confirmed_version') === (int) $admin->auth_version
            && $confirmedAt >= time() - self::VALID_FOR_SECONDS;

        if (!$isCurrent) {
            $request->session()->forget([
                'admin_sensitive_confirmed_at',
                'admin_sensitive_confirmed_id',
                'admin_sensitive_confirmed_version',
            ]);

            return redirect()->guest(route('admin.security.confirm'));
        }

        return $next($request);
    }
}
