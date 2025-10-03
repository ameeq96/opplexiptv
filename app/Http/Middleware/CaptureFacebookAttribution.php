<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;


class CaptureFacebookAttribution
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // _fbp (2 years)
        $fbp = $request->cookie('_fbp');
        if (!$fbp) {
            $fbp = sprintf('fb.1.%d.%d', time(), random_int(1000000000, 9999999999));
            cookie()->queue(new Cookie('_fbp', $fbp, 60*24*730, '/', null, false, false));
        }

        // _fbc from fbclid (90 days)
        $fbclid = $request->query('fbclid');
        $fbc = $request->cookie('_fbc');
        if ($fbclid && !$fbc) {
            $fbc = sprintf('fb.1.%d.%s', time(), $fbclid);
            cookie()->queue(new Cookie('_fbc', $fbc, 60*24*90, '/', null, false, false));
        }

        // session snapshot (last touch)
        session([
            'fb.fbp' => $fbp,
            'fb.fbc' => $fbc ?: session('fb.fbc'),
            'fb.last_touch_url' => $request->fullUrl(),
            'fb.last_referrer'  => $request->headers->get('referer'),
            'fb.ip'  => $request->ip(),
            'fb.ua'  => $request->userAgent(),
        ]);

        foreach (['utm_source','utm_medium','utm_campaign','utm_term','utm_content'] as $utm) {
            if ($request->has($utm)) session(["fb.$utm" => $request->get($utm)]);
        }

        return $next($request);
    }
}
