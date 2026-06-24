<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate XML sitemap with hreflang alternates for all locales';

    public function handle()
    {
        // Locales from config
        $locales       = array_keys(config('laravellocalization.supportedLocales') ?? ['en' => []]);
        $defaultLocale = LaravelLocalization::getDefaultLocale();
        $hideDefault   = (bool) (config('laravellocalization.hideDefaultLocaleInURL') ?? false);

        // Public, indexable named routes (localized, with hreflang clusters).
        $namedRoutes = [
            'home',
            'about',
            'pricing',
            'iptv-subscription-service',
            'reseller-panel',
            'iptv-applications',
            'faqs',
            'contact',
            'shop',
            'movies',
            'activate',
            'blogs.index',
            'digital.shop',
            'terms-of-service',
            'privacy-policy',
            'refund-policy',
        ];

        // XML init - correct namespaces (note: xmlns:xhtml on root only)
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->addAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        foreach ($namedRoutes as $routeName) {
            if (!Route::has($routeName)) {
                $this->warn("Route missing: {$routeName}");
                continue;
            }

            // Relative path for this named route (e.g. '/', 'about')
            $base = route($routeName, [], false); // relative
            if ($base === '') { $base = '/'; }

            // Build SAME-PAGE alternates per locale
            $cluster = [];
            foreach ($locales as $loc) {
                if ($loc === $defaultLocale && $hideDefault) {
                    // Force non-localized absolute URL for default locale
                    $abs   = url($base); // absolute current route
                    $href  = LaravelLocalization::getNonLocalizedURL($abs);
                } else {
                    // Localized absolute URL for non-default (or if not hiding default)
                    $href = LaravelLocalization::getLocalizedURL($loc, $base, [], true);
                }

                // Normalize any accidental double slashes before query
                $href = preg_replace('~(?<!:)//+~', '/', $href);
                $href = Str::startsWith($href, 'http') ? $href : url($href);
                $href = $this->withoutQueryAndFragment($href);

                if ($href === null) {
                    continue;
                }

                $cluster[$loc] = $href;
            }

            if ($cluster === []) {
                $this->warn("Skipped route due to invalid URL cluster: {$routeName}");
                continue;
            }

            // Canonical = default locale version (respects hideDefaultLocaleInURL)
            $canonicalLoc = in_array($defaultLocale, $locales, true) ? $defaultLocale : $locales[0];
            $canonicalUrl = $cluster[$canonicalLoc] ?? reset($cluster);

            // <url> node
            $urlNode = $xml->addChild('url');
            $urlNode->addChild('loc', htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'));
            $urlNode->addChild('lastmod', now()->toAtomString());
            $urlNode->addChild('changefreq', $routeName === 'home' ? 'daily' : 'weekly');
            $urlNode->addChild('priority', $routeName === 'home' ? '1.0' : '0.8');

            // xhtml:link alternates (no per-tag xmlns attributes!)
            foreach ($cluster as $lg => $href) {
                $link = $urlNode->addChild('xhtml:link', null, 'http://www.w3.org/1999/xhtml');
                $link->addAttribute('rel', 'alternate');
                $link->addAttribute('hreflang', $lg);
                $link->addAttribute('href', $href);
            }

            // x-default -> canonical
            $xdef = $urlNode->addChild('xhtml:link', null, 'http://www.w3.org/1999/xhtml');
            $xdef->addAttribute('rel', 'alternate');
            $xdef->addAttribute('hreflang', 'x-default');
            $xdef->addAttribute('href', $canonicalUrl);
        }

        // Dynamic, DB-driven URLs (canonical only). Wrapped so a missing DB
        // never breaks the static portion of the sitemap.
        $this->addBlogPosts($xml, $defaultLocale);
        $this->addDigitalProducts($xml);

        // Write to public/sitemap.xml
        $path = public_path('sitemap.xml');
        File::put($path, $xml->asXML());

        $this->info("Sitemap generated at: {$path}");
        return self::SUCCESS;
    }

    /** Add published blog posts (using the default-locale slug as canonical). */
    private function addBlogPosts(\SimpleXMLElement $xml, string $defaultLocale): void
    {
        if (!$this->hasTable('blogs') || !$this->hasTable('blog_translations') || !Route::has('blogs.show')) {
            return;
        }

        try {
            $blogs = \App\Models\Blog::query()
                ->when(method_exists(\App\Models\Blog::class, 'scopePublished'), fn ($q) => $q->published())
                ->with('translations')
                ->get();
        } catch (\Throwable $e) {
            $this->warn('Skipped blog posts in sitemap: ' . $e->getMessage());
            return;
        }

        foreach ($blogs as $blog) {
            $translation = optional($blog->translations)->firstWhere('locale', $defaultLocale)
                ?? optional($blog->translations)->first();

            $slug = $translation->slug ?? null;
            if (!$slug) {
                continue;
            }

            $loc = $this->withoutQueryAndFragment(route('blogs.show', $slug));
            if ($loc === null) {
                continue;
            }

            $lastmod = optional($blog->updated_at ?? $blog->published_at)->toAtomString() ?? now()->toAtomString();
            $this->addSimpleUrl($xml, $loc, $lastmod, '0.6');
        }
    }

    /** Add active digital products. */
    private function addDigitalProducts(\SimpleXMLElement $xml): void
    {
        if (!$this->hasTable('digital_products') || !Route::has('digital.product.show')) {
            return;
        }

        try {
            $products = \App\Models\Digital\DigitalProduct::query()
                ->where('is_active', true)
                ->get(['id', 'slug', 'updated_at']);
        } catch (\Throwable $e) {
            $this->warn('Skipped digital products in sitemap: ' . $e->getMessage());
            return;
        }

        foreach ($products as $product) {
            if (empty($product->slug)) {
                continue;
            }

            $loc = $this->withoutQueryAndFragment(route('digital.product.show', $product->slug));
            if ($loc === null) {
                continue;
            }

            $lastmod = optional($product->updated_at)->toAtomString() ?? now()->toAtomString();
            $this->addSimpleUrl($xml, $loc, $lastmod, '0.6');
        }
    }

    private function addSimpleUrl(\SimpleXMLElement $xml, string $loc, string $lastmod, string $priority): void
    {
        $urlNode = $xml->addChild('url');
        $urlNode->addChild('loc', htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'));
        $urlNode->addChild('lastmod', $lastmod);
        $urlNode->addChild('changefreq', 'weekly');
        $urlNode->addChild('priority', $priority);
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private function withoutQueryAndFragment(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $base = $parts['scheme'] . '://';

        if (isset($parts['user'])) {
            $base .= $parts['user'];
            if (isset($parts['pass'])) {
                $base .= ':' . $parts['pass'];
            }
            $base .= '@';
        }

        $base .= $parts['host'];

        if (isset($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        $path = $parts['path'] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        return $base . $path;
    }
}
