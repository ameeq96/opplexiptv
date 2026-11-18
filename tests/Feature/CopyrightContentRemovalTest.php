<?php

namespace Tests\Feature;

use App\Jobs\GenerateWebpImage;
use App\Models\Blog;
use App\Models\BlogGenerationRun;
use App\Models\ChannelLogo;
use App\Models\Digital\DigitalProduct;
use App\Models\Package;
use App\Models\ShopProduct;
use App\Services\UnifiedProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CopyrightContentRemovalTest extends TestCase
{
    use RefreshDatabase;

    private const RETIRED_OFFERS = [
        'netflix', 'prime-video', 'hbo-max-premium', 'crunchyroll-premium',
        'youtube-premium', 'hulu', 'paramount-plus', 'curiosity-stream',
        'jiocinema-with-vpn', 'hotstar', 'espn-plus', 'zee5-premium', 'sling-tv',
    ];

    private const OLD_COVERS = [
        'blogs/0wmReYEoRa3kQ7Ig6alMQshVPLovXBTt8C6mA0TG.jpg',
        'blogs/izN5fgSPAT26N74Z6BrxyOZWxC5wmPq2BzfDMPR4.jpg',
        'blogs/v5Fg96cMN7YWRz9ZAVlXhnl32B6d1k1hmSm9J9Rv.jpg',
    ];

    private array $routingLocaleEnvironment = [];

    protected function refreshApplication(): void
    {
        $this->routingLocaleEnvironment = [
            'process' => getenv('ROUTING_LOCALE'),
            'env' => $_ENV['ROUTING_LOCALE'] ?? null,
            'server' => $_SERVER['ROUTING_LOCALE'] ?? null,
        ];
        $locale = $this->providedData()[0] ?? 'en';
        $routingLocale = $locale === 'en' ? '' : $locale;
        putenv('ROUTING_LOCALE='.$routingLocale);
        $_ENV['ROUTING_LOCALE'] = $routingLocale;
        $_SERVER['ROUTING_LOCALE'] = $routingLocale;
        parent::refreshApplication();
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            $previous = $this->routingLocaleEnvironment;
            if (isset($previous['env'])) {
                $_ENV['ROUTING_LOCALE'] = $previous['env'];
            } else {
                unset($_ENV['ROUTING_LOCALE']);
            }
            if (isset($previous['server'])) {
                $_SERVER['ROUTING_LOCALE'] = $previous['server'];
            } else {
                unset($_SERVER['ROUTING_LOCALE']);
            }
            putenv(($previous['process'] ?? false) === false ? 'ROUTING_LOCALE' : 'ROUTING_LOCALE='.$previous['process']);
        }
    }

    public static function locales(): array
    {
        $locales = ['en', 'es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];

        return array_combine($locales, array_map(static fn (string $locale): array => [$locale], $locales));
    }

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
        Cache::flush();
        Bus::fake();
        Mail::fake();
        Notification::fake();
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['results' => []])]);
        config()->set('services.tmdb.api_key', 'isolated-copyright-regression-key');
    }

    #[DataProvider('locales')]
    public function test_all_localized_public_pages_omit_retired_art_and_keep_useful_content_without_tmdb_requests(string $locale): void
    {
        $this->createOffers();
        ChannelLogo::create(['image' => 'images/resource/4.webp', 'is_active' => true, 'sort_order' => 1]);

        app()->setLocale($locale);
        $cached = app(UnifiedProductService::class)->frontendProducts();
        $this->assertContains('netflix', $cached->pluck('slug')->all());
        Cache::put('ui:tmdb:v2:trending:all:day:'.$locale.':p1', [[
            'id' => 42, 'media_type' => 'movie', 'title' => 'Retired cached movie fixture',
            'overview' => 'An old cached catalogue entry.', 'backdrop_path' => '/retired-backdrop.jpg',
            'poster_path' => '/retired-poster.jpg',
        ]], now()->addMinutes(10));

        foreach (['home', 'movies', 'faqs', 'reseller-panel', 'about', 'shop'] as $page) {
            $api = $this->getJson('/api/v1/site/pages/'.$page.'?locale='.$locale)
                ->assertOk()->assertJsonPath('data.locale', $locale)->assertJsonPath('data.logos', []);
            $this->assertRetiredArtAbsent($api->getContent());
            $this->assertNotEmpty($api->json('data.content'));
            $path = parse_url($api->json('data.meta.canonical'), PHP_URL_PATH);
            $html = $this->get($path)->assertOk();
            $this->assertRetiredArtAbsent($html->getContent());
            $this->assertStringContainsString('<h1', $html->getContent(), $locale.':'.$page);
            $this->assertStringNotContainsString('Retired cached movie fixture', $html->getContent());
            if (in_array($page, ['home', 'movies'], true)) {
                $api->assertJsonPath('data.movies', [])->assertJsonPath('data.displayMovies', []);
            }
            if (in_array($page, ['home', 'shop'], true)) {
                $this->assertStringContainsString('/images/shop/B08CRV62C4.webp', $html->getContent());
            }
        }

        Http::assertNothingSent();
        Bus::assertNotDispatched(GenerateWebpImage::class);
    }

    public function test_active_streaming_offers_cannot_leak_from_warm_shared_lists_or_direct_product_urls(): void
    {
        $offers = $this->createOffers();

        foreach (config('app.locales') as $locale) {
            app()->setLocale($locale);
            $shared = app(UnifiedProductService::class)->frontendProducts();
            $this->assertCount(14, $shared->where('type', 'digital'));
            $response = $this->getJson('/api/v1/site/products?locale='.$locale.'&type=all&per_page=100')->assertOk();
            $this->assertSame(['nordvpn'], collect($response->json('data'))->where('type', 'digital')->pluck('slug')->values()->all());
            $affiliate = collect($response->json('data'))->firstWhere('type', 'affiliate');
            $this->assertSame('Text-only device fixture', $affiliate['name']);
            $this->assertSame('https://example.test/affiliate-device', $affiliate['url']);
            $this->assertSame(asset('images/shop/B08CRV62C4.webp'), $affiliate['image']);
            $this->assertSame($affiliate['image'], $affiliate['image_sources']['src']);
        }

        app()->setLocale('en');
        foreach ($offers['retired'] as $product) {
            $this->getJson('/api/v1/site/products/digital/'.$product->id.'?locale=en')->assertNotFound();
            $this->get('/product-share/digital/'.$product->id)->assertNotFound();
            $this->assertTrue($product->fresh()->is_active);
        }

        $this->getJson('/api/v1/site/products/digital/'.$offers['vpn']->id.'?locale=en')
            ->assertOk()->assertJsonPath('data.productName', 'NordVPN fixture');
        $this->get('/product-share/digital/'.$offers['vpn']->id)->assertOk()->assertSeeText('NordVPN fixture');
        $share = $this->getJson('/api/v1/site/products/affiliate/'.$offers['affiliate']->id.'?locale=en')
            ->assertOk()->assertJsonPath('data.productImage', asset('images/shop/B08CRV62C4.webp'))
            ->assertJsonPath('data.productImageSources.src', asset('images/shop/B08CRV62C4.webp'));
        $this->assertStringContainsString('Text-only device fixture', $share->json('data.productName'));
        $this->get('/product-share/affiliate/'.$offers['affiliate']->id)->assertOk()
            ->assertSeeText('Text-only device fixture')->assertSee('images/shop/B08CRV62C4.webp', false);
        $home = $this->get('/iptv-home')->assertOk();
        $home->assertSee('https://example.test/affiliate-device', false)->assertSeeText('Text-only device fixture');
        $plans = $this->getJson('/api/v1/site/brands/iptvsubscription/plans?locale=en')->assertOk();
        $this->assertContains($offers['package']->id, array_column($plans->json('data.packages'), 'id'));
        $this->assertSame('17.43', number_format($offers['package']->fresh()->price_amount, 2, '.', ''));
        $this->assertSame(14, DigitalProduct::query()->where('is_active', true)->count());
        Http::assertNothingSent();
    }

    #[DataProvider('locales')]
    public function test_legacy_blog_covers_are_suppressed_in_html_json_og_and_nested_schema_without_changing_stored_posts(string $locale): void
    {
        $legacy = [];
        foreach (self::OLD_COVERS as $index => $cover) {
            $legacy[] = $this->createPost('main', 'retired-cover-'.$index, $cover, $index === 0);
        }
        $owned = $this->createPost('main', 'owned-cover-control', 'blogs/owned-copyright-test-cover.jpg');
        $other = $this->createPost('geo', 'geo-cover-control', self::OLD_COVERS[0], true);
        $revision = hash('sha256', 'geo-copyright-scope-control');
        $landingImage = 'https://geoiptv.store/share-card.png';
        $run = BlogGenerationRun::create([
            'site_key' => 'geo', 'blog_id' => $other->id, 'topic' => 'Separate brand cover control',
            'status' => 'published', 'revision' => $revision, 'published_at' => now()->subHour(),
            'payload' => ['post' => ['id' => $other->id, 'slug' => 'geo-cover-control', 'revision' => $revision,
                'published_at' => now()->subHour()->toIso8601String(), 'image' => $landingImage,
                'translations' => ['en' => ['title' => 'Separate brand cover control']]]],
        ]);
        $originalRunPayload = $run->payload;

        $index = $this->getJson('/api/v1/site/blogs?locale='.$locale)->assertOk();
        $this->assertRetiredCoversAbsent($index->getContent());
        $this->assertStringNotContainsString('geo-cover-control', $index->getContent());
        $listing = $this->get(parse_url($index->json('data.meta.canonical'), PHP_URL_PATH))->assertOk();
        $this->assertRetiredCoversAbsent($listing->getContent());
        foreach ($legacy as $post) {
            $slug = $post->translation($locale)->slug;
            $api = $this->getJson('/api/v1/site/blogs/'.$slug.'?locale='.$locale)->assertOk()
                ->assertJsonPath('data.article.cover_image', null)->assertJsonPath('data.meta.image', null);
            $this->assertRetiredCoversAbsent($api->getContent());
            $this->assertRetiredCoversAbsent($this->get(parse_url($api->json('data.article.url'), PHP_URL_PATH))->assertOk()->getContent());
        }
        $ownedApi = $this->getJson('/api/v1/site/blogs/'.$owned->translation($locale)->slug.'?locale='.$locale)->assertOk();
        $this->assertStringContainsString('blogs/owned-copyright-test-cover.jpg', $ownedApi->json('data.article.cover_image'));
        $this->assertStringContainsString('blogs/owned-copyright-test-cover.jpg', $ownedApi->json('data.meta.image'));

        foreach ([...$legacy, $owned, $other] as $post) {
            $fresh = $post->fresh();
            $this->assertSame($post->cover_image, $fresh->cover_image);
            foreach ($post->translations as $translation) {
                $stored = $translation->fresh();
                $this->assertSame($translation->og_image, $stored->og_image);
                $this->assertSame($translation->schema_json, $stored->schema_json);
            }
        }
        $this->getJson('/api/v1/site/blogs/geo-cover-control?locale=en')->assertNotFound();
        $this->getJson('/api/v1/site/brands/geo/blogs/geo-cover-control?locale=en')->assertOk()
            ->assertJsonPath('data.post.image', $landingImage)->assertJsonPath('data.post.revision', $revision);
        $this->assertSame($originalRunPayload, $run->fresh()->payload);
        Http::assertNothingSent();
    }

    public function test_retired_catalogue_endpoints_and_previously_queued_tmdb_jobs_make_no_outbound_or_file_writes(): void
    {
        $this->getJson('/api/trending?page=2')->assertOk()->assertExactJson(['results' => [], 'page' => 2, 'total_pages' => 1]);
        $this->getJson('/api/movies/movie/42/trailer')->assertNotFound()->assertJsonPath('url', null);
        $this->getJson('/api/movies/tv/42/trailer')->assertNotFound()->assertJsonPath('url', null);

        $isolatedPublic = rtrim(config('view.compiled'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'retired-tmdb-job-output';
        $this->assertDirectoryDoesNotExist($isolatedPublic);
        $this->app->usePublicPath($isolatedPublic);
        foreach (['image.tmdb.org', 'IMAGE.TMDB.ORG'] as $host) {
            (new GenerateWebpImage('https://'.$host.'/t/p/w780/retired-fixture.jpg', 960, 540, 70, 'webp_images/retired-fixture.webp'))->handle();
        }
        Http::assertNothingSent();
        $this->assertDirectoryDoesNotExist($isolatedPublic);
        Bus::assertNotDispatched(GenerateWebpImage::class);
    }

    private function createOffers(): array
    {
        $retired = [];
        foreach (self::RETIRED_OFFERS as $index => $slug) {
            $retired[] = DigitalProduct::create([
                'title' => 'Retired offer fixture '.$slug, 'slug' => $slug, 'price' => 3, 'currency' => 'USD',
                'image' => $slug.'.webp', 'is_active' => true, 'sort_order' => $index, 'delivery_type' => 'manual',
            ]);
        }
        $vpn = DigitalProduct::create(['title' => 'NordVPN fixture', 'slug' => 'nordvpn', 'price' => 3,
            'currency' => 'USD', 'image' => 'nordvpn.webp', 'is_active' => true, 'sort_order' => 20, 'delivery_type' => 'manual']);
        $affiliate = ShopProduct::create(['name' => 'Text-only device fixture', 'asin' => 'COPYRIGHT-DEVICE',
            'link' => 'https://example.test/affiliate-device', 'image' => 'B08CRV62C4.webp', 'is_active' => true, 'sort_order' => 1]);
        $package = Package::create(['type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Opplex',
            'price_amount' => 17.43, 'display_price' => '$17.43', 'duration_months' => 1, 'active' => true,
            'is_available' => true, 'max_devices' => 1, 'features' => ['Setup help'], 'sort_order' => 1]);

        return compact('retired', 'vpn', 'affiliate', 'package');
    }

    private function createPost(string $site, string $slug, string $cover, bool $featured = false): Blog
    {
        $post = Blog::create(['site_key' => $site, 'status' => 'published', 'published_at' => now()->subHour(),
            'cover_image' => $cover, 'is_featured' => $featured, 'views' => 0]);
        foreach (config('app.locales') as $locale) {
            $post->translations()->create(['locale' => $locale, 'slug' => $slug, 'title' => 'Cover control '.$locale.' '.$slug,
                'excerpt' => 'An isolated cover fixture used only by this regression test.', 'content' => '<p>Useful article content remains available.</p>',
                'og_image' => $cover, 'schema_json' => ['@context' => 'https://schema.org', '@type' => 'BlogPosting',
                    'headline' => 'Cover control '.$locale, 'image' => [url('storage/'.$cover)],
                    'associatedMedia' => [['@type' => 'ImageObject', 'url' => url('storage/'.$cover)]],
                    'nested' => ['contentUrl' => url('storage/'.$cover)]]]);
        }

        return $post->load('translations');
    }

    private function assertRetiredArtAbsent(string $body): void
    {
        $body = str_replace('\\/', '/', $body);
        $resource = 'samsung-tv-2|mobileimg1|onmobile(?:2|3|4|7|8|9|10|11|12)?|alldevices|[2-9]|network-[3-5]|movie-night-tv(?:-(?:480|720|1024|1280))?|fastx|squidgame|extraction2';
        $this->assertDoesNotMatchRegularExpression('~/images/resource/(?:responsive/)?(?:'.$resource.')(?:-webp-(?:320|640|960))?\.(?:webp|avif)~', $body);
        $this->assertDoesNotMatchRegularExpression('~/images/digital-products/(?:responsive/)?(?:'.implode('|', self::RETIRED_OFFERS).')(?:-(?:png|webp)-(?:320|640|960))?\.(?:png|webp|avif)~', $body);
        $this->assertStringNotContainsString('/webp_images/', $body);
        $this->assertStringNotContainsString('image.tmdb.org', $body);
        $this->assertStringNotContainsString('Retired offer fixture', $body);
    }

    private function assertRetiredCoversAbsent(string $body): void
    {
        foreach (self::OLD_COVERS as $cover) {
            $this->assertStringNotContainsString(basename($cover), $body);
        }
    }
}
