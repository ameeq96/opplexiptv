<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('admin')?->must_change_password) {
            if ($request->routeIs('api.browser.admin.*')) {
                return response()->json([
                    'message' => 'Change your password before continuing.',
                    'code' => 'password_change_required', 'redirect_url' => route('admin.password.change'),
                ], 403);
            }
            return redirect()->route('admin.password.change');
        }

        return $next($request);
    }
}
