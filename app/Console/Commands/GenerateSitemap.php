<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\URL as LaravelURL;

class GenerateSitemap extends Command
{
    protected $signature = 'generate:sitemap';
    protected $description = 'Generate multilingual sitemap with hreflang tags';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Your supported locales
        $locales = ['en', 'fr', 'it'];

        // Your route slugs
        $routes = [
            '', // home
            'about',
            'faqs',
            'buy-now-panel',
            'contact',
            'buynow',
            'pricing',
            'movies',
            'packages',
            'reseller-panel',
            'iptv-applications',
            'trending',
        ];

        foreach ($routes as $route) {

            // Generate main URL for default language 'en' WITHOUT /en prefix
            $defaultUrl = $route === '' ? '/' : "/$route";
            $fullDefaultUrl = LaravelURL::to($defaultUrl);

            $urlItem = Url::create($fullDefaultUrl)
                ->setPriority(0.8)
                ->setChangeFrequency('weekly')
                ->setLastModificationDate(Carbon::now());

            // Add hreflang links for all locales, default language WITHOUT prefix
            foreach ($locales as $altLocale) {
                if ($altLocale === 'en') {
                    // For English, no prefix
                    $altPath = $route === '' ? '/' : "/$route";
                } else {
                    // For other languages, prefix with locale
                    $altPath = $route === '' ? "/$altLocale" : "/$altLocale/$route";
                }

                $urlItem->addAlternate(LaravelURL::to($altPath), $altLocale);
            }

            $sitemap->add($urlItem);
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✅ Sitemap with hreflang generated successfully!');
    }
}
