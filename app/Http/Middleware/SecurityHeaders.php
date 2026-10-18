<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', $request->is('account', 'account/*', 'api/browser/account/*', 'api/browser/orders/*', 'api/browser/marketing/*', 'api/browser/event-promotion/*') || $request->routeIs('orders.status', 'orders.payment-proof', 'marketing.unsubscribe', 'event-promotions.activate', 'landing.resume.show', 'api.browser.landing.resume.confirm', 'api.landing.resume-links') ? 'no-referrer' : 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set(
            'Content-Security-Policy',
            "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'"
        );

        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
