<?php

namespace App\Services;

use Illuminate\Support\Facades\{Cache, Http, Log};
use Illuminate\Http\Client\ConnectionException;

class TmdbService
{
    public function __construct(private LocaleService $locale) {}

    private function base(): string
    {
        return rtrim(env('TMDB_BASE_URL', 'https://api.themoviedb.org/3'), '/');
    }
    private function key(): string
    {
        return trim((string) env('TMDB_API_KEY', ''));
    }

    public function configured(): bool
    {
        $key = strtolower($this->key());

        return $key !== '' && !in_array($key, [
            'your_tmdb_key_here',
            'your_tmdb_api_key',
            'tmdb_api_key',
            'changeme',
        ], true);
    }

    private function redact(string $message): string
    {
        return preg_replace('/api_key=([^&\s]+)/', 'api_key=[redacted]', $message) ?? $message;
    }

    private function get(string $path, array $params = [], int $ttlMinutes = 60): array
    {
        if (!$this->configured()) {
            return [];
        }

        $url = $this->base() . $path;
        $all = array_filter([
            'api_key'  => $this->key(),
            'language' => $this->locale->langCode(),
        ] + $params, fn($v) => $v !== null && $v !== '');

        $cacheKey = 'tmdb:' . md5($url . '|' . http_build_query($all));

        $staleKey = $cacheKey . ':stale';

        return Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), function () use ($url, $all, $staleKey) {
            try {
                $resp = Http::retry(3, 800)
                    ->connectTimeout(5)
                    ->timeout(10)
                    ->withoutVerifying()
                    ->get($url, $all);

                if ($resp->successful()) {
                    $json = (array) $resp->json();
                    Cache::put($staleKey, $json, now()->addHours(6));
                    return $json;
                }

                Log::warning('TMDB non-success', ['url' => $url, 'status' => $resp->status()]);
                return Cache::get($staleKey, []);
            } catch (ConnectionException $e) {
                Log::warning('TMDB connection error', ['url' => $url, 'e' => $this->redact($e->getMessage())]);
                return Cache::get($staleKey, []);
            } catch (\Throwable $e) {
                Log::error('TMDB unexpected error', ['url' => $url, 'e' => $this->redact($e->getMessage())]);
                return Cache::get($staleKey, []);
            }
        });
    }

    public function trending(string $media, string $window, int $page = 1): array
    {
        return $this->get("/trending/{$media}/{$window}", ['page' => $page]);
    }

    public function discoverMovies(int $page = 1, array $params = []): array
    {
        return $this->get('/discover/movie', array_merge([
            'page' => $page,
            'sort_by' => 'primary_release_date.desc',
            'include_adult' => 'false',
            'include_video' => 'false',
        ], $params), 30);
    }

    public function discoverTv(int $page = 1, array $params = []): array
    {
        return $this->get('/discover/tv', array_merge([
            'page' => $page,
            'sort_by' => 'first_air_date.desc',
            'include_adult' => 'false',
            'include_null_first_air_dates' => 'false',
        ], $params), 30);
    }

    public function searchMulti(string $query, int $page = 1, int $ttl = 60): array
    {
        return $this->get('/search/multi', ['query' => $query, 'page' => $page], $ttl);
    }

    public function trailerUrl(int $id, string $mediaType, int $ttl = 1440): ?string
    {
        $data = $this->get("/{$mediaType}/{$id}/videos", [], $ttl);
        foreach (($data['results'] ?? []) as $vid) {
            if (($vid['site'] ?? null) === 'YouTube' && !empty($vid['key'])) {
                return 'https://www.youtube.com/watch?v=' . $vid['key'];
            }
        }
        return null;
    }
}
