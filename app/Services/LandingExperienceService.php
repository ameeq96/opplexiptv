<?php

namespace App\Services;

use App\Models\CustomerExternalLink;
use App\Models\CustomerReview;
use App\Models\LandingPageContent;
use App\Models\Order;
use App\Models\ServiceIncident;
use App\Models\SetupMedia;

class LandingExperienceService
{
    public function reviewBrand(Order $order): ?string
    {
        if ($order->payment_provider !== 'safepay' || $order->payment_status !== 'paid' || !$order->paid_at || !$order->user_id) {
            return null;
        }
        $brands = CustomerExternalLink::query()->where('source', 'opplexify')->where('entity_type', 'landing_attribution')
            ->where('order_id', $order->id)->where('user_id', $order->user_id)->get()
            ->filter(fn ($link) => ($link->state['environment'] ?? null) === 'production')
            ->map(fn ($link) => $link->state['attribution']['brand'] ?? null)
            ->filter(fn ($brand) => is_string($brand) && isset(LandingPageContent::BRANDS[$brand]))->unique()->values();

        return $brands->count() === 1 ? $brands->first() : null;
    }

    public function reviewEligible(CustomerReview $review): bool
    {
        $order = $review->order;

        return $order && (int) $order->user_id === (int) $review->user_id
            && $this->reviewBrand($order) === $review->brand;
    }

    public function mediaUrlAllowed(string $url, string $kind): bool
    {
        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https'
            || !in_array(strtolower($parts['host'] ?? ''), ['opplexiptv.com', 'www.opplexiptv.com'], true)
            || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment']) || isset($parts['port'])) {
            return false;
        }
        $path = $parts['path'] ?? '';
        $extensions = $kind === 'image' ? 'webp|png|jpe?g|avif' : ($kind === 'video' ? 'mp4|webm' : '');
        if ($extensions === '' || !preg_match('~^/(?:images|storage)/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_.-]+\.(?:'.$extensions.')$~iD', $path)
            || str_contains($path, '..')) {
            return false;
        }
        $file = realpath(public_path(ltrim($path, '/')));
        $base = realpath(str_starts_with($path, '/storage/') ? storage_path('app/public') : public_path('images'));

        return $file !== false && $base !== false && is_file($file)
            && str_starts_with(str_replace('\\', '/', $file), rtrim(str_replace('\\', '/', $base), '/').'/');
    }

    public function mediaEligible(SetupMedia $media): bool
    {
        foreach (['guide_slug', 'title', 'description', 'alt_text', 'device_model', 'player_name', 'player_version', 'evidence_reference'] as $field) {
            if (trim((string) $media->{$field}) === '') return false;
        }

        return $media->publication_permission_at && $media->tested_at && !$media->tested_at->isFuture()
            && $this->mediaUrlAllowed($media->media_url, $media->kind);
    }

    public function publicData(string $brand, string $locale): array
    {
        abort_unless(isset(LandingPageContent::BRANDS[$brand]) && in_array($locale, config('app.locales', ['en']), true), 404);
        $incidents = ServiceIncident::query()->where('brand', $brand)->where('locale', $locale)
            ->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('updated_at')->limit(20)->get()
            ->filter(fn ($incident) => in_array($incident->state, ServiceIncident::STATES, true)
                && !$incident->started_at->isFuture()
                && ($incident->state !== 'resolved' || ($incident->resolved_at && $incident->resolved_at->gte($incident->started_at) && !$incident->resolved_at->isFuture())))
            ->map(fn ($incident) => [
                'id' => $incident->id, 'title' => $incident->title, 'body' => $incident->body, 'state' => $incident->state,
                'started_at' => $incident->started_at->toIso8601String(), 'resolved_at' => $incident->resolved_at?->toIso8601String(),
                'updated_at' => $incident->updated_at->toIso8601String(),
            ])->values()->all();
        $reviews = CustomerReview::query()->with('order')->where('brand', $brand)->where('locale', $locale)
            ->where('status', 'published')->whereNotNull('publication_consented_at')->whereNull('withdrawn_at')
            ->whereNotNull('published_at')->where('published_at', '<=', now())->latest('published_at')->limit(30)->get()
            ->filter(fn ($review) => $this->reviewEligible($review))
            ->map(fn ($review) => [
                'id' => $review->id, 'display_name' => $review->display_name, 'rating' => $review->rating,
                'body' => $review->body, 'locale' => $review->locale,
                'published_at' => $review->published_at->toIso8601String(), 'verified_purchase' => true,
            ])->values()->all();
        $media = SetupMedia::query()->where('brand', $brand)->where('locale', $locale)->where('is_published', true)
            ->whereNotNull('published_at')->where('published_at', '<=', now())->latest('published_at')->limit(30)->get()
            ->filter(fn ($entry) => $this->mediaEligible($entry))
            ->map(fn ($entry) => [
                'id' => $entry->id, 'guide_slug' => $entry->guide_slug, 'title' => $entry->title, 'description' => $entry->description,
                'kind' => $entry->kind, 'media_url' => $entry->media_url, 'alt_text' => $entry->alt_text,
                'device_model' => $entry->device_model, 'player_name' => $entry->player_name, 'player_version' => $entry->player_version,
                'tested_at' => $entry->tested_at->toIso8601String(), 'published_at' => $entry->published_at->toIso8601String(),
            ])->values()->all();

        return compact('incidents', 'reviews', 'media');
    }
}
