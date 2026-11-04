<?php

namespace Tests\Feature;

use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\LaravelLocalization as Localization;
use Tests\TestCase;

class MainPublicSlugMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const ORIGINAL_ROUTES = [
        '' => 'home', 'about' => 'about', 'activate' => 'activate',
        'activate-info' => 'activate-info', 'blogs' => 'blogs.index', 'contact' => 'contact',
        'faqs' => 'faqs', 'iptv-applications' => 'iptv-applications', 'movies' => 'movies',
        'packages' => 'packages', 'pricing' => 'pricing', 'privacy-policy' => 'privacy-policy',
        'refund-policy' => 'refund-policy', 'reseller-panel' => 'reseller-panel', 'shop' => 'shop',
        'terms-of-service' => 'terms-of-service',
    ];

    private const CHANGES = [
        'en' => [
            'about' => 'about-us', 'activate' => 'activation', 'activate-info' => 'activation-guide',
            'blogs' => 'articles', 'contact' => 'contact-us', 'faqs' => 'help-center',
            'iptv-applications' => 'iptv-player-apps', 'movies' => 'entertainment-catalogue',
            'packages' => 'subscription-plans', 'pricing' => 'subscription-pricing',
            'privacy-policy' => 'privacy-notice', 'refund-policy' => 'refund-terms',
            'reseller-panel' => 'reseller-plans', 'shop' => 'store',
            'terms-of-service' => 'terms-and-conditions',
        ],
        'ar' => [
            '' => 'home', 'blogs' => 'articles', 'faqs' => 'help-center',
            'iptv-applications' => 'iptv-player-apps', 'movies' => 'entertainment-catalogue',
        ],
        'es' => [
            '' => 'home', 'about' => 'about-us', 'contact' => 'contact-us', 'faqs' => 'help-center',
            'iptv-applications' => 'iptv-player-apps', 'packages' => 'subscription-plans',
            'pricing' => 'subscription-pricing', 'refund-policy' => 'refund-terms',
            'reseller-panel' => 'reseller-plans', 'shop' => 'store',
        ],
        'fr' => [
            '' => 'home', 'activate-info' => 'activation-guide',
            'iptv-applications' => 'iptv-player-apps', 'packages' => 'subscription-plans',
        ],
        'hi' => [
            '' => 'home', 'faqs' => 'help-center', 'movies' => 'entertainment-catalogue',
            'packages' => 'subscription-plans',
        ],
        'it' => [
            '' => 'home', 'about' => 'about-us', 'faqs' => 'help-center',
            'movies' => 'entertainment-catalogue', 'packages' => 'subscription-plans',
            'pricing' => 'subscription-pricing', 'privacy-policy' => 'privacy-notice',
            'reseller-panel' => 'reseller-plans', 'shop' => 'store',
        ],
        'nl' => [
            '' => 'home', 'about' => 'about-us', 'activate-info' => 'activation-guide',
            'faqs' => 'help-center', 'movies' => 'entertainment-catalogue',
        ],
        'pt' => [
            '' => 'home', 'about' => 'about-us', 'blogs' => 'articles',
            'iptv-applications' => 'iptv-player-apps', 'movies' => 'entertainment-catalogue',
            'reseller-panel' => 'reseller-plans', 'shop' => 'store',
        ],
        'ru' => [
            '' => 'home', 'activate' => 'activation', 'activate-info' => 'activation-guide',
            'blogs' => 'articles', 'faqs' => 'help-center', 'iptv-applications' => 'iptv-player-apps',
            'movies' => 'entertainment-catalogue', 'packages' => 'subscription-plans',
            'pricing' => 'subscription-pricing', 'refund-policy' => 'refund-terms',
            'reseller-panel' => 'reseller-plans',
        ],
        'ur' => [
            '' => 'home', 'about' => 'about-us', 'faqs' => 'help-center',
            'movies' => 'entertainment-catalogue', 'pricing' => 'subscription-pricing',
        ],
    ];

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Bus::fake();
        Mail::fake();
        Notification::fake();
        Cache::flush();
        $this->mock(TmdbService::class, fn ($mock) => $mock->shouldReceive('configured')->andReturn(false));
    }

    public function test_exactly_the_75_selected_urls_redirect_once_and_preserve_get_and_head_queries(): void
    {
        $count = 0;
        foreach (self::CHANGES as $locale => $changes) {
            $this->loadLocaleRoutes($locale);
            foreach ($changes as $oldSlug => $newSlug) {
                $oldPath = $this->localizedPath($locale, $oldSlug);
                $newPath = $this->localizedPath($locale, $newSlug);
                $target = $this->app['router']->getRoutes()->match(Request::create($newPath));
                $this->assertSame(self::ORIGINAL_ROUTES[$oldSlug], $target->getName(), $newPath);

                foreach (['GET', 'HEAD'] as $method) {
                    $request = Request::create($oldPath.'?search=box%20set&page=2&category=17', $method);
                    $this->app->instance('request', $request);
                    $route = $this->app['router']->getRoutes()->match($request);
                    $this->assertNull($route->getName(), $oldPath);
                    $response = $route->run();
                    $this->assertSame(301, $response->getStatusCode(), $oldPath);
                    $this->assertSame($newPath, $this->urlPath($response->getTargetUrl()), $oldPath);
                    parse_str((string) parse_url($response->getTargetUrl(), PHP_URL_QUERY), $query);
                    $this->assertSame(['category' => '17', 'page' => '2', 'search' => 'box set'], $query, $oldPath);
                }
                $count++;
            }
        }
        $this->assertSame(75, $count);
    }

    public function test_the_85_unlisted_locale_variants_and_private_and_article_routes_keep_their_identity(): void
    {
        $count = 0;
        foreach (self::CHANGES as $locale => $changes) {
            $this->loadLocaleRoutes($locale);
            foreach (self::ORIGINAL_ROUTES as $slug => $name) {
                if (array_key_exists($slug, $changes)) {
                    continue;
                }
                $path = $this->localizedPath($locale, $slug);
                $route = $this->app['router']->getRoutes()->match(Request::create($path));
                $this->assertSame($name, $route->getName(), $path);
                $this->assertSame($path, $this->urlPath(route($name)), $path);
                $count++;
            }

            $article = $this->app['router']->getRoutes()->match(Request::create($this->localizedPath($locale, 'blogs/unchanged-article')));
            $this->assertSame('blogs.show', $article->getName());
            $this->assertSame('unchanged-article', $article->parameter('slug'));
            $this->assertSame('App\\Http\\Controllers\\BlogController@show', $article->getActionName());

            $purchase = $this->app['router']->getRoutes()->match(Request::create($this->localizedPath($locale, 'purchase/17')));
            $this->assertSame('packages.purchase', $purchase->getName());
            $this->assertContains('signed', $purchase->gatherMiddleware());
            $this->assertContains('throttle:30,1', $purchase->gatherMiddleware());
            $checkout = $this->app['router']->getRoutes()->match(Request::create($this->localizedPath($locale, 'checkout')));
            $this->assertSame('checkout', $checkout->getName());
            $this->assertSame('App\\Http\\Controllers\\HomeController@checkoutStep1', $checkout->getActionName());
            $account = $this->app['router']->getRoutes()->match(Request::create('/account/login'));
            $this->assertSame('customer.login', $account->getName());
        }
        $this->assertSame(85, $count);
    }

    public function test_canonical_and_language_alternates_resolve_the_same_page_across_all_locales(): void
    {
        foreach (self::CHANGES as $locale => $changes) {
            $this->loadLocaleRoutes($locale);
            foreach (self::ORIGINAL_ROUTES as $originalSlug => $name) {
                $currentPath = $this->localizedPath($locale, $changes[$originalSlug] ?? $originalSlug);
                $current = url($currentPath);
                $canonical = $locale === 'en'
                    ? LaravelLocalization::getNonLocalizedURL($current)
                    : LaravelLocalization::getLocalizedURL($locale, $current, [], true);
                $this->assertSame($currentPath, $this->urlPath($canonical), $name.' '.$locale);

                $english = self::CHANGES['en'][$originalSlug] ?? $originalSlug;
                $this->assertSame($this->localizedPath('en', $english), $this->urlPath(LaravelLocalization::getNonLocalizedURL($current)));
                foreach (self::CHANGES as $targetLocale => $targetChanges) {
                    $targetSlug = $targetChanges[$originalSlug] ?? $originalSlug;
                    $href = LaravelLocalization::getLocalizedURL($targetLocale, $current, [], true);
                    $expected = $targetLocale === 'en' ? '/en'.($targetSlug !== '' ? '/'.$targetSlug : '') : $this->localizedPath($targetLocale, $targetSlug);
                    $this->assertSame($expected, $this->urlPath($href), $name.' '.$locale.' -> '.$targetLocale);
                }
            }
        }
    }

    public function test_legacy_redirects_survive_route_cache_serialization_without_writing_cache_files(): void
    {
        foreach (self::CHANGES as $locale => $changes) {
            $this->loadLocaleRoutes($locale);
            $oldSlug = array_key_first($changes);
            $newPath = $this->localizedPath($locale, $changes[$oldSlug]);
            $request = Request::create($this->localizedPath($locale, $oldSlug).'?page=3&search=player');
            $this->app->instance('request', $request);
            $route = clone $this->app['router']->getRoutes()->match($request);
            $route->prepareForSerialization();
            $restored = unserialize(serialize($route));
            $restored->setContainer($this->app);
            $response = $restored->run();
            $this->assertSame(301, $response->getStatusCode());
            $this->assertSame($newPath, $this->urlPath($response->getTargetUrl()));
            parse_str((string) parse_url($response->getTargetUrl(), PHP_URL_QUERY), $query);
            $this->assertSame(['page' => '3', 'search' => 'player'], $query);
        }
    }

    public function test_rendered_about_pages_keep_metadata_and_emit_correct_canonical_hreflang_and_menu_links(): void
    {
        foreach (['en', 'it'] as $locale) {
            $path = $this->localizedPath($locale, 'about-us');
            $this->loadLocaleRoutes($locale, $path);
            $response = $this->get($path)->assertOk();
            $html = $response->getContent();
            $this->assertStringContainsString('<title>'.e(trans('meta.about.title')).'</title>', $html);
            $this->assertStringContainsString('name="description" content="'.e(trans('meta.about.description')).'"', $html);
            $document = new \DOMDocument();
            @$document->loadHTML($html);
            $xpath = new \DOMXPath($document);
            $canonical = $xpath->query('//link[@rel="canonical"]');
            $this->assertSame(1, $canonical->length);
            $this->assertSame($path, $this->urlPath($canonical->item(0)->getAttribute('href')));
            foreach (self::CHANGES as $targetLocale => $changes) {
                $expected = $this->localizedPath($targetLocale, $changes['about'] ?? 'about');
                $alternate = $xpath->query('//link[@rel="alternate" and @hreflang="'.$targetLocale.'"]');
                $this->assertSame(1, $alternate->length, $targetLocale);
                $this->assertSame($expected, $this->urlPath($alternate->item(0)->getAttribute('href')), $targetLocale);
                $switchPath = $targetLocale === 'en' ? '/en/about-us' : $expected;
                $this->assertStringContainsString('href="'.url($switchPath).'"', $html, $targetLocale);
            }
            $xDefault = $xpath->query('//link[@rel="alternate" and @hreflang="x-default"]');
            $this->assertSame('/about-us', $this->urlPath($xDefault->item(0)->getAttribute('href')));
        }
    }

    public function test_sitemap_has_new_canonicals_and_only_the_four_requested_activation_guide_entries(): void
    {
        $this->loadLocaleRoutes('en');
        $xml = '';
        File::shouldReceive('put')->once()->with(public_path('sitemap.xml'), \Mockery::on(function ($value) use (&$xml) {
            $xml = $value;
            return is_string($value);
        }))->andReturn(1);
        $this->artisan('sitemap:generate')->assertSuccessful();
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($xml));
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
        $paths = [];
        foreach ($xpath->query('/sm:urlset/sm:url/sm:loc') as $node) {
            $paths[] = $this->urlPath($node->textContent);
        }
        $this->assertCount(164, $paths);
        $this->assertCount(164, array_unique($paths));
        foreach (self::CHANGES as $locale => $changes) {
            foreach ($changes as $oldSlug => $newSlug) {
                $this->assertContains($this->localizedPath($locale, $newSlug), $paths);
                $this->assertNotContains($this->localizedPath($locale, $oldSlug), $paths);
            }
            if (in_array($locale, ['en', 'fr', 'nl', 'ru'], true)) {
                $this->assertContains($this->localizedPath($locale, 'activation-guide'), $paths);
            } else {
                $this->assertNotContains($this->localizedPath($locale, 'activate-info'), $paths);
            }
        }
        foreach ($xpath->query('/sm:urlset/sm:url') as $node) {
            $this->assertSame(11, $xpath->query('xhtml:link', $node)->length);
        }
    }

    private function loadLocaleRoutes(string $locale, ?string $path = null): void
    {
        config()->set('app.locale', 'en');
        $this->app['translator']->setLocale('en');
        $request = Request::create('http://localhost'.($path ?? $this->localizedPath($locale, '')));
        $this->app->instance('request', $request);
        $this->app['url']->setRequest($request);
        $this->app->forgetInstance(Localization::class);
        LaravelLocalization::clearResolvedInstance('laravellocalization');
        $this->app['router']->setRoutes(new RouteCollection());
        $this->app['router']->group([], base_path('routes/api.php'));
        require base_path('routes/web.php');
        $routes = $this->app['router']->getRoutes();
        $routes->refreshNameLookups();
        $routes->refreshActionLookups();
        $this->app['url']->setRoutes($routes);
    }

    private function localizedPath(string $locale, string $slug): string
    {
        return '/'.trim(($locale === 'en' ? '' : $locale.'/').$slug, '/');
    }

    private function urlPath(string $url): string
    {
        return '/'.trim((string) parse_url($url, PHP_URL_PATH), '/');
    }
}
