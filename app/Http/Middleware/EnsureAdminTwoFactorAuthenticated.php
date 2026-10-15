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
        if (!$admin || app()->environment('local')) {
            return $next($request);
        }

        if (!$admin->two_factor_confirmed_at) {
            if ($request->routeIs('api.browser.admin.*')) {
                return response()->json([
                    'message' => 'Set up two-factor authentication before continuing.',
                    'code' => 'two_factor_setup_required', 'redirect_url' => route('admin.two-factor.settings'),
                ], 403);
            }
            return redirect()
                ->route('admin.two-factor.settings')
                ->with('warning', 'Set up two-factor authentication before continuing.');
        }

        if ((int) $request->session()->get('admin_two_factor_verified_id') !== (int) $admin->getAuthIdentifier()) {
            if ($request->routeIs('api.browser.admin.*')) {
                return response()->json([
                    'message' => 'Complete two-factor authentication before continuing.',
                    'code' => 'two_factor_required', 'redirect_url' => route('admin.two-factor.challenge'),
                ], 403);
            }
            return redirect()->guest(route('admin.two-factor.challenge'));
        }

        return $next($request);
    }
}
