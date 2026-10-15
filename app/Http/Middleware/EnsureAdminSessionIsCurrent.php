<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSessionIsCurrent
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if (!$admin) {
            return $next($request);
        }

        if ((int) $request->session()->get('admin_auth_version') !== (int) $admin->auth_version) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->routeIs('api.browser.admin.*')) {
                return response()->json([
                    'message' => 'Your admin session expired. Please sign in again.',
                    'code' => 'session_expired', 'redirect_url' => route('admin.login'),
                ], 401);
            }

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Your admin session expired. Please sign in again.',
            ]);
        }

        return $next($request);
    }
}
