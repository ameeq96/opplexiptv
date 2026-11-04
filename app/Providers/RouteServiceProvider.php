<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;

class RouteServiceProvider extends ServiceProvider
{
    use LoadsTranslatedCachedRoutes;

    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $publicSlugs = [
            'home' => '',
            'about' => 'about',
            'activate' => 'activate',
            'activate-info' => 'activate-info',
            'blogs.index' => 'blogs',
            'contact' => 'contact',
            'faqs' => 'faqs',
            'iptv-applications' => 'iptv-applications',
            'movies' => 'movies',
            'packages' => 'packages',
            'pricing' => 'pricing',
            'privacy-policy' => 'privacy-policy',
            'refund-policy' => 'refund-policy',
            'reseller-panel' => 'reseller-panel',
            'shop' => 'shop',
            'terms-of-service' => 'terms-of-service',
        ];
        $changedSlugs = [
            'home' => ['home', ['ar', 'es', 'fr', 'hi', 'it', 'nl', 'pt', 'ru', 'ur']],
            'about' => ['about-us', ['en', 'es', 'it', 'nl', 'pt', 'ur']],
            'activate' => ['activation', ['en', 'ru']],
            'activate-info' => ['activation-guide', ['en', 'fr', 'nl', 'ru']],
            'blogs.index' => ['articles', ['en', 'ar', 'pt', 'ru']],
            'contact' => ['contact-us', ['en', 'es']],
            'faqs' => ['help-center', ['en', 'ar', 'es', 'hi', 'it', 'nl', 'ru', 'ur']],
            'iptv-applications' => ['iptv-player-apps', ['en', 'ar', 'es', 'fr', 'pt', 'ru']],
            'movies' => ['entertainment-catalogue', ['en', 'ar', 'hi', 'it', 'nl', 'pt', 'ru', 'ur']],
            'packages' => ['subscription-plans', ['en', 'es', 'fr', 'hi', 'it', 'ru']],
            'pricing' => ['subscription-pricing', ['en', 'es', 'it', 'ru', 'ur']],
            'privacy-policy' => ['privacy-notice', ['en', 'it']],
            'refund-policy' => ['refund-terms', ['en', 'es', 'ru']],
            'reseller-panel' => ['reseller-plans', ['en', 'es', 'it', 'pt', 'ru']],
            'shop' => ['store', ['en', 'es', 'it', 'pt']],
            'terms-of-service' => ['terms-and-conditions', ['en']],
        ];

        foreach (array_keys(config('laravellocalization.supportedLocales', [])) as $locale) {
            $lines = [];
            foreach ($publicSlugs as $name => $slug) {
                [$replacement, $locales] = $changedSlugs[$name];
                $lines['main_public_route_slugs.'.$name] = in_array($locale, $locales, true) ? $replacement : $slug;
            }
            app('translator')->addLines($lines, $locale);
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::group([], base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
