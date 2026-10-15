<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureCustomerAccount
{
    public function handle(Request $request, Closure $next, string $access = 'required')
    {
        $user = $request->user('web');
        $verified = $user && $user->email_verified_at
            && hash_equals(hash('sha256', User::normalizeEmail($user->email)), (string) $request->session()->get('customer_verified_email', ''));

        if ($access !== 'public' && ! $verified) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please sign in through your email to continue.'], 401)
                    ->header('Cache-Control', 'private, no-store');
            }
            if ($request->isMethod('get')) {
                $request->session()->put('customer_intended_path', $request->getRequestUri());
            }
            return redirect()->route('customer.login')->with('status', 'Please verify your email to open your account.');
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }
}
