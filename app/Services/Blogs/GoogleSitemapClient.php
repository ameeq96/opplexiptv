<?php

namespace App\Services\Blogs;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GoogleSitemapClient
{
    public function configured(): bool
    {
        $path = config('blog-publishing.google.credentials_file');
        return is_string($path) && is_file($path) && is_readable($path);
    }

    public function submit(string $site): array
    {
        $definition = config('blog-publishing.sites.'.$site);
        if (!is_array($definition) || !$this->configured()) {
            return ['status' => 'blocked_credentials', 'http_status' => null, 'error_code' => 'google_credentials_missing'];
        }
        try {
            $token = $this->accessToken();
            $url = 'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($definition['google_property'])
                .'/sitemaps/'.rawurlencode('https://'.$definition['domain'].'/sitemap.xml');
            $response = Http::withToken($token)->acceptJson()->timeout(25)
                ->withOptions(['allow_redirects' => false])->send('PUT', $url);
            return [
                'status' => $response->successful() ? 'accepted' : 'failed',
                'http_status' => $response->status(),
                'error_code' => $response->successful() ? null : 'google_sitemap_http_'.$response->status(),
            ];
        } catch (Throwable) {
            return ['status' => 'failed', 'http_status' => null, 'error_code' => 'google_authorization_or_delivery_failed'];
        }
    }

    private function accessToken(): string
    {
        $path = config('blog-publishing.google.credentials_file');
        $cacheKey = 'blog-google-token:'.hash('sha256', $path.':'.filemtime($path));
        return Cache::remember($cacheKey, 3000, function () use ($path): string {
            $credentials = json_decode(file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
            if (($credentials['type'] ?? null) !== 'service_account'
                || !is_string($credentials['client_email'] ?? null)
                || !str_ends_with($credentials['client_email'], '.iam.gserviceaccount.com')
                || !is_string($credentials['private_key'] ?? null)) {
                throw new RuntimeException('Invalid Google service-account configuration.');
            }
            $encode = fn ($value) => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
            $header = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
            $claims = $encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/webmasters',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => time(), 'exp' => time() + 3600,
            ], JSON_THROW_ON_ERROR));
            $unsigned = $header.'.'.$claims;
            if (!openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Google authorization signing failed.');
            }
            $response = Http::asForm()->acceptJson()->timeout(25)->withOptions(['allow_redirects' => false])
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $unsigned.'.'.$encode($signature),
                ]);
            $token = $response->json('access_token');
            if (!$response->successful() || !is_string($token) || strlen($token) < 10) {
                throw new RuntimeException('Google authorization was not accepted.');
            }
            return $token;
        });
    }
}
