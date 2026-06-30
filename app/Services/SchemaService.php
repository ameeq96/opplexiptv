<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use App\Models\Package;

/**
 * Central builder for JSON-LD structured data (SEO / GEO / AEO).
 *
 * Every method returns a plain PHP array (or null when there is no data),
 * which keeps the schema testable and lets callers merge nodes into a
 * single @graph. Use the global `jsonld()` helper to render an array into
 * a <script type="application/ld+json"> tag.
 *
 * Design notes:
 *  - The canonical brand entity is an OnlineStore (a subtype of
 *    Organization) addressed by the stable @id `{site}/#organization`.
 *    Per-page nodes reference it via that @id instead of re-declaring it.
 *  - The WebSite entity is addressed by `{site}/#website`.
 */
class SchemaService
{
    public const NAME     = 'Opplex IPTV';
    public const PHONE    = '+1-639-390-3194';
    public const EMAIL    = 'info@opplexiptv.com';
    public const LOGO     = 'images/opplexiptvlogo.webp';

    /** JSON flags shared by every rendered node. JSON_HEX_TAG prevents `</script>` breakouts. */
    public const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;

    /* ───────────────────────── Identity helpers ───────────────────────── */

    /** Absolute site root with a single trailing slash. */
    public function siteUrl(): string
    {
        return rtrim(url('/'), '/') . '/';
    }

    public function organizationId(): string
    {
        return rtrim(url('/'), '/') . '/#organization';
    }

    public function websiteId(): string
    {
        return rtrim(url('/'), '/') . '/#website';
    }

    /** Reference to the brand entity, for use as provider/publisher. */
    public function organizationRef(): array
    {
        return ['@id' => $this->organizationId()];
    }

    /* ───────────────────────── Global entity graph ────────────────────── */

