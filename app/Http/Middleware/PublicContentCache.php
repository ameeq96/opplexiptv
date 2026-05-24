<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicContentCache
{
    /** @var array<int,string> */
    private array $cacheableRoutes = [
        'home',
        'about',
        'faqs',
        'activate',
        'activate-info',
        'pricing',
        'movies',
        'packages',
        'reseller-panel',
        'iptv-applications',
        'shop',
        'blogs.index',
        'blogs.show',
        'terms-of-service',
        'privacy-policy',
        'refund-policy',
    ];

    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->isCacheable($request, $response)) {
            return $response;
        }

        $response->headers->set(
            'Cache-Control',
            'public, max-age=60, s-maxage=600, stale-while-revalidate=86400'
        );
        $response->headers->remove('Pragma');
        $response->headers->remove('Expires');

        $this->removeCookie($response, (string) config('session.cookie'));
        $this->removeCookie($response, 'XSRF-TOKEN');

        return $response;
    }

    private function isCacheable(Request $request, Response $response): bool
    {
        if (!$request->isMethodCacheable() || $request->query->count() > 0) {
            return false;
        }

        if (!$response->isSuccessful() || $response->isRedirection()) {
            return false;
        }

        $sessionCookie = (string) config('session.cookie');
        if ($request->cookies->has($sessionCookie) || $request->cookies->has('XSRF-TOKEN')) {
            return false;
        }

        $routeName = optional($request->route())->getName();

        return is_string($routeName) && in_array($routeName, $this->cacheableRoutes, true);
    }

    private function removeCookie(Response $response, string $name): void
    {
        if ($name === '') {
            return;
        }

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                $response->headers->removeCookie($name, $cookie->getPath(), $cookie->getDomain());
            }
        }
    }
}
