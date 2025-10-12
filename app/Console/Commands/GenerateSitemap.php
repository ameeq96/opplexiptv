<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
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

        // Public named routes only (from your routes)
        $namedRoutes = [
            'home',
            'about',
            'pricing',
            'movies',
            'reseller-panel',
            'packages',
            'iptv-applications',
            'faqs',
            'contact',
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

                $cluster[$loc] = $href;
            }

            // Canonical = default locale version (respects hideDefaultLocaleInURL)
            $canonicalLoc = in_array($defaultLocale, $locales, true) ? $defaultLocale : $locales[0];
            $canonicalUrl = $cluster[$canonicalLoc] ?? reset($cluster);

            // <url> node
            $urlNode = $xml->addChild('url');
            $urlNode->addChild('loc', htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'));
            $urlNode->addChild('lastmod', now()->toAtomString());
            $urlNode->addChild('changefreq', 'weekly');
            $urlNode->addChild('priority', '0.8');

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

        // Write to public/sitemap.xml
        $path = public_path('sitemap.xml');
        File::put($path, $xml->asXML());

        $this->info("Sitemap generated at: {$path}");
        return self::SUCCESS;
    }
}
