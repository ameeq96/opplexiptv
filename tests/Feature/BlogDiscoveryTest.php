<?php

namespace Tests\Feature;

use App\Models\BlogGenerationRun;
use App\Models\BlogPublishDelivery;
use App\Models\BlogSiteSetting;
use App\Services\Blogs\BlogContentService;
use App\Services\Blogs\BlogDiscoveryService;
use App\Services\Blogs\BlogPublicationService;
use App\Services\Blogs\GoogleSitemapClient;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BlogDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

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
        Carbon::setTestNow('2026-10-28 04:00:00');
        CarbonImmutable::setTestNow('2026-10-28 04:00:00');
        Http::preventStrayRequests();
        Mail::fake(); Notification::fake(); Bus::fake(); Cache::flush();
        config()->set('blog-publishing.google.credentials_file', sys_get_temp_dir().'/blog-google-missing-'.bin2hex(random_bytes(8)).'.json');
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        Carbon::setTestNow(); CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_unpublished_or_failed_runs_cannot_notify_search_engines(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun(false);
        foreach (['reserved', 'ready', 'blocked', 'failed'] as $status) {
            $run->update(['status' => $status]);
            try {
                app(BlogDiscoveryService::class)->notify($run->fresh('blog'));
                $this->fail('An unpublished run must not send discovery notifications.');
            } catch (HttpException $error) {
                $this->assertSame(422, $error->getStatusCode());
            }
        }
        $this->assertSame(0, $run->deliveries()->sum('attempts'));
        Http::assertNothingSent(); Mail::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_archived_moved_or_missing_blogs_are_blocked_before_any_http(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun();
        $blog = $run->blog;
        $assertBlocked = function () use ($run): void {
            try {
                app(BlogDiscoveryService::class)->notify($run);
                $this->fail('A missing, archived or wrong-site article must not notify.');
            } catch (HttpException $error) {
                $this->assertSame(422, $error->getStatusCode());
            }
        };
        $blog->update(['status' => 'archived']);
        $assertBlocked();
        $blog->update(['status' => 'draft', 'site_key' => 'getiptv']);
        $assertBlocked();
        $blog->update(['site_key' => 'geo']);
        $run->update(['blog_id' => null]);
        $assertBlocked();
        $this->assertSame(0, $run->deliveries()->sum('attempts'));
        Http::assertNothingSent();
    }

    public function test_future_or_missing_run_publication_dates_cannot_notify(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun();
        foreach ([now()->addDay(), null] as $date) {
            $run->update(['published_at' => $date]);
            try {
                app(BlogDiscoveryService::class)->notify($run);
                $this->fail('A publication must be due and have a publication date.');
            } catch (HttpException $error) {
                $this->assertSame(422, $error->getStatusCode());
            }
        }
        $this->assertSame(0, $run->deliveries()->sum('attempts'));
        Http::assertNothingSent();
    }

    public function test_a_retained_published_snapshot_can_notify_while_a_new_edit_is_draft(): void
    {
        $this->mockGoogle(1);
        $run = $this->prepareRun();
        $run->update(['published_at' => now()->subDay()]);
        $run->blog->update(['status' => 'draft']);
        $pending = BlogGenerationRun::create(['site_key' => 'geo', 'blog_id' => $run->blog_id,
            'topic' => 'Isolated newer edit awaiting publication', 'status' => 'ready',
            'revision' => hash('sha256', 'isolated-newer-draft-revision'), 'published_at' => null]);
        $this->fakePublicResources($run);
        $this->assertTrue(app(BlogDiscoveryService::class)->notify($run)['complete']);
        $this->assertSame(1, $this->indexNowRequests());
        $this->assertSame('draft', $run->blog->fresh()->status);
        $this->assertSame('ready', $pending->fresh()->status);
        $this->assertSame(0, $pending->deliveries()->sum('attempts'));
        $this->assertDatabaseHas('blog_publish_deliveries', ['run_id' => $run->id, 'revision' => $run->revision,
            'channel' => 'indexnow', 'status' => 'accepted', 'attempts' => 1]);
    }

    public function test_public_html_must_match_the_published_article_before_notifications(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun();
        $this->fakePublicResources($run, htmlOverride: '<html><head><link rel="canonical" href="https://geoiptv.store/account/"></head><body><h1>Wrong article</h1></body></html>');
        try {
            $result = app(BlogDiscoveryService::class)->notify($run);
            $this->assertFalse($result['complete']);
        } catch (\RuntimeException) {
            $this->assertTrue(true, 'Invalid public HTML was rejected before discovery.');
        }
        Http::assertNotSent(fn (Request $request) => $request->method() !== 'GET');
        $this->assertSame(0, $run->deliveries()->whereIn('channel', ['indexnow', 'google'])->sum('attempts'));
    }

    public function test_all_ten_exact_canonicals_must_be_present_in_the_public_sitemap(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun();
        $urls = app(BlogContentService::class)->urls($run);
        $this->assertCount(10, $urls);
        $missing = array_pop($urls);
        $urls[] = $missing.'?utm_source=not-the-canonical';
        $this->fakePublicResources($run, sitemapUrls: $urls);
        $result = app(BlogDiscoveryService::class)->notify($run);
        $this->assertFalse($result['complete']);
        $this->assertDatabaseHas('blog_publish_deliveries', ['run_id' => $run->id, 'channel' => 'sitemap',
            'status' => 'failed', 'error_code' => 'public_sitemap_not_verified']);
        Http::assertNotSent(fn (Request $request) => $request->method() !== 'GET');
        $this->assertSame(0, $run->deliveries()->whereIn('channel', ['indexnow', 'google'])->sum('attempts'));
    }

    public function test_a_stale_final_language_blocks_discovery_after_the_other_nine_match(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun();
        $post = $run->payload['post'];
        $url = app(BlogContentService::class)->canonical('geo', 'hi', $post['slug']);
        $this->fakePublicResources($run, htmlOverride: '<html><head><title>Stale title</title><link rel="canonical" href="'.$url.'"></head><body><main data-blog-id="'.$post['id'].'" data-blog-revision="'.$post['revision'].'"><h1>'.$post['translations']['hi']['title'].'</h1></main></body></html>', overrideLocale: 'hi');
        try {
            app(BlogDiscoveryService::class)->notify($run);
            $this->fail('Every published language must match before notifying.');
        } catch (\RuntimeException) {
            $this->assertCount(10, Http::recorded(fn (Request $request) => in_array($request->url(), app(BlogContentService::class)->urls($run), true)));
            Http::assertNotSent(fn (Request $request) => $request->method() !== 'GET');
        }
    }

    public function test_a_noindex_http_header_blocks_discovery_even_when_the_html_matches(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun();
        $this->fakePublicResources($run, articleHeaders: ['X-Robots-Tag' => 'googlebot: noindex']);
        try {
            app(BlogDiscoveryService::class)->notify($run);
            $this->fail('An article blocked from indexing must not notify search engines.');
        } catch (\RuntimeException) {
            Http::assertNotSent(fn (Request $request) => $request->method() !== 'GET');
        }
        $this->assertSame(0, $run->deliveries()->sum('attempts'));
    }

    public function test_external_entity_markup_is_rejected_without_submitting_anything(): void
    {
        $this->mockGoogle(0);
        $run = $this->prepareRun();
        $this->fakePublicResources($run, sitemapOverride: '<!DOCTYPE urlset [<!ENTITY injected SYSTEM "https://example.invalid/private">]><urlset><url><loc>&injected;</loc></url></urlset>');
        $result = app(BlogDiscoveryService::class)->notify($run);
        $this->assertFalse($result['complete']);
        Http::assertNotSent(fn (Request $request) => $request->method() !== 'GET' || str_contains($request->url(), 'example.invalid'));
    }

    public function test_only_the_changed_article_translations_are_notified_and_202_is_pending(): void
    {
        $this->mockGoogle(1);
        $run = $this->prepareRun();
        $expected = app(BlogContentService::class)->urls($run);
        $this->fakePublicResources($run, sitemapUrls: [...$expected, 'https://geoiptv.store/', 'https://geoiptv.store/account/'], indexNowStatus: 202);
        $result = app(BlogDiscoveryService::class)->notify($run);
        $this->assertTrue($result['complete']);
        $this->assertCount(10, Http::recorded(fn (Request $request) => $request->method() === 'GET' && in_array($request->url(), $expected, true)));
        Http::assertSent(function (Request $request) use ($expected): bool {
            if ($request->url() !== 'https://api.indexnow.org/indexnow') {
                return false;
            }
            $this->assertSame('POST', $request->method());
            $this->assertSame('geoiptv.store', $request['host']);
            $this->assertSame('https://geoiptv.store/indexnow-key.txt', $request['keyLocation']);
            $this->assertSame($expected, $request['urlList']);
            $this->assertCount(10, array_unique($request['urlList']));
            foreach ($request['urlList'] as $url) {
                $this->assertStringContainsString('/blogs/geo-discovery-fixture/', $url);
                $this->assertStringNotContainsString('/account', $url);
                $this->assertStringNotContainsString('?', $url);
            }
            return true;
        });
        $this->assertDatabaseHas('blog_publish_deliveries', ['run_id' => $run->id, 'revision' => $run->revision,
            'channel' => 'indexnow', 'status' => 'accepted_pending', 'http_status' => 202, 'attempts' => 1]);
        $this->assertNotContains('indexed', array_column($result['deliveries'], 'status'));
        Mail::assertNothingSent(); Notification::assertNothingSent(); Bus::assertNothingDispatched();
    }

    public function test_delivery_is_idempotent_per_run_revision_and_a_new_revision_can_notify_once(): void
    {
        $this->mockGoogle(2);
        $run = $this->prepareRun();
        $this->fakePublicResources($run);
        $service = app(BlogDiscoveryService::class);
        $this->assertTrue($service->notify($run)['complete']);
        $this->assertTrue($service->notify($run)['complete']);
        $this->assertSame(1, $this->indexNowRequests());
        $oldRevision = $run->revision;
        $payload = $run->payload;
        $payload['post']['revision'] = hash('sha256', 'isolated-second-public-revision');
        $payload['post']['updated_at'] = now()->addMinute()->toIso8601String();
        $run->update(['revision' => $payload['post']['revision'], 'payload' => $payload]);
        $this->assertTrue($service->notify($run)['complete']);
        $this->assertTrue($service->notify($run)['complete']);
        $this->assertSame(2, $this->indexNowRequests());
        foreach ([$oldRevision, $run->revision] as $revision) {
            $this->assertDatabaseHas('blog_publish_deliveries', ['run_id' => $run->id, 'revision' => $revision,
                'channel' => 'indexnow', 'status' => 'accepted', 'attempts' => 1]);
            $this->assertDatabaseHas('blog_publish_deliveries', ['run_id' => $run->id, 'revision' => $revision,
                'channel' => 'google', 'status' => 'accepted', 'attempts' => 1]);
        }
    }

    public function test_rate_limited_notifications_wait_for_backoff_before_retrying(): void
    {
        $this->mockGoogle(1);
        $run = $this->prepareRun();
        $this->fakePublicResources($run, indexNowStatus: 429);
        $service = app(BlogDiscoveryService::class);
        $this->assertFalse($service->notify($run)['complete']);
        $delivery = $run->deliveries()->where('channel', 'indexnow')->firstOrFail();
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame(429, $delivery->http_status);
        $this->assertSame('indexnow_http_429', $delivery->error_code);
        $this->assertTrue($delivery->next_attempt_at->equalTo(now()->addMinutes(15)));
        Carbon::setTestNow(now()->addMinutes(14)); CarbonImmutable::setTestNow(Carbon::now());
        $this->assertFalse($service->notify($run)['complete']);
        $this->assertSame(1, $this->indexNowRequests());
        Carbon::setTestNow(now()->addMinutes(2)); CarbonImmutable::setTestNow(Carbon::now());
        $this->assertFalse($service->notify($run)['complete']);
        $this->assertSame(2, $this->indexNowRequests());
        $delivery->refresh();
        $this->assertSame(2, $delivery->attempts);
        $this->assertTrue($delivery->next_attempt_at->equalTo(now()->addMinutes(30)));
    }

    public function test_missing_google_credentials_are_blocked_without_http_or_secret_output(): void
    {
        $run = $this->prepareRun();
        $this->fakePublicResources($run);
        $service = app(BlogDiscoveryService::class);
        $first = $service->notify($run);
        $this->assertFalse($first['complete']);
        $this->assertDatabaseHas('blog_publish_deliveries', ['run_id' => $run->id, 'channel' => 'google',
            'status' => 'blocked_credentials', 'error_code' => 'google_credentials_missing', 'attempts' => 1]);
        $second = $service->notify($run);
        $this->assertFalse($second['complete']);
        $this->assertSame(1, $run->deliveries()->where('channel', 'google')->value('attempts'));
        $this->assertStringNotContainsString(config('blog-publishing.google.credentials_file'), json_encode($second));
        $this->assertStringNotContainsString('private_key', json_encode($second));
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'googleapis.com'));
    }

    public function test_malformed_google_credentials_fail_without_exposing_the_fixture_or_path(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'isolated-blog-malformed-');
        $this->assertIsString($path); $this->temporaryFiles[] = $path;
        file_put_contents($path, '{"private_key":"isolated-sensitive-fixture-not-a-real-key"');
        config()->set('blog-publishing.google.credentials_file', $path);
        $result = app(GoogleSitemapClient::class)->submit('geo');
        $this->assertSame(['status' => 'failed', 'http_status' => null, 'error_code' => 'google_authorization_or_delivery_failed'], $result);
        $this->assertStringNotContainsString('isolated-sensitive-fixture', json_encode($result));
        $this->assertStringNotContainsString($path, json_encode($result));
        Http::assertNothingSent();
    }

    public function test_google_client_uses_a_signed_scoped_assertion_and_submits_the_correct_sitemap(): void
    {
        $opensslConfig = tempnam(sys_get_temp_dir(), 'isolated-blog-openssl-');
        $this->assertIsString($opensslConfig); $this->temporaryFiles[] = $opensslConfig;
        file_put_contents($opensslConfig, "[req]\ndistinguished_name = fixture_name\n[fixture_name]\n");
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => $opensslConfig]);
        $this->assertInstanceOf(\OpenSSLAsymmetricKey::class, $key);
        $this->assertTrue(openssl_pkey_export($key, $privateKey, null, ['config' => $opensslConfig]));
        $publicKey = openssl_pkey_get_details($key)['key'];
        $path = tempnam(sys_get_temp_dir(), 'isolated-blog-google-');
        $this->assertIsString($path); $this->temporaryFiles[] = $path;
        file_put_contents($path, json_encode(['type' => 'service_account', 'client_email' => 'isolated-publisher@fixture.iam.gserviceaccount.com', 'private_key' => $privateKey], JSON_THROW_ON_ERROR));
        config()->set('blog-publishing.google.credentials_file', $path);
        $token = 'isolated-access-token-for-this-test-only';
        $url = 'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode('https://getiptv.store/').'/sitemaps/'.rawurlencode('https://getiptv.store/sitemap.xml');
        Http::fake(function (Request $request) use ($publicKey, $token, $url) {
            if ($request->url() === 'https://oauth2.googleapis.com/token') {
                $this->assertSame('POST', $request->method());
                $this->assertSame('urn:ietf:params:oauth:grant-type:jwt-bearer', $request['grant_type']);
                $parts = explode('.', $request['assertion']); $this->assertCount(3, $parts);
                $decode = fn ($value) => base64_decode(strtr($value, '-_', '+/'), true);
                $this->assertSame(['alg' => 'RS256', 'typ' => 'JWT'], json_decode($decode($parts[0]), true));
                $claims = json_decode($decode($parts[1]), true);
                $this->assertSame('isolated-publisher@fixture.iam.gserviceaccount.com', $claims['iss']);
                $this->assertSame('https://www.googleapis.com/auth/webmasters', $claims['scope']);
                $this->assertSame('https://oauth2.googleapis.com/token', $claims['aud']);
                $this->assertSame(3600, $claims['exp'] - $claims['iat']);
                $this->assertSame(1, openssl_verify($parts[0].'.'.$parts[1], $decode($parts[2]), $publicKey, OPENSSL_ALGO_SHA256));
                return Http::response(['access_token' => $token, 'expires_in' => 3600], 200);
            }
            if ($request->url() === $url) {
                $this->assertSame('PUT', $request->method());
                $this->assertSame(['Bearer '.$token], $request->header('Authorization'));
                $this->assertSame('', $request->body());
                return Http::response('', 204);
            }
            throw new \RuntimeException('Unexpected isolated Google request.');
        });
        $client = app(GoogleSitemapClient::class);
        $this->assertTrue($client->configured());
        $this->assertSame(['status' => 'accepted', 'http_status' => 204, 'error_code' => null], $client->submit('getiptv'));
        $this->assertSame('accepted', $client->submit('getiptv')['status']);
        $this->assertCount(1, Http::recorded(fn (Request $request) => $request->url() === 'https://oauth2.googleapis.com/token'));
        $this->assertCount(2, Http::recorded(fn (Request $request) => $request->url() === $url));
        Http::assertSentCount(3);
    }

    private function mockGoogle(int $times): void
    {
        $this->mock(GoogleSitemapClient::class, function ($mock) use ($times): void {
            $mock->shouldReceive('configured')->andReturn(true);
            $mock->shouldReceive('submit')->with('geo')->times($times)
                ->andReturn(['status' => 'accepted', 'http_status' => 204, 'error_code' => null]);
        });
    }

    private function prepareRun(bool $published = true): BlogGenerationRun
    {
        $publisher = app(BlogPublicationService::class);
        $publisher->initializeSettings();
        BlogSiteSetting::where('site_key', 'geo')->update(['enabled' => true]);
        $run = $publisher->import('geo', '2026-10-28', $this->payload());
        if ($published) {
            $run->blog->update(['status' => 'published', 'published_at' => now()]);
            $run->update(['status' => 'published', 'published_at' => now()]);
        }
        return $run->fresh('blog');
    }

    private function fakePublicResources(BlogGenerationRun $run, ?array $sitemapUrls = null, int $indexNowStatus = 200, ?string $htmlOverride = null, ?string $sitemapOverride = null, ?string $overrideLocale = null, array $articleHeaders = []): void
    {
        $urls = $sitemapUrls ?? app(BlogContentService::class)->urls($run);
        $sitemap = $sitemapOverride ?? '<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .implode('', array_map(fn ($url) => '<url><loc>'.htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc></url>', $urls)).'</urlset>';
        Http::fake(function (Request $request) use ($run, $sitemap, $indexNowStatus, $htmlOverride, $overrideLocale, $articleHeaders) {
            if ($request->url() === 'https://geoiptv.store/sitemap.xml') {
                return Http::response($sitemap, 200, ['Content-Type' => 'application/xml']);
            }
            if ($request->url() === 'https://geoiptv.store/indexnow-key.txt') {
                return Http::response(str_repeat('ab', 16), 200, ['Content-Type' => 'text/plain']);
            }
            if ($request->url() === 'https://api.indexnow.org/indexnow') {
                return Http::response('', $indexNowStatus);
            }
            foreach (config('blog-publishing.supported_locales') as $locale) {
                $post = $run->payload['post'];
                $url = app(BlogContentService::class)->canonical('geo', $locale, $post['slug']);
                if ($request->url() === $url) {
                    $escape = fn ($value) => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $html = '<!doctype html><html lang="'.$locale.'"><head><title>'.$escape($post['translations'][$locale]['seo_title']).'</title><link rel="canonical" href="'.$escape($url).'"></head>'
                        .'<body><main data-blog-id="'.$post['id'].'" data-blog-revision="'.$post['revision'].'"><h1>'.$escape($post['translations'][$locale]['title']).'</h1><p>'.$escape($post['translations'][$locale]['sections'][0]['paragraphs'][0]).'</p></main></body></html>';
                    $body = $htmlOverride !== null && ($overrideLocale === null || $overrideLocale === $locale) ? $htmlOverride : $html;
                    return Http::response($body, 200, $articleHeaders + ['Content-Type' => 'text/html']);
                }
            }
            throw new \RuntimeException('Unexpected isolated discovery request.');
        });
    }

    private function indexNowRequests(): int
    {
        return Http::recorded(fn (Request $request) => $request->url() === 'https://api.indexnow.org/indexnow')->count();
    }

    private function payload(): array
    {
        $translations = [];
        foreach (config('blog-publishing.supported_locales') as $locale) {
            $translations[$locale] = [
                'title' => $locale.' Geo IPTV setup notes before a support enquiry',
                'excerpt' => $locale.' Keep the exact screen model, player version and the first visible error together before asking for setup help.',
                'seo_title' => $locale.' Geo IPTV setup records for a clear support question',
                'seo_description' => $locale.' Prepare a useful device record with your screen model, software version, connection type and a precise question before changing working settings.',
                'sections' => [
                    ['heading' => $locale.' Start with the device record', 'paragraphs' => [
                        $locale.' Write down the model shown in the system information screen and the software version currently installed. A similar-looking television can run a different operating system, so its instructions may show different menus. Record the player name separately and check the developer documentation for that exact version before changing account settings or installing another application.',
                        $locale.' Keep one short note for the screen, another for the player and a third for the unresolved question. If ordinary internet access fails on the same device, describe that result first. This gives support a concrete starting point and avoids treating an app installation as proof that an existing order is active.',
                    ], 'items' => [], 'links' => [['label' => 'Official Google TV help', 'url' => 'https://support.google.com/googletv/answer/10070482?hl=en']]],
                    ['heading' => $locale.' Keep a useful result', 'paragraphs' => [
                        $locale.' Change one documented setting at a time and note whether the visible symptom changes. Preserve the last working configuration rather than resetting unrelated settings. Include the approximate time and the original error text when requesting help; a screenshot can be useful after private account fields have been cropped out.',
                        $locale.' Describe the connection used for this attempt and whether another playback session was already open. Ask about the selected plan rather than assuming every provider has the same allowance. An enquiry should include the order reference privately, but it should never include passwords, one-time codes or a full payment-card number.',
                    ], 'items' => [], 'links' => []],
                ],
            ];
        }
        return ['version' => 1, 'site_key' => 'geo', 'topic' => 'Preparing a precise device record before a support enquiry',
            'primary_keyword' => 'Geo IPTV setup', 'slug' => 'geo-discovery-fixture', 'translations' => $translations,
            'sources' => [['label' => 'Official Google TV help', 'url' => 'https://support.google.com/googletv/answer/10070482?hl=en']]];
    }
}
