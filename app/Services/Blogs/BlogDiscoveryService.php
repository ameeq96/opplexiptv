<?php

namespace App\Services\Blogs;

use App\Models\BlogGenerationRun;
use App\Models\BlogPublishDelivery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class BlogDiscoveryService
{
    public function __construct(private readonly BlogContentService $content, private readonly GoogleSitemapClient $google)
    {
    }

    public function notify(BlogGenerationRun $run): array
    {
        $run->refresh()->load('blog');
        abort_unless($run->status === 'published', 422, 'Only a published run can notify search engines.');
        abort_unless($run->blog && $run->blog->site_key === $run->site_key && $run->blog->status !== 'archived'
            && $run->published_at && !$run->published_at->isFuture(), 422, 'Only an available published article can notify search engines.');
        $result = Cache::lock('blog-discovery:'.$run->id, 900)->get(function () use ($run): array {
            if ($this->result($run)['complete']) {
                return $this->result($run);
            }
            app(BlogPublicationService::class)->verifyPublic($run);
            $urls = $this->content->urls($run);
            $site = config('blog-publishing.sites.'.$run->site_key);
            $sitemap = $this->delivery($run, 'sitemap');
            if (!$this->accepted($sitemap)) {
                try {
                    $body = $this->publicFile('https://'.$site['domain'].'/sitemap.xml', 52 * 1024 * 1024);
                    if (preg_match('/<!DOCTYPE|<!ENTITY/i', $body)) {
                        throw new RuntimeException('Invalid sitemap markup.');
                    }
                    $previous = libxml_use_internal_errors(true);
                    try {
                        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET);
                        $members = $xml === false ? [] : array_map(fn ($node) => (string) $node, $xml->xpath('//*[local-name()="url"]/*[local-name()="loc"]'));
                    } finally {
                        libxml_clear_errors();
                        libxml_use_internal_errors($previous);
                    }
                    if (array_diff($urls, $members)) {
                        throw new RuntimeException('Published article URLs are missing from the public sitemap.');
                    }
                    $this->record($sitemap, ['status' => 'accepted', 'http_status' => 200, 'error_code' => null]);
                } catch (Throwable) {
                    $this->record($sitemap, ['status' => 'failed', 'http_status' => null, 'error_code' => 'public_sitemap_not_verified']);
                    return $this->result($run);
                }
            }
            foreach (['indexnow', 'google'] as $channel) {
                $delivery = $this->delivery($run, $channel);
                if ($this->accepted($delivery) || ($delivery->next_attempt_at && $delivery->next_attempt_at->isFuture())) {
                    continue;
                }
                if ($channel === 'google' && $delivery->status === 'blocked_credentials' && !$this->google->configured()) {
                    continue;
                }
                $outcome = $channel === 'google' ? $this->google->submit($run->site_key) : $this->indexNow($site, $urls);
                $this->record($delivery, $outcome);
            }
            return $this->result($run);
        });
        if (!is_array($result)) {
            throw new RuntimeException('A discovery delivery for this run is already in progress.');
        }
        return $result;
    }

    public function publicFile(string $url, int $maxBytes = 2097152, bool $indexable = false): string
    {
        $parts = parse_url($url);
        $hosts = array_column(config('blog-publishing.sites'), 'domain');
        if (!is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || !in_array($parts['host'] ?? '', $hosts, true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new RuntimeException('Only an approved canonical public URL can be verified.');
        }
        $response = Http::timeout(25)->withOptions(['allow_redirects' => false, 'connect_timeout' => 8])->get($url);
        if ($response->status() !== 200 || strlen($response->body()) > $maxBytes) {
            throw new RuntimeException('The public resource is not available for verification.');
        }
        if ($indexable && preg_match('/\b(?:noindex|none)\b/i', $response->header('X-Robots-Tag') ?? '')) {
            throw new RuntimeException('The public resource is not indexable.');
        }
        return $response->body();
    }

    private function indexNow(array $site, array $urls): array
    {
        try {
            $location = 'https://'.$site['domain'].$site['indexnow_key_path'];
            $key = trim($this->publicFile($location, 256));
            if (!preg_match('/^[a-zA-Z0-9-]{8,128}$/D', $key)) {
                throw new RuntimeException('The public ownership key is not valid.');
            }
            $response = Http::asJson()->acceptJson()->timeout(25)->withOptions(['allow_redirects' => false])
                ->post('https://api.indexnow.org/indexnow', ['host' => $site['domain'], 'key' => $key, 'keyLocation' => $location, 'urlList' => $urls]);
            $accepted = in_array($response->status(), [200, 202], true);
            return [
                'status' => $accepted ? ($response->status() === 202 ? 'accepted_pending' : 'accepted') : 'failed',
                'http_status' => $response->status(),
                'error_code' => $accepted ? null : 'indexnow_http_'.$response->status(),
            ];
        } catch (Throwable) {
            return ['status' => 'failed', 'http_status' => null, 'error_code' => 'indexnow_ownership_or_delivery_failed'];
        }
    }

    private function delivery(BlogGenerationRun $run, string $channel): BlogPublishDelivery
    {
        return BlogPublishDelivery::firstOrCreate(['run_id' => $run->id, 'channel' => $channel, 'revision' => $run->revision], ['status' => 'pending']);
    }

    private function accepted(BlogPublishDelivery $delivery): bool
    {
        return in_array($delivery->status, ['accepted', 'accepted_pending'], true);
    }

    private function record(BlogPublishDelivery $delivery, array $outcome): void
    {
        $attempts = $delivery->attempts + 1;
        $delivery->update($outcome + [
            'attempts' => $attempts, 'last_attempt_at' => now(),
            'next_attempt_at' => $outcome['status'] === 'failed' ? now()->addMinutes(min(1440, 15 * (2 ** min(6, $attempts - 1)))) : null,
        ]);
    }

    private function result(BlogGenerationRun $run): array
    {
        $deliveries = $run->deliveries()->where('revision', $run->revision)->get();
        return [
            'complete' => collect(['sitemap', 'indexnow', 'google'])->every(fn ($channel) => $deliveries->contains(fn ($delivery) => $delivery->channel === $channel && $this->accepted($delivery))),
            'deliveries' => $deliveries->map->only(['channel', 'status', 'http_status', 'error_code'])->values()->all(),
        ];
    }
}
