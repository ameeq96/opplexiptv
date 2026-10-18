<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LandingIndexNowSubmitTest extends TestCase
{
    private const KEY = '0123456789abcdef0123456789abcdef';
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';
    private string $output = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
    }

    private function runCommand(array $urls = [], bool $submit = false, string $domain = 'trexiptv8k.store'): int
    {
        $status = Artisan::call('landing:indexnow', ['--domain' => $domain, '--url' => $urls, '--submit' => $submit]);
        $this->output = Artisan::output();
        return $status;
    }

    private function fakeSite(string $domain = 'trexiptv8k.store', array $overrides = []): void
    {
        $origin = 'https://'.$domain;
        $urls = [$origin.'/', $origin.'/packages/', $origin.'/it/packages/'];
        $responses = [
            $origin.'/sitemap.xml' => Http::response($this->sitemap($urls), 200, ['Content-Type' => 'application/xml']),
            $origin.'/indexnow-key.txt' => Http::response(self::KEY."\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']),
            self::ENDPOINT => Http::response('', 200),
        ];
        foreach ($urls as $url) {
            $responses[$url] = Http::response($this->page($url), 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }
        Http::fake(array_replace($responses, $overrides));
    }

    private function sitemap(array $urls): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .implode('', array_map(fn ($url) => '<url><loc>'.htmlspecialchars($url, ENT_XML1).'</loc></url>', $urls)).'</urlset>';
    }

    private function page(string $url, string $head = ''): string
    {
        return '<!doctype html><html lang="en"><head><title>Public page</title><link rel="canonical" href="'.$url.'">'
            .$head.'</head><body><main><h1>Useful page</h1></main></body></html>';
    }

    public function test_empty_input_makes_no_http_requests_even_with_submit(): void
    {
        Http::fake();
        $this->assertSame(0, $this->runCommand([], true));
        Http::assertNothingSent();
        $this->assertStringContainsString('No changed URLs', $this->output);
    }

    public function test_default_is_dry_run_and_does_not_print_the_key(): void
    {
        $this->fakeSite();
        $this->assertSame(0, $this->runCommand(['https://trexiptv8k.store/']));
        Http::assertSentCount(3);
        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST');
        $this->assertStringContainsString('Dry run', $this->output);
        $this->assertStringNotContainsString(self::KEY, $this->output);
    }

    public static function domains(): array
    {
        return array_map(fn ($domain) => [$domain], ['trexiptv8k.store', 'opplexiptv.store', 'geoiptv.store', '5gliveiptv.store']);
    }

    #[DataProvider('domains')]
    public function test_explicit_submission_uses_only_deduplicated_public_urls(string $domain): void
    {
        $this->fakeSite($domain);
        $origin = 'https://'.$domain;
        $this->assertSame(0, $this->runCommand([$origin.'/', $origin.'/it/packages/', $origin.'/'], true, $domain));
        Http::assertSentCount(5);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST' && $request->url() === self::ENDPOINT
            && $request['host'] === $domain && $request['key'] === self::KEY
            && $request['keyLocation'] === $origin.'/indexnow-key.txt'
            && $request['urlList'] === [$origin.'/', $origin.'/it/packages/']);
        $this->assertStringContainsString('HTTP 200', $this->output);
        $this->assertStringContainsString('not guaranteed', $this->output);
        $this->assertStringNotContainsString(self::KEY, $this->output);
    }

    public static function invalidUrls(): array
    {
        return array_map(fn ($url) => [$url], [
            'https://geoiptv.store/', 'https://www.trexiptv8k.store/', 'http://trexiptv8k.store/',
            'https://user:password@trexiptv8k.store/', 'https://trexiptv8k.store:443/',
            'https://trexiptv8k.store/?token=private', 'https://trexiptv8k.store/#private',
            'https://trexiptv8k.store/checkout/resume/private/', 'https://trexiptv8k.store/account/',
            'https://trexiptv8k.store/admin/', 'https://trexiptv8k.store/api/',
            'https://trexiptv8k.store/it/account/', 'https://trexiptv8k.store/%2e%2e/account/',
            'https://trexiptv8k.store/packages', 'https://trexiptv8k.store/index.html',
            'https://trexiptv8k.store/packages/../account/', 'https://trexiptv8k.store/unknown/',
            'https://trexiptv8k.store/blogs/article/private/',
        ]);
    }

    #[DataProvider('invalidUrls')]
    public function test_noncanonical_private_or_offsite_urls_are_rejected_before_http(string $url): void
    {
        Http::fake();
        $this->assertSame(1, $this->runCommand([$url], true));
        Http::assertNothingSent();
        $this->assertStringNotContainsString($url, $this->output);
    }

    public function test_unapproved_domain_and_excessive_batch_are_rejected_before_http(): void
    {
        Http::fake();
        $this->assertSame(1, $this->runCommand(['https://example.com/'], true, 'example.com'));
        $urls = array_map(fn ($i) => 'https://trexiptv8k.store/blogs/article-'.$i.'/', range(0, 10000));
        $this->assertSame(1, $this->runCommand($urls, true));
        Http::assertNothingSent();
    }

    public function test_url_absent_from_sitemap_is_not_submitted(): void
    {
        $this->fakeSite();
        $this->assertSame(1, $this->runCommand(['https://trexiptv8k.store/about/'], true));
        Http::assertSentCount(1);
        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST');
    }

    public static function invalidSitemaps(): array
    {
        return [
            ['<urlset>'],
            ['<sitemapindex><sitemap><loc>https://elsewhere.test/</loc></sitemap></sitemapindex>'],
            ['<!DOCTYPE urlset [<!ENTITY leak SYSTEM "file:///private-file">]><urlset><url><loc>&leak;</loc></url></urlset>'],
            ['<urlset xmlns="https://untrusted.test/schema"><url><loc>https://trexiptv8k.store/</loc></url></urlset>'],
            ['<urlset><url><loc>https://elsewhere.test/</loc></url></urlset>'],
            ['<urlset><url><loc>https://trexiptv8k.store/account/</loc></url></urlset>'],
        ];
    }

    #[DataProvider('invalidSitemaps')]
    public function test_sitemap_is_parsed_without_external_entities_or_offsite_reads(string $xml): void
    {
        $this->fakeSite(overrides: ['https://trexiptv8k.store/sitemap.xml' => Http::response($xml, 200)]);
        $this->assertSame(1, $this->runCommand(['https://trexiptv8k.store/'], true));
        Http::assertSentCount(1);
    }

    public static function invalidKeys(): array
    {
        return [['short'], [str_repeat('a', 129)], ['valid-key-with spaces'], ["invalid\xffkey"], ['<html>error page</html>']];
    }

    #[DataProvider('invalidKeys')]
    public function test_invalid_or_non_utf8_key_is_never_submitted_or_printed(string $key): void
    {
        $this->fakeSite(overrides: ['https://trexiptv8k.store/indexnow-key.txt' => Http::response($key, 200)]);
        $this->assertSame(1, $this->runCommand(['https://trexiptv8k.store/'], true));
        Http::assertSentCount(2);
        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST');
        $this->assertStringNotContainsString($key, $this->output);
    }

    public static function redirectedResources(): array
    {
        $cases = [];
        foreach (['/sitemap.xml', '/indexnow-key.txt', '/'] as $path) {
            foreach ([302, 404, 503] as $status) {
                $cases[] = [$path, $status];
            }
        }
        return $cases;
    }

    #[DataProvider('redirectedResources')]
    public function test_redirects_and_missing_resources_are_rejected(string $path, int $status): void
    {
        $this->fakeSite(overrides: ['https://trexiptv8k.store'.$path => Http::response('', $status, ['Location' => 'http://127.0.0.1/private'])]);
        $this->assertSame(1, $this->runCommand(['https://trexiptv8k.store/'], true));
        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST' || str_contains($request->url(), '127.0.0.1'));
    }

    public static function ineligiblePages(): array
    {
        return [
            ['<meta name="robots" content="noindex,follow">', []],
            ['<meta name="bingbot" content="none">', []],
            ['<meta name="googlebot" content="NOINDEX">', []],
            ['<meta http-equiv="refresh" content="0;url=https://other.test">', []],
            ['', ['X-Robots-Tag' => 'bingbot: noindex']],
            ['', ['x-ROBOTS-taG' => 'NoIndex']],
            ['', ['Refresh' => '0;url=https://other.test']],
            ['<link rel="canonical" href="https://trexiptv8k.store/packages/">', []],
        ];
    }

    #[DataProvider('ineligiblePages')]
    public function test_noindex_redirecting_and_duplicate_canonical_pages_are_rejected(string $head, array $headers): void
    {
        $url = 'https://trexiptv8k.store/';
        $this->fakeSite(overrides: [$url => Http::response($this->page($url, $head), 200, array_merge(['Content-Type' => 'text/html'], $headers))]);
        $this->assertSame(1, $this->runCommand([$url], true));
        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST');
    }

    public static function invalidPageResponses(): array
    {
        return [['wrong-canonical'], ['missing-canonical'], ['non-html'], ['oversized'], ['missing']];
    }

    #[DataProvider('invalidPageResponses')]
    public function test_wrong_missing_canonical_non_html_and_oversized_responses_fail_closed(string $kind): void
    {
        $url = 'https://trexiptv8k.store/';
        $response = match ($kind) {
            'wrong-canonical' => Http::response($this->page('https://other.test/'), 200, ['Content-Type' => 'text/html']),
            'missing-canonical' => Http::response('<html><body>No canonical</body></html>', 200, ['Content-Type' => 'text/html']),
            'non-html' => Http::response('{}', 200, ['Content-Type' => 'application/json']),
            'oversized' => Http::response(str_repeat('x', 3145729), 200, ['Content-Type' => 'text/html']),
            'missing' => Http::response('', 404),
        };
        $this->fakeSite(overrides: [$url => $response]);
        $this->assertSame(1, $this->runCommand([$url], true));
        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST');
    }

    public function test_http_202_reports_key_validation_pending_not_indexed(): void
    {
        $this->fakeSite(overrides: [self::ENDPOINT => Http::response('', 202)]);
        $this->assertSame(0, $this->runCommand(['https://trexiptv8k.store/'], true));
        $this->assertStringContainsString('key validation is pending', $this->output);
        $this->assertStringContainsString('Indexing is not confirmed', $this->output);
    }

    public static function failureStatuses(): array
    {
        return [[302], [400], [403], [422], [429], [500]];
    }

    #[DataProvider('failureStatuses')]
    public function test_protocol_failures_are_redacted_and_not_retried(int $status): void
    {
        $this->fakeSite(overrides: [self::ENDPOINT => Http::response('private-response-'.self::KEY, $status)]);
        $this->assertSame(1, $this->runCommand(['https://trexiptv8k.store/'], true));
        Http::assertSentCount(4);
        $this->assertStringContainsString('HTTP '.$status, $this->output);
        $this->assertStringNotContainsString('private-response', $this->output);
        $this->assertStringNotContainsString(self::KEY, $this->output);
    }

    public function test_transport_errors_do_not_print_private_exception_details(): void
    {
        Http::fake(fn () => throw new ConnectionException('private-transport-'.self::KEY));
        $this->assertSame(1, $this->runCommand(['https://trexiptv8k.store/'], true));
        $this->assertStringNotContainsString('private-transport', $this->output);
        $this->assertStringNotContainsString(self::KEY, $this->output);
    }
}