    /**
     * The site-wide entity graph (OnlineStore + WebSite). Emitted on every
     * public page so AI/answer engines can resolve the brand entity.
     *
     * @param  array<int,array<string,mixed>>  $socials  Footer social links.
     * @return array<string,mixed>
     */
    public function globalGraph(array $socials = []): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph'   => [
                $this->organization($socials),
                $this->website(),
            ],
        ];
    }

    /**
     * The brand entity (OnlineStore is an Organization subtype).
     *
     * @param  array<int,array<string,mixed>>  $socials
     * @return array<string,mixed>
     */
    public function organization(array $socials = []): array
    {
        $node = [
            '@type'       => 'OnlineStore',
            '@id'         => $this->organizationId(),
            'name'        => self::NAME,
            'url'         => $this->siteUrl(),
            'logo'        => asset(self::LOGO),
            'image'       => asset(self::LOGO),
            'description' => 'Opplex IPTV provides premium IPTV subscription services with 12,000+ live TV channels, sports, movies, and VOD in HD and 4K, with apps for every device, a free trial, and 24/7 support.',
            'telephone'   => self::PHONE,
            'email'       => self::EMAIL,
            'areaServed'  => 'Worldwide',
            'contactPoint' => [
                '@type'             => 'ContactPoint',
                'telephone'         => self::PHONE,
                'email'             => self::EMAIL,
                'contactType'       => 'customer support',
                'areaServed'        => 'Worldwide',
                'availableLanguage' => $this->availableLanguages(),
            ],
        ];

        $sameAs = $this->sameAs($socials);
        if ($sameAs !== []) {
            $node['sameAs'] = $sameAs;
        }

        return $node;
    }

    /** @return array<string,mixed> */
    public function website(): array
    {
        return [
            '@type'      => 'WebSite',
            '@id'        => $this->websiteId(),
            'url'        => $this->siteUrl(),
            'name'       => self::NAME,
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'publisher'  => $this->organizationRef(),
            'potentialAction' => [
                '@type'  => 'SearchAction',
                'target' => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => route('movies') . '?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /* ───────────────────────── Page-level nodes ───────────────────────── */

    /**
     * BreadcrumbList from the <x-page-title> breadcrumb array.
     * Crumb shape: ['url' => '/path' (optional), 'label' => '...'].
     *
     * @param  array<int,array<string,mixed>>  $crumbs
     * @return array<string,mixed>|null
     */
    public function breadcrumbList(array $crumbs): ?array
    {
        $items = [];
        $position = 1;

        foreach ($crumbs as $crumb) {
            $label = trim((string) ($crumb['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $item = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => $label,
            ];

            if (!empty($crumb['url'])) {
                $item['item'] = $this->absoluteUrl((string) $crumb['url']);
            }

            $items[] = $item;
            $position++;
        }

        // A single-item breadcrumb (just "Home") adds no value.
        if (count($items) < 2) {
            return null;
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * FAQPage from a list of Q&A items.
     * Item shape: ['question' => '...', 'answer' => 'html or text'].
     *
     * @param  iterable<array<string,mixed>>  $faqs
     * @return array<string,mixed>|null
     */
    public function faqPage(iterable $faqs): ?array
    {
        $entities = [];

        foreach ($faqs as $faq) {
            $question = $this->plain((string) ($faq['question'] ?? ''));
            $answer   = $this->plain((string) ($faq['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type'          => 'Question',
                'name'           => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $answer,
                ],
            ];
        }

        if ($entities === []) {
            return null;
        }

        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    /**
     * HowTo with ordered steps.
     *
     * @param  array<int,array{name:string,text:string,url?:string}>  $steps
     * @return array<string,mixed>|null
     */
    public function howTo(string $name, ?string $description, array $steps): ?array
    {
        $stepNodes = [];
        $position = 1;

        foreach ($steps as $step) {
            $text = $this->plain((string) ($step['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $node = [
                '@type'    => 'HowToStep',
                'position' => $position,
                'name'     => $this->plain((string) ($step['name'] ?? $text)),
                'text'     => $text,
            ];

            if (!empty($step['url'])) {
                $node['url'] = $this->absoluteUrl((string) $step['url']);
            }

            $stepNodes[] = $node;
            $position++;
        }

        if ($stepNodes === []) {
            return null;
        }

        $node = [
            '@context' => 'https://schema.org',
            '@type'    => 'HowTo',
            'name'     => $this->plain($name),
            'step'     => $stepNodes,
        ];

        if ($description !== null && trim($description) !== '') {
            $node['description'] = $this->plain($description);
        }

        return $node;
    }

    /**
     * Service node with an OfferCatalog of plans.
     *
     * @param  array<int,array{name:string,price:int|float|string,priceCurrency?:string,url?:string}>  $offers
     * @return array<string,mixed>
     */
    public function service(string $name, ?string $description, string $url, array $offers = [], string $serviceType = 'IPTV Streaming Service'): array
    {
        $node = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            '@id'         => $this->absoluteUrl($url) . '#service',
            'name'        => $this->plain($name),
            'serviceType' => $serviceType,
            'url'         => $this->absoluteUrl($url),
            'provider'    => $this->organizationRef(),
            'areaServed'  => 'Worldwide',
        ];

        if ($description !== null && trim($description) !== '') {
            $node['description'] = $this->plain($description);
        }

        $catalogItems = [];
        foreach ($offers as $offer) {
            $offerNode = $this->offer($offer);
            if ($offerNode !== null) {
                $catalogItems[] = $offerNode;
            }
        }

        if ($catalogItems !== []) {
            $node['hasOfferCatalog'] = [
                '@type'           => 'OfferCatalog',
                'name'            => $this->plain($name) . ' Plans',
                'itemListElement' => $catalogItems,
            ];
        }

        return $node;
    }

    /**
     * Build offer arrays for IPTV or reseller packages straight from the DB,
     * so prices are clean numerics rather than display strings.
     *
     * @return array<int,array{name:string,price:float,priceCurrency:string}>
     */
    public function packageOffers(string $kind): array
    {
        if (!$this->hasTable('packages')) {
            return [];
        }

        try {
            $rows = Package::query()
                ->where('active', true)
                ->where('type', $kind === 'reseller' ? 'reseller' : 'iptv')
                ->whereIn('vendor', ['opplex', 'starshare'])
                ->orderByRaw('COALESCE(sort_order, duration_months, credits, id)')
                ->with('translations')
                ->get(['id', 'vendor', 'title', 'price_amount', 'duration_months', 'credits']);
        } catch (\Throwable) {
            return [];
        }

        $offers = [];
        foreach ($rows as $row) {
            if ($row->price_amount === null) {
                continue;
            }

            $title = $row->translation()?->title ?: $row->title;

            $offers[] = [
                'name'          => (string) $title,
                'price'         => (float) $row->price_amount,
                'priceCurrency' => $this->currency(),
                'category'      => $kind === 'reseller' ? 'IPTV reseller credits' : 'IPTV subscription',
            ];
        }

        return $offers;
    }

    /** @return array<string,mixed> */
    public function aboutPage(string $title, ?string $description, string $url): array
    {
        $node = [
            '@context' => 'https://schema.org',
            '@type'    => 'AboutPage',
            'url'      => $this->absoluteUrl($url),
            'name'     => $this->plain($title),
            'about'    => $this->organizationRef(),
            'publisher' => $this->organizationRef(),
        ];

        if ($description !== null && trim($description) !== '') {
            $node['description'] = $this->plain($description);
        }

        return $node;
    }

    /** @return array<string,mixed> */
    public function contactPage(string $title, ?string $description, string $url): array
    {
        $node = [
            '@context' => 'https://schema.org',
            '@type'    => 'ContactPage',
            'url'      => $this->absoluteUrl($url),
            'name'     => $this->plain($title),
            'about'    => $this->organizationRef(),
            'publisher' => $this->organizationRef(),
            'mainEntity' => $this->organizationRef(),
        ];

        if ($description !== null && trim($description) !== '') {
            $node['description'] = $this->plain($description);
        }

        return $node;
    }

    /**
     * CollectionPage for listing pages (shop, blog index, movies).
     *
     * @param  array<int,array{name:string,url:string}>  $items
     * @return array<string,mixed>
     */
    public function collectionPage(string $title, ?string $description, string $url, array $items = []): array
    {
        $node = [
            '@context'  => 'https://schema.org',
            '@type'     => 'CollectionPage',
            'url'       => $this->absoluteUrl($url),
            'name'      => $this->plain($title),
            'isPartOf'  => ['@id' => $this->websiteId()],
            'publisher' => $this->organizationRef(),
        ];

        if ($description !== null && trim($description) !== '') {
            $node['description'] = $this->plain($description);
        }

        $listItems = [];
        $position = 1;
        foreach ($items as $item) {
            $name = $this->plain((string) ($item['name'] ?? ''));
            if ($name === '' || empty($item['url'])) {
                continue;
            }
            $listItems[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => $name,
                'url'      => $this->absoluteUrl((string) $item['url']),
            ];
            $position++;
        }

        if ($listItems !== []) {
            $node['mainEntity'] = [
                '@type'           => 'ItemList',
                'itemListElement' => $listItems,
            ];
        }

        return $node;
    }

    /* ──────────────────────────── Internals ───────────────────────────── */

    /**
     * Build a single Offer node from a loose offer array.
     *
     * @param  array<string,mixed>  $offer
     * @return array<string,mixed>|null
     */
    private function offer(array $offer): ?array
    {
        $name  = $this->plain((string) ($offer['name'] ?? ''));
        if ($name === '' || !isset($offer['price'])) {
            return null;
        }

        $node = [
            '@type'         => 'Offer',
            'name'          => $name,
            'price'         => number_format((float) $offer['price'], 2, '.', ''),
            'priceCurrency' => (string) ($offer['priceCurrency'] ?? $this->currency()),
            'availability'  => 'https://schema.org/InStock',
        ];

        if (!empty($offer['category'])) {
            $node['category'] = (string) $offer['category'];
        }

        if (!empty($offer['url'])) {
            $node['url'] = $this->absoluteUrl((string) $offer['url']);
        }

        return $node;
    }

    /**
     * Extract a clean list of profile URLs for sameAs.
     *
     * @param  array<int,array<string,mixed>>  $socials
     * @return array<int,string>
     */
    private function sameAs(array $socials): array
    {
        $urls = [];

        foreach ($socials as $social) {
            $url = trim((string) ($social['url'] ?? ''));
            // sameAs is for brand profiles, not click-to-chat / mail links.
            if ($url === '' || !preg_match('~^https?://~i', $url)) {
                continue;
            }
            if (preg_match('~(wa\.me|api\.whatsapp\.com|mailto:|tel:)~i', $url)) {
                continue;
            }
            $urls[] = $url;
        }

        // Sensible defaults if the footer has no socials configured.
        if ($urls === []) {
            $urls = [
                'https://www.facebook.com/profile.php?id=61565476366548',
                'https://www.instagram.com/oplextv/',
            ];
        }

        // Always include the official X profile so the brand entity resolves there.
        $hasX = false;
        foreach ($urls as $u) {
            if (preg_match('~(?:^|\.)(?:x|twitter)\.com/~i', $u)) {
                $hasX = true;
                break;
            }
        }
        if (!$hasX) {
            $urls[] = 'https://x.com/opplex_iptv';
        }

        return array_values(array_unique($urls));
    }

    /** @return array<int,string> BCP-47 codes of supported locales (Google-preferred). */
    private function availableLanguages(): array
    {
        $locales = array_keys((array) config('laravellocalization.supportedLocales', []));
        $codes = array_values(array_filter(array_map(
            static fn ($code) => is_string($code) && $code !== '' ? str_replace('_', '-', $code) : null,
            $locales
        )));

        return $codes !== [] ? $codes : ['en'];
    }

    private function currency(): string
    {
        return (string) config('services.app.default_currency', 'USD');
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    /** Resolve a relative path / route URL to an absolute URL. */
    private function absoluteUrl(string $url): string
    {
        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }

        return rtrim(url('/'), '/') . '/' . ltrim($url, '/');
    }

    /** Strip HTML, decode entities and collapse whitespace for schema text values. */
    private function plain(string $value): string
    {
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }
}
