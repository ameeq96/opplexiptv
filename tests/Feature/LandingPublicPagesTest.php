<?php

namespace Tests\Feature;

use App\Services\Api\PublicSiteDataService;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LandingPublicPagesTest extends TestCase
{
    private const BRANDS = ['trex', 'opplex', 'geo', '5g'];

    private const PAGES = [
        'about', 'contact', 'faqs', 'packages', 'reseller-panel', 'iptv-applications',
        'setup', 'movies', 'shop', 'activate', 'activate-info', 'blogs',
        'terms-of-service', 'privacy-policy', 'refund-policy',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
        $this->withoutMiddleware(ThrottleRequests::class);
        Http::preventStrayRequests();
    }

    public function test_each_brand_has_one_packages_page_and_three_original_articles_in_all_ten_languages(): void
    {
        $locales = config('app.locales');
        $this->assertSame(['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'], $locales);
        $englishTitles = [];
        $articleSlugs = [];
        foreach ($locales as $locale) {
            $titles = [];
            $introductions = [];
            $bodies = [];
            foreach (self::BRANDS as $brand) {
                $response = $this->getJson('/api/v1/site/brands/'.$brand.'/pages?locale='.$locale)
                    ->assertOk()->assertJsonPath('data.brand', $brand)
                    ->assertJsonPath('data.locale', $locale)
                    ->assertHeader('Content-Language', $locale)
                    ->assertJsonCount(18, 'data.pages');
                $this->assertNotEmpty($response->json('data.labels'));
                $pages = collect($response->json('data.pages'));
                $this->assertEqualsCanonicalizing(self::PAGES, $pages->pluck('slug')->reject(fn ($slug) => str_starts_with($slug, 'blogs/'))->values()->all());
                $this->assertSame(1, $pages->where('slug', 'packages')->count());
                $articles = $pages->pluck('slug')->filter(fn ($slug) => str_starts_with($slug, 'blogs/'))->values()->all();
                $this->assertCount(3, $articles);
                if ($locale === 'en') {
                    $articleSlugs[$brand] = $articles;
                } else {
                    $this->assertSame($articleSlugs[$brand], $articles, $brand.':'.$locale);
                }
                foreach ($pages as $page) {
                    $key = $brand.':'.$page['slug'];
                    $this->assertEqualsCanonicalizing(['slug', 'title', 'description', 'intro', 'kind', 'sections'], array_keys($page));
                    $this->assertNotEmpty($page['description'], $key);
                    $this->assertNotEmpty($page['sections'], $key);
                    $titles[] = $page['title'];
                    $introductions[] = $page['intro'];
                    $bodies[] = implode("\n", array_column($page['sections'], 'body'));
                    if ($locale === 'en') {
                        $englishTitles[$key] = $page['title'];
                    } else {
                        $this->assertNotSame($englishTitles[$key], $page['title'], $key.' must have a localized title');
                    }
                    foreach ($page['sections'] as $section) {
                        $this->assertSame(['heading', 'body'], array_keys($section));
                        $this->assertNotEmpty(trim($section['heading']));
                        $this->assertNotEmpty(trim($section['body']));
                    }
                }
            }
            $this->assertCount(72, array_unique($titles), $locale.' page titles must be brand-specific');
            $this->assertCount(72, array_unique($introductions), $locale.' page introductions must be original');
            $this->assertCount(72, array_unique($bodies), $locale.' page bodies must be original');
        }
        $this->assertCount(12, array_unique(array_merge(...array_values($articleSlugs))));
    }

    public function test_detail_matches_the_catalog_including_nested_article_slugs(): void
    {
        foreach (self::BRANDS as $brand) {
            $catalog = $this->getJson('/api/v1/site/brands/'.$brand.'/pages?locale=it')->assertOk()->json('data');
            foreach ($catalog['pages'] as $page) {
                $this->getJson('/api/v1/site/brands/'.$brand.'/pages/'.$page['slug'].'?locale=it')
                    ->assertOk()->assertExactJson(['data' => [
                        'brand' => $brand, 'locale' => 'it', 'labels' => $catalog['labels'], 'page' => $page,
                    ]]);
            }
        }
    }

    public function test_unknown_brands_pages_and_duplicate_pricing_routes_are_not_exposed(): void
    {
        $this->getJson('/api/v1/site/brands/unknown/pages')->assertNotFound();
        $this->getJson('/api/v1/site/brands/unknown/pages/about')->assertNotFound();
        foreach (['missing', 'pricing', 'iptv-subscription-service', 'admin', 'checkout', 'account', 'blogs/missing'] as $page) {
            $this->getJson('/api/v1/site/brands/trex/pages/'.$page)->assertNotFound();
        }
    }

    public function test_locales_and_paths_are_validated_without_reading_arbitrary_files(): void
    {
        foreach (['not-supported', '../en', 'en/../../app', 'en.json'] as $locale) {
            $this->getJson('/api/v1/site/brands/trex/pages?locale='.rawurlencode($locale))
                ->assertUnprocessable()->assertJsonValidationErrors('locale');
        }
        $this->getJson('/api/v1/site/brands/trex/pages?locale%5B%5D=en')
            ->assertUnprocessable()->assertJsonValidationErrors('locale');
        foreach (['..%2F..%2F.env', '%2E%2E', 'blogs%2F..%2Fabout', 'about.json', 'blogs/nested/article'] as $page) {
            $this->getJson('/api/v1/site/brands/trex/pages/'.$page)->assertNotFound();
        }
    }

    public function test_missing_locale_source_does_not_fall_back_to_english(): void
    {
        config()->set('app.locales', [...config('app.locales'), 'zz']);
        app()->setLocale('zz');
        try {
            app(PublicSiteDataService::class)->landingPages('trex');
            $this->fail('A missing localized source must not return English content.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    public function test_public_catalog_is_read_only_and_does_not_query_customer_or_payment_data(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        config()->set('services.opplexify.shared_secret', 'private-payment-secret');
        $response = $this->getJson('/api/v1/site/brands/trex/pages?locale=en')->assertOk()
            ->assertJsonMissingPath('data.customer')->assertJsonMissingPath('data.orders')
            ->assertJsonMissingPath('data.packages')->assertJsonMissingPath('data.session');
        $this->assertStringNotContainsString('private-payment-secret', $response->getContent());
        $this->assertSame([], $queries);
        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            foreach (['pages', 'pages/about'] as $path) {
                $this->json($method, '/api/v1/site/brands/trex/'.$path, ['title' => 'Not permitted'])
                    ->assertStatus(405);
            }
        }
    }
}
