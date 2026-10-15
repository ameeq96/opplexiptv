<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

class ApiJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        if ($request->routeIs('api.movies.trailer', 'api.trending', 'api.packages.provider-plans', 'api.site.*', 'api.browser.site.context', 'api.browser.contact.*', 'api.browser.subscribe', 'api.browser.checkout*')) {
            $data = validator($request->query(), [
                'locale' => ['nullable', 'string', Rule::in(array_keys(config('laravellocalization.supportedLocales', [])))],
            ])->validate();
            if (!empty($data['locale'])) {
                LaravelLocalization::setLocale($data['locale']);
            }
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('Content-Language', app()->getLocale());

        return $response;
    }
}
