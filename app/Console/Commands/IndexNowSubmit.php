<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Submit the sitemap's URLs to IndexNow so participating engines (Bing,
 * Yandex, Seznam, Naver…) discover new/changed pages within minutes.
 *
 * Setup: the matching key file lives at public/<KEY>.txt and MUST be
 * deployed and reachable at https://<host>/<KEY>.txt — IndexNow verifies
 * ownership by fetching it.
 */
class IndexNowSubmit extends Command
{
    protected $signature = 'indexnow:submit {--dry : Build and print the payload without sending}';
    protected $description = 'Submit sitemap URLs to IndexNow (Bing, Yandex, etc.) for near-instant discovery';

    /** IndexNow key — must match the filename of public/<KEY>.txt. */
    private const KEY = '978a20800d5de9c0b1a2ff9b51aed86c';

    public function handle(): int
    {
        $sitemap = public_path('sitemap.xml');
        if (!File::exists($sitemap)) {
            $this->error('sitemap.xml not found — run `php artisan sitemap:generate` first.');
            return self::FAILURE;
        }

        $xml = @simplexml_load_file($sitemap);
        if ($xml === false) {
            $this->error('Could not parse sitemap.xml.');
            return self::FAILURE;
        }

        $urls = [];
        foreach ($xml->url as $node) {
            $loc = trim((string) $node->loc);
            if ($loc !== '') {
                $urls[] = $loc;
            }
        }
        $urls = array_values(array_unique($urls));

        if ($urls === []) {
            $this->warn('No URLs found in sitemap.xml.');
            return self::SUCCESS;
        }

        $host = parse_url($urls[0], PHP_URL_HOST)
            ?: parse_url((string) config('app.url'), PHP_URL_HOST);

        $payload = [
            'host'        => $host,
            'key'         => self::KEY,
            'keyLocation' => 'https://' . $host . '/' . self::KEY . '.txt',
            'urlList'     => array_slice($urls, 0, 10000),
        ];

        $this->info(sprintf('Prepared %d URL(s) for IndexNow (host: %s).', count($payload['urlList']), $host));

        if ($this->option('dry')) {
            $this->line('Dry run — nothing sent. keyLocation: ' . $payload['keyLocation']);
            return self::SUCCESS;
        }

        try {
            $res = Http::asJson()->timeout(20)->post('https://api.indexnow.org/indexnow', $payload);
        } catch (\Throwable $e) {
            $this->error('IndexNow request failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // IndexNow returns 200 (OK) or 202 (Accepted) on success.
        if (in_array($res->status(), [200, 202], true)) {
            $this->info('IndexNow accepted the submission (HTTP ' . $res->status() . ').');
            return self::SUCCESS;
        }

        $this->error('IndexNow responded HTTP ' . $res->status() . ': ' . $res->body());
        return self::FAILURE;
    }
}
