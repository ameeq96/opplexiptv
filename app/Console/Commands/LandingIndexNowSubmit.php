<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LandingIndexNowSubmit extends Command
{
    protected $signature = 'landing:indexnow {--domain= : Approved apex domain} {--url=* : Changed public URL; repeat for each URL} {--submit : Send the validated URLs; otherwise dry-run only}';

    protected $description = 'Validate explicitly selected changed landing pages and optionally notify IndexNow';

    private const DOMAINS = ['trexiptv8k.store', 'opplexiptv.store', 'geoiptv.store', '5gliveiptv.store', 'iptvsubscription.shop', 'getiptv.store'];
    private const LOCALES = ['es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];
    private const PAGES = ['about', 'contact', 'faqs', 'packages', 'reseller-panel', 'iptv-applications', 'setup', 'movies', 'shop', 'activate', 'activate-info', 'blogs', 'terms-of-service', 'privacy-policy', 'refund-policy'];
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    public function handle(): int
    {
        $domain = $this->option('domain');
        if (!in_array($domain, self::DOMAINS, true)) {
            $this->error('Select one approved apex domain with --domain.');
            return self::FAILURE;
        }

        $urls = array_values(array_unique($this->option('url')));
        if ($urls === []) {
            $this->info('No changed URLs supplied; nothing requested or submitted.');
            return self::SUCCESS;
        }
        if (count($urls) > 10000) {
            $this->error('A submission may contain at most 10000 URLs.');
            return self::FAILURE;
        }
        foreach ($urls as $url) {
            if (!$this->isPublicUrl($url, $domain)) {
                $this->error('Every URL must be an exact HTTPS public-page URL on the selected apex domain, without query or fragment.');
                return self::FAILURE;
            }
        }

        try {
            $origin = 'https://'.$domain;
            [, $sitemap] = $this->getPublic($origin.'/sitemap.xml', 2097152);
            $members = $this->sitemapUrls($sitemap, $domain);
            foreach ($urls as $url) {
                if (!isset($members[$url])) {
                    throw new RuntimeException('A selected URL is not an exact public sitemap member.');
                }
            }

            $keyLocation = $origin.'/indexnow-key.txt';
            [, $keyBody] = $this->getPublic($keyLocation, 256);
            $key = trim($keyBody);
            if (!preg_match('//u', $keyBody) || !preg_match('/\A[A-Za-z0-9-]{8,128}\z/D', $key)) {
                throw new RuntimeException('The published IndexNow key file is invalid.');
            }

            foreach ($urls as $url) {
                [$response, $html] = $this->getPublic($url, 3145728);
                $this->validatePage($response, $html, $url);
            }

            $this->info(sprintf('Validated %d changed public URL(s) for %s.', count($urls), $domain));
            if (!$this->option('submit')) {
                $this->info('Dry run; no IndexNow submission sent.');
                return self::SUCCESS;
            }

            $response = Http::asJson()->withoutRedirecting()->connectTimeout(8)->timeout(20)
                ->withOptions(['stream' => true, 'read_timeout' => 20])
                ->post(self::ENDPOINT, [
                    'host' => $domain,
                    'key' => $key,
                    'keyLocation' => $keyLocation,
                    'urlList' => $urls,
                ]);

            if ($response->status() === 200) {
                $this->info('IndexNow received the URLs (HTTP 200); crawling and indexing are not guaranteed.');
                return self::SUCCESS;
            }
            if ($response->status() === 202) {
                $this->info('IndexNow received the URLs; key validation is pending (HTTP 202). Indexing is not confirmed.');
                return self::SUCCESS;
            }

            $this->error('IndexNow did not accept the submission (HTTP '.$response->status().'). No automatic retry was made.');
            return self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error('IndexNow validation or delivery failed; no response details were printed.');
            return self::FAILURE;
        }
    }

    private function isPublicUrl(string $url, string $domain): bool
    {
        if (strlen($url) > 2048 || !preg_match('~\Ahttps://'.preg_quote($domain, '~').'/(?:[a-z0-9-]+/)*\z~D', $url)) {
            return false;
        }
        $segments = array_values(array_filter(explode('/', substr($url, strlen('https://'.$domain)))));
        if (in_array($segments[0] ?? null, self::LOCALES, true)) {
            array_shift($segments);
        }
        if ($segments === []) {
            return true;
        }
        return in_array($segments[0], self::PAGES, true)
            && (count($segments) === 1 || ($segments[0] === 'blogs' && count($segments) === 2));
    }

    private function getPublic(string $url, int $limit): array
    {
        $response = Http::withoutRedirecting()->connectTimeout(8)->timeout(20)
            ->withOptions(['stream' => true, 'read_timeout' => 20])->get($url);
        if ($response->status() !== 200) {
            throw new RuntimeException('A required public resource did not return HTTP 200; redirects are not followed.');
        }
        $length = $response->header('Content-Length');
        if (is_string($length) && ctype_digit($length) && (int) $length > $limit) {
            throw new RuntimeException('A public resource exceeds the validation size limit.');
        }
        $stream = $response->toPsrResponse()->getBody();
        $body = '';
        while (!$stream->eof() && strlen($body) <= $limit) {
            $chunk = $stream->read(min(8192, $limit + 1 - strlen($body)));
            if ($chunk === '' && !$stream->eof()) {
                throw new RuntimeException('A public resource could not be read completely.');
            }
            $body .= $chunk;
        }
        if (strlen($body) > $limit) {
            throw new RuntimeException('A public resource exceeds the validation size limit.');
        }
        return [$response, $body];
    }

    private function sitemapUrls(string $xml, string $domain): array
    {
        if (preg_match('/<!\s*(?:DOCTYPE|ENTITY)\b/i', $xml)) {
            throw new RuntimeException('Sitemap entity declarations are not allowed.');
        }
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $valid = $document->loadXML($xml, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (!$valid || $document->documentElement?->localName !== 'urlset'
            || !in_array($document->documentElement?->namespaceURI, [null, '', 'http://www.sitemaps.org/schemas/sitemap/0.9'], true)) {
            throw new RuntimeException('A valid public urlset sitemap is required.');
        }
        $xpath = new DOMXPath($document);
        $namespace = $document->documentElement->namespaceURI;
        if ($namespace) {
            $xpath->registerNamespace('sm', $namespace);
        }
        $nodes = $xpath->query($namespace ? '/sm:urlset/sm:url/sm:loc' : '/urlset/url/loc');
        if (!$nodes || $nodes->length === 0 || $nodes->length > 10000) {
            throw new RuntimeException('The public sitemap must contain between 1 and 10000 URLs.');
        }
        $members = [];
        foreach ($nodes as $node) {
            $url = trim($node->textContent);
            if (!$this->isPublicUrl($url, $domain)) {
                throw new RuntimeException('The sitemap contains a URL outside the approved public-page scope.');
            }
            $members[$url] = true;
        }
        return $members;
    }

    private function validatePage(Response $response, string $html, string $url): void
    {
        $robots = $response->header('X-Robots-Tag');
        if (preg_match('/\b(?:noindex|none)\b/i', $robots) || $response->header('Refresh')) {
            throw new RuntimeException('A selected page is not eligible for public indexing notification.');
        }
        if (!preg_match('~\Atext/html(?:\s*;|\z)~i', $response->header('Content-Type') ?? '')) {
            throw new RuntimeException('A selected page did not return HTML.');
        }
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $valid = $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (!$valid) {
            throw new RuntimeException('A selected page could not be parsed.');
        }
        foreach ($document->getElementsByTagName('meta') as $meta) {
            if ((in_array(strtolower($meta->getAttribute('name')), ['robots', 'bingbot', 'googlebot'], true)
                    && preg_match('/\b(?:noindex|none)\b/i', $meta->getAttribute('content')))
                || strtolower($meta->getAttribute('http-equiv')) === 'refresh') {
                throw new RuntimeException('A selected page is not eligible for public indexing notification.');
            }
        }
        $canonicals = [];
        foreach ($document->getElementsByTagName('link') as $link) {
            if (in_array('canonical', preg_split('/\s+/', strtolower(trim($link->getAttribute('rel')))), true)) {
                $canonicals[] = $link->getAttribute('href');
            }
        }
        if ($canonicals !== [$url]) {
            throw new RuntimeException('A selected page must have one exact self-referencing canonical URL.');
        }
    }
}
