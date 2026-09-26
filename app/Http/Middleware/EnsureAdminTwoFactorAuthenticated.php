<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTwoFactorAuthenticated
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');
        if (!$admin) {
            return $next($request);
        }

        if (!$admin->two_factor_confirmed_at) {
            return redirect()
                ->route('admin.two-factor.settings')
                ->with('warning', 'Set up two-factor authentication before continuing.');
        }

        if ((int) $request->session()->get('admin_two_factor_verified_id') !== (int) $admin->getAuthIdentifier()) {
            return redirect()->guest(route('admin.two-factor.challenge'));
        }

        return $next($request);
    }
}
