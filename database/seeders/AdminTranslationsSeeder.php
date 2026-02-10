<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Lang, Schema};
use App\Models\{ShopProduct, HomeService, Testimonial, ChannelLogo, MenuItem, Package, PricingSection, FooterSetting, FooterLink, SocialLink};

class AdminTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $locales = config('app.locales', ['en']);
        $fallback = config('app.fallback_locale', 'en');

        if (Schema::hasTable('shop_products')) {
            ShopProduct::with('translations')->get()->each(function (ShopProduct $p) use ($locales, $fallback) {
                $baseName = $p->name;
                foreach ($locales as $locale) {
                    $p->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['name' => $baseName]
                    );
                }
            });
        }

        if (Schema::hasTable('home_services')) {
            $homeMap = [
                0 => ['messages.iptv_packages', 'messages.iptv_packages_desc'],
                1 => ['messages.reseller_panel', 'messages.reseller_panel_desc'],
                2 => ['messages.iptv_sports', 'messages.iptv_sports_desc'],
                3 => ['messages.iptv_vod', 'messages.iptv_vod_desc'],
                4 => ['messages.iptv_devices', 'messages.iptv_devices_desc'],
            ];

            HomeService::with('translations')->get()->each(function (HomeService $s) use ($locales, $homeMap) {
                $keyPair = $homeMap[$s->sort_order] ?? null;
                foreach ($locales as $locale) {
                    $title = $keyPair ? Lang::get($keyPair[0], [], $locale) : $s->title;
                    $desc = $keyPair ? Lang::get($keyPair[1], [], $locale) : $s->description;
                    $s->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['title' => $title, 'description' => $desc]
                    );
                }
            });
        }

        if (Schema::hasTable('testimonials')) {
            Testimonial::with('translations')->get()->each(function (Testimonial $t) use ($locales) {
                $key = 'messages.testimonial_' . ((int) $t->sort_order + 1);
                foreach ($locales as $locale) {
                    $text = Lang::get($key, [], $locale);
                    $t->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['author_name' => $t->author_name, 'text' => $text]
                    );
                }
            });
        }

        if (Schema::hasTable('channel_logos')) {
            ChannelLogo::with('translations')->get()->each(function (ChannelLogo $l) use ($locales, $fallback) {
                foreach ($locales as $locale) {
                    $l->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['alt_text' => null]
                    );
                }
            });
        }

        if (Schema::hasTable('menu_items')) {
            $menuKeyMap = [
                'home' => 'messages.nav_home',
                'our packages' => 'messages.nav_packages',
                'iptv applications' => 'messages.nav_iptv_apps',
                "faq's" => 'messages.nav_faqs',
                'more' => 'messages.nav_services',
                'about us' => 'messages.nav_about_us',
                'about' => 'messages.nav_about',
                'contact us' => 'messages.nav_contact',
                'reseller panel' => 'messages.nav_reseller',
                'pricing' => 'messages.nav_pricing',
                'movies/series' => 'messages.nav_movies_series',
            ];

            MenuItem::with('translations')->get()->each(function (MenuItem $m) use ($locales, $menuKeyMap) {
                $lookup = strtolower(trim($m->label));
                $key = $menuKeyMap[$lookup] ?? null;
                foreach ($locales as $locale) {
                    $label = $key ? Lang::get($key, [], $locale) : $m->label;
                    $m->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['label' => $label]
                    );
                }
            });
        }

        if (Schema::hasTable('packages')) {
            $iptvTitleMap = [
                1 => 'messages.buynow.packages.monthly',
                3 => 'messages.buynow.packages.monthly',
                6 => 'messages.buynow.packages.half_yearly',
                12 => 'messages.buynow.packages.yearly',
            ];
            $resellerTitleMap = [
                20 => 'messages.starter_reseller',
                50 => 'messages.essential_reseller',
                100 => 'messages.pro_reseller',
                200 => 'messages.advanced_reseller',
                300 => 'messages.advanced_reseller',
            ];

            Package::with('translations')->get()->each(function (Package $p) use ($locales, $iptvTitleMap, $resellerTitleMap) {
                foreach ($locales as $locale) {
                    $title = $p->title;
                    $features = $p->features;

                    if ($p->type === 'iptv') {
                        $months = (int) ($p->duration_months ?? 0);
                        if (isset($iptvTitleMap[$months])) {
                            $title = Lang::get($iptvTitleMap[$months], [], $locale);
                        }
                        $features = [
                            Lang::get('messages.no_buffer', [], $locale),
                            Lang::get('messages.support_24_7', [], $locale),
                            Lang::get('messages.regular_updates', [], $locale),
                            Lang::get('messages.quality_content', [], $locale),
                        ];
                    } elseif ($p->type === 'reseller') {
                        $credits = (int) ($p->credits ?? 0);
                        if (isset($resellerTitleMap[$credits])) {
                            $title = Lang::get($resellerTitleMap[$credits], [], $locale);
                        }
                        $features = [
                            Lang::get('messages.uptime', [], $locale),
                            Lang::get('messages.no_credit_expiry', [], $locale),
                            Lang::get('messages.unlimited_trials', [], $locale),
                            Lang::get('messages.no_subreseller', [], $locale),
                        ];
                    }

                    $p->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['title' => $title, 'features' => $features]
                    );
                }
            });
        }

        if (Schema::hasTable('pricing_sections')) {
            PricingSection::with('translations')->get()->each(function (PricingSection $p) use ($locales) {
                foreach ($locales as $locale) {
                    $credit = '<span style=\"color:red;\">1 ' . Lang::get('messages.credit', [], $locale) . '</span> = ' . Lang::get('messages.1_month', [], $locale)
                        . ' &nbsp;<i class=\"fa fa-plus\"></i>&nbsp; '
                        . '<span style=\"color:red;\">5 ' . Lang::get('messages.credit', [], $locale) . '</span> = ' . Lang::get('messages.6_months', [], $locale)
                        . ' &nbsp;<i class=\"fa fa-plus\"></i>&nbsp; '
                        . '<span style=\"color:red;\">10 ' . Lang::get('messages.credit', [], $locale) . '</span> = ' . Lang::get('messages.12_months', [], $locale);

                    $p->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'heading' => Lang::get('messages.pricing_heading', [], $locale),
                            'subheading' => Lang::get('messages.pricing_subheading', [], $locale),
                            'show_reseller_label' => Lang::get('messages.show_reseller_packages', [], $locale),
                            'credit_info' => $credit,
                        ]
                    );
                }
            });
        }

        if (Schema::hasTable('footer_settings')) {
            FooterSetting::with('translations')->get()->each(function (FooterSetting $f) use ($locales) {
                foreach ($locales as $locale) {
                    $f->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'brand_text' => $f->brand_text,
                            'crypto_note' => $f->crypto_note,
                            'address' => Lang::get('messages.footer_address', [], $locale),
                            'rights_text' => Lang::get('messages.footer_rights', [], $locale),
                            'legal_note' => $f->legal_note,
                        ]
                    );
                }
            });
        }

        if (Schema::hasTable('footer_links')) {
            $footerMap = [
                '/pricing' => 'messages.nav_pricing',
                '/packages' => 'messages.nav_packages',
                '/reseller-panel' => 'messages.nav_reseller',
                '/movies' => 'messages.nav_movies_series',
                '/iptv-applications' => 'messages.nav_iptv_apps',
                '/faqs' => 'messages.nav_faqs',
                '/about' => 'messages.nav_about_us',
                '/contact' => 'messages.nav_contact',
                '/' => 'messages.nav_home',
            ];

            FooterLink::with('translations')->get()->each(function (FooterLink $l) use ($locales, $footerMap) {
                $key = $footerMap[$l->url] ?? null;
                foreach ($locales as $locale) {
                    $label = $key ? Lang::get($key, [], $locale) : $l->label;
                    $l->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['label' => $label]
                    );
                }
            });
        }

        if (Schema::hasTable('social_links')) {
            SocialLink::with('translations')->get()->each(function (SocialLink $s) use ($locales, $fallback) {
                foreach ($locales as $locale) {
                    $s->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['platform' => $s->platform]
                    );
                }
            });
        }
    }
}
