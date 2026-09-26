<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackagesSeeder extends Seeder
{
    public function run(): void
    {
        // Common features
        $iptvFeatures = [
            __('messages.no_buffer'),
            __('messages.support_24_7'),
            __('messages.regular_updates'),
            __('messages.quality_content'),
        ];
        $resellerFeatures = [
            __('messages.uptime'),
            __('messages.no_credit_expiry'),
            __('messages.unlimited_trials'),
            __('messages.no_subreseller'),
        ];

        // -------- IPTV (type=iptv, vendor=opplex|starshare) --------
        $iptv = [
            // Opplex
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Monthly',     'display_price'=>'$2.99 / 1 month',   'price_amount'=>2.99,  'duration_months'=>1,  'sort_order'=>1, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'3 Months',    'display_price'=>'$7.99 / 3 months',  'price_amount'=>7.99,  'duration_months'=>3,  'sort_order'=>2, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Half Yearly', 'display_price'=>'$14.99 / 6 months', 'price_amount'=>14.99, 'duration_months'=>6,  'sort_order'=>3, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Yearly',      'display_price'=>'$23.99 / 12 months','price_amount'=>23.99, 'duration_months'=>12, 'sort_order'=>4, 'icon'=>'bi-router', 'features'=>$iptvFeatures],

            // Starshare
            ['type'=>'iptv','vendor'=>'starshare','title'=>'Monthly',     'display_price'=>'$4.50 / 1 month',   'price_amount'=>4.50,  'duration_months'=>1,  'sort_order'=>1, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'starshare','title'=>'3 Months',    'display_price'=>'$11.99 / 3 months', 'price_amount'=>11.99, 'duration_months'=>3,  'sort_order'=>2, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'starshare','title'=>'Half Yearly', 'display_price'=>'$21.99 / 6 months', 'price_amount'=>21.99, 'duration_months'=>6,  'sort_order'=>3, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
            ['type'=>'iptv','vendor'=>'starshare','title'=>'Yearly',      'display_price'=>'$39.99 / 12 months','price_amount'=>39.99, 'duration_months'=>12, 'sort_order'=>4, 'icon'=>'bi-router', 'features'=>$iptvFeatures],
        ];

        // Additional monthly IPTV brands, shown with the existing pricing-card design.
        $catalogPlans = [
            // Basic
            ['type'=>'iptv','vendor'=>'starshare','title'=>'FILEX IPTV', 'display_price'=>'$4.50 / 1 month', 'price_amount'=>4.50, 'duration_months'=>1, 'sort_order'=>10, 'icon'=>'bi-router', 'features'=>[
                'Basic Plan',
                '17,000+ channels',
                '24/7 support',
                '24-hour free trial',
                '250,000+ movies and series',
                'Fast HD-quality streaming',
                'Budget-friendly',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Opplex iptv', 'display_price'=>'$2.99 / 1 month', 'price_amount'=>2.99, 'duration_months'=>1, 'sort_order'=>11, 'icon'=>'bi-router', 'features'=>[
                'Basic Plan',
                '15,000+ channels',
                '12-hour free trial',
                '70,000+ movies and series',
                'Budget-friendly',
                'Fast activation with 24/7 support',
            ]],

            // Standard
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Crystal IPTV', 'display_price'=>'From $10 / month', 'price_amount'=>10.00, 'duration_months'=>1, 'sort_order'=>20, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '28,000+ live TV channels',
                '150,000+ movies and series',
                'Full HD and 4K quality',
                '24/7 support',
                'Anti-buffering and anti-freezing',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Loin ott iptv', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>21, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '20,000+ live TV channels',
                '24/7 support',
                '24-hour free trial',
                '150,000+ movies and series',
                '4K HD quality',
                'Buffer-free support across devices',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Extra IPTV', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>22, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '18,000+ channels',
                '24/7 support',
                '24-hour free trial',
                'Smooth playback',
                '4K quality',
                'Multi-device support',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Mega Ott IPTV', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>23, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                'Live TV channels',
                'Movies and series',
                'Buffer-free streaming',
                'Worldwide content',
                '24/7 support',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'GEO world iptv', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>24, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '15,000+ live TV channels',
                '24/7 support',
                '24-hour free trial',
                '100,000+ movies and series',
                'All-device support',
                '4K HD quality',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Oxynet IPTV', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>25, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '15,000+ channels',
                '24-hour free trial',
                '70,000+ movies and series',
                '4K HD content',
                '24/7 support',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'RYNEX IPTV', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>26, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '15,000+ channels',
                '24/7 support',
                '80,000+ movies and series',
                'HD quality',
                'All-device support',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Cloud iptv', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>27, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                'Live TV channels',
                '24/7 support',
                '24-hour free trial',
                'Movies and series',
                '4K HD quality',
                'Buffer-free streaming',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'B1G IPTV', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>28, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '27,000+ channels',
                '24/7 support',
                '6-hour free trial',
                '100,000+ movies and series',
                '4K HD quality',
                'Buffer-free and freeze-free streaming',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'ASTV IPTV', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>29, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                '20,000+ live TV channels',
                'Unlimited movies and series',
                'HD and 4K quality',
                '24/7 support',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'starshare','title'=>'Star Share', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>30, 'icon'=>'bi-router', 'features'=>[
                'Standard Plan',
                'Live TV channels',
                'Movies and series',
                'HD quality',
                'All-device support',
                '24/7 support',
                '24-hour free trial',
            ]],

            // Premium
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Diamond IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>40, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '30,000+ live TV channels',
                'Unlimited movies and series',
                'Buffer-free streaming',
                '4K HD quality',
                '24/7 support',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'NEO 4K', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>41, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '30,000+ live TV channels',
                '150,000+ movies and series',
                'Full HD and 4K quality',
                '24/7 support',
                'Only reseller panel available',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Tivi one', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>42, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '30,000+ live TV channels',
                'Movies and series',
                '4K HD quality',
                'Worldwide content',
                '24/7 support',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Dino IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>43, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '30,000+ live TV channels',
                '150,000+ movies and series',
                'Full HD and 4K quality',
                '24/7 support',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'XTRA IPTV (DSTV)', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>44, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '10,000+ live TV channels',
                '24/7 support',
                '50,000+ movies',
                'HD and 4K resolution',
                'Smooth, buffer-free streaming',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'TS4K STROM IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>45, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '25,000+ channels',
                'Global content',
                '150,000+ movies and series',
                '4K Ultra HD quality',
                'Buffer-free streaming',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'TREX IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>46, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '550,000+ live TV channels',
                '250,000+ movies and series',
                'All-device support',
                '4K Ultra HD quality',
                'Worldwide content',
                '24/7 support',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Sky gilas GTV', 'display_price'=>'From $10 / month', 'price_amount'=>10.00, 'duration_months'=>1, 'sort_order'=>47, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                'International channels',
                'Buffer-free and freeze-free streaming',
                '24/7 support',
                '24-hour free trial',
                'Movies and series',
                'All-device support',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'STRONG 8K IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>48, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '30,000+ channels',
                '150,000+ movies and series',
                'Global content',
                'HD and 4K quality',
                'Buffer-free streaming',
                'Only reseller panel available',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Plixi IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>49, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '24/7 support',
                '10,000+ channels',
                '10,000+ movies and series',
                '4K Ultra HD quality',
                'All-device support',
                '24-hour free trial',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'PROMAX Ott IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>50, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '20,000+ live TV channels',
                '24/7 support',
                '24-hour free trial',
                '100,000+ movies and series',
                '4K quality',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Hello Sky IPTV', 'display_price'=>'From $15 / month', 'price_amount'=>15.00, 'duration_months'=>1, 'sort_order'=>51, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                'Live TV channels',
                '24/7 support',
                '24-hour free trial',
                'Movies, series, kids and sports',
                '4K HD content',
                'Buffer-free and freeze-free streaming',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Golden IPTV (GOTT)', 'display_price'=>'From $10 / month', 'price_amount'=>10.00, 'duration_months'=>1, 'sort_order'=>52, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '27,000+ live TV channels',
                'Buffer-free and freeze-free streaming',
                '24-hour free trial',
                '200,000+ movies and series',
                'All-device support',
                'HD and 4K quality',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Enfinty IPTV', 'display_price'=>'From $8 / month', 'price_amount'=>8.00, 'duration_months'=>1, 'sort_order'=>53, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                'HD channels',
                '24/7 support',
                '24-hour free trial',
                'Worldwide movies and series',
                '4K HD quality',
                'Buffer-free streaming',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Dream 4k premium', 'display_price'=>'From $10 / month', 'price_amount'=>10.00, 'duration_months'=>1, 'sort_order'=>54, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '30,000+ channels',
                '24/7 support',
                '24-hour free trial',
                '150,000+ movies and series',
                'High-resolution 4K quality',
                'Buffer-free streaming',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'Eagle 4k iptv', 'display_price'=>'From $10 / month', 'price_amount'=>10.00, 'duration_months'=>1, 'sort_order'=>55, 'icon'=>'bi-router', 'features'=>[
                'Most Premium Plan',
                'True 8K streaming',
                '24/7 support',
                '24-hour free trial',
                '30,000+ channels',
                '100,000+ movies and series',
                '4K HD quality',
            ]],
            ['type'=>'iptv','vendor'=>'opplex','title'=>'5G Live IPTV (Zaintv)', 'display_price'=>'From $10 / month', 'price_amount'=>10.00, 'duration_months'=>1, 'sort_order'=>56, 'icon'=>'bi-router', 'features'=>[
                'Premium Plan',
                '10,000+ channels',
                '24/7 support',
                '24-hour free trial',
                '40,000+ movies and series',
                '4K Ultra HD quality',
                'Buffer-free and freeze-free streaming',
            ]],
        ];

        $previousCatalogPlans = $catalogPlans;
        $catalogPlanTemplates = [];
        foreach ($previousCatalogPlans as $plan) {
            $catalogPlanTemplates[$plan['title']] = $plan;
        }

        // PKR source prices supplied on 26-Sep-2026. The 25-Sep-2026 SBP
        // weighted-average offer rate was PKR 277.3072 per USD.
        $pkrPerUsd = 277.3072;
        $requestedCatalogPlans = [
            ['title'=>'StreamBuzz IPTV', 'template'=>null, 'source_pkr'=>350, 'duration_months'=>1],
            ['title'=>'ZUM TV', 'template'=>null, 'source_pkr'=>400, 'duration_months'=>1],
            ['title'=>'Boss IPTV', 'template'=>null, 'source_pkr'=>300, 'duration_months'=>1],
            ['title'=>'Golden IPTV (GOTT)', 'template'=>'Golden IPTV (GOTT)', 'source_pkr'=>650, 'duration_months'=>1],
            ['title'=>'Loin OTT IPTV', 'template'=>'Loin ott iptv', 'source_pkr'=>4000, 'duration_months'=>12],
            ['title'=>'Rolex IPTV', 'template'=>null, 'source_pkr'=>200, 'duration_months'=>1],
            ['title'=>'Fiber Stream IPTV', 'template'=>null, 'source_pkr'=>250, 'duration_months'=>1],
            ['title'=>'Hexa IPTV', 'template'=>null, 'source_pkr'=>200, 'duration_months'=>1],
            ['title'=>'Enfinty IPTV', 'template'=>'Enfinty IPTV', 'source_pkr'=>400, 'duration_months'=>1],
            ['title'=>'Eagle 4K IPTV', 'template'=>'Eagle 4k iptv', 'source_pkr'=>600, 'duration_months'=>1],
            ['title'=>'ASTV IPTV', 'template'=>'ASTV IPTV', 'source_pkr'=>450, 'duration_months'=>1],
            ['title'=>'Trix IPTV', 'template'=>null, 'source_pkr'=>8000, 'duration_months'=>12],
            ['title'=>'5G IPTV', 'template'=>'5G Live IPTV (Zaintv)', 'source_pkr'=>500, 'duration_months'=>1],
            ['title'=>'Tivi One', 'template'=>'Tivi one', 'source_pkr'=>7000, 'duration_months'=>12],
            ['title'=>'TS4K STROM IPTV', 'template'=>'TS4K STROM IPTV', 'source_pkr'=>500, 'duration_months'=>1],
            ['title'=>'GEO World IPTV', 'template'=>'GEO world iptv', 'source_pkr'=>300, 'duration_months'=>1],
            ['title'=>'Tele TV', 'template'=>null, 'source_pkr'=>250, 'duration_months'=>1],
            ['title'=>'POP Live', 'template'=>null, 'source_pkr'=>200, 'duration_months'=>1],
            ['title'=>'Sky Glass IPTV', 'template'=>'Sky gilas GTV', 'source_pkr'=>600, 'duration_months'=>1],
            ['title'=>'Cloud IPTV', 'template'=>'Cloud iptv', 'source_pkr'=>500, 'duration_months'=>1],
            ['title'=>'TREX IPTV', 'template'=>'TREX IPTV', 'source_pkr'=>6500, 'duration_months'=>12],
            ['title'=>'ZainTV', 'template'=>'5G Live IPTV (Zaintv)', 'source_pkr'=>500, 'duration_months'=>1],
            ['title'=>'B1G IPTV', 'template'=>'B1G IPTV', 'source_pkr'=>350, 'duration_months'=>1],
        ];

        // Filex and Opplex prices/content remain exactly as configured above.
        $catalogPlans = [
            $catalogPlanTemplates['FILEX IPTV'] + ['durations' => [1, 3, 6, 12], 'template_title' => 'FILEX IPTV'],
            $catalogPlanTemplates['Opplex iptv'] + ['durations' => [1, 3, 6, 12], 'template_title' => 'Opplex iptv'],
        ];

        foreach ($requestedCatalogPlans as $index => $requestedPlan) {
            $templateTitle = $requestedPlan['template'];
            $plan = $templateTitle !== null
                ? $catalogPlanTemplates[$templateTitle]
                : [
                    'type' => 'iptv',
                    'vendor' => 'opplex',
                    'icon' => 'bi-router',
                    'features' => array_merge(['Standard Plan'], $iptvFeatures),
                ];
            $durationMonths = $requestedPlan['duration_months'];
            $price = (float) ceil(($requestedPlan['source_pkr'] * 3) / $pkrPerUsd);

            if ($durationMonths === 12) {
                $plan['features'] = array_values(array_unique(array_merge(
                    $plan['features'],
                    ['Yearly subscription only (monthly plan unavailable)']
                )));
            }

            $catalogPlans[] = array_merge($plan, [
                'title' => $requestedPlan['title'],
                'display_price' => '$' . number_format($price, 0) . ' / ' . ($durationMonths === 1 ? '1 month' : '12 months'),
                'price_amount' => $price,
                'duration_months' => $durationMonths,
                'sort_order' => 20 + $index,
                'durations' => $durationMonths === 12 ? [12] : [1, 3, 6, 12],
                'template_title' => $templateTitle ?? $requestedPlan['title'],
            ]);
        }

        $catalogDurations = [
            1 => null,
            3 => '3 Months',
            6 => 'Half Yearly',
            12 => 'Yearly',
        ];

        $existingServicePrices = [
            'Opplex iptv' => [1 => 2.99, 3 => 7.99, 6 => 14.99, 12 => 23.99],
            'FILEX IPTV' => [1 => 4.50, 3 => 11.99, 6 => 21.99, 12 => 39.99],
        ];

        $catalogLogos = [
            'FILEX IPTV' => 'images/providers/filex-iptv.webp',
            'Opplex iptv' => 'images/providers/opplex-iptv.webp',
            'Crystal IPTV' => 'images/providers/crystal-iptv.webp',
            'Loin ott iptv' => 'images/providers/loin-ott-iptv.webp',
            'Extra IPTV' => 'images/providers/extra-iptv.webp',
            'Mega Ott IPTV' => 'images/providers/mega-ott-iptv.webp',
            'GEO world iptv' => 'images/providers/geo-world-iptv.webp',
            'Oxynet IPTV' => 'images/providers/oxynet-iptv.webp',
            'RYNEX IPTV' => 'images/providers/rynex-iptv.webp',
            'Cloud iptv' => 'images/providers/cloud-iptv.webp',
            'B1G IPTV' => 'images/providers/b1g-iptv.webp',
            'ASTV IPTV' => 'images/providers/astv-iptv.webp',
            'Star Share' => 'images/providers/star-share.webp',
            'Diamond IPTV' => 'images/providers/diamond-iptv.webp',
            'NEO 4K' => 'images/providers/neo-4k.webp',
            'Tivi one' => 'images/providers/tivi-one.webp',
            'Dino IPTV' => 'images/providers/dino-iptv.webp',
            'XTRA IPTV (DSTV)' => 'images/providers/xtra-iptv-dstv.webp',
            'TS4K STROM IPTV' => 'images/providers/ts4k-strom-iptv.webp',
            'TREX IPTV' => 'images/providers/trex-iptv.webp',
            'Sky gilas GTV' => 'images/providers/sky-gilas-gtv.webp',
            'STRONG 8K IPTV' => 'images/providers/strong-8k-iptv.webp',
            'Plixi IPTV' => 'images/providers/plixi-iptv.webp',
            'PROMAX Ott IPTV' => 'images/providers/promax-ott-iptv.webp',
            'Hello Sky IPTV' => 'images/providers/hello-sky-iptv.webp',
            'Golden IPTV (GOTT)' => 'images/providers/golden-iptv-gott.webp',
            'Enfinty IPTV' => 'images/providers/enfinty-iptv.webp',
            'Dream 4k premium' => 'images/providers/dream-4k-premium.webp',
            'Eagle 4k iptv' => 'images/providers/eagle-4k-iptv.webp',
            '5G Live IPTV (Zaintv)' => 'images/providers/5g-live-iptv.webp',
        ];

        $catalogFreeTrials = [
            'FILEX IPTV' => 24,
            'Opplex iptv' => 12,
            'Crystal IPTV' => 24,
            'Loin ott iptv' => 24,
            'Extra IPTV' => 24,
            'Mega Ott IPTV' => 24,
            'GEO world iptv' => 24,
            'Oxynet IPTV' => 24,
            'Cloud iptv' => 24,
            'B1G IPTV' => 6,
            'ASTV IPTV' => 24,
            'Star Share' => 24,
            'Diamond IPTV' => 24,
            'Tivi one' => 24,
            'Dino IPTV' => 24,
            'XTRA IPTV (DSTV)' => 24,
            'TS4K STROM IPTV' => 24,
            'TREX IPTV' => 24,
            'Sky gilas GTV' => 24,
            'Plixi IPTV' => 24,
            'PROMAX Ott IPTV' => 24,
            'Hello Sky IPTV' => 24,
            'Golden IPTV (GOTT)' => 24,
            'Enfinty IPTV' => 24,
            'Dream 4k premium' => 24,
            'Eagle 4k iptv' => 24,
            '5G Live IPTV (Zaintv)' => 24,
        ];

        $featuredServices = ['Opplex iptv', 'FILEX IPTV', 'Eagle 4K IPTV'];

        foreach (['', ' - 3 Months', ' - Half Yearly', ' - Yearly'] as $suffix) {
            Package::where(['type' => 'iptv', 'vendor' => 'starshare', 'title' => 'Star Sher' . $suffix])
                ->update(['title' => 'Star Share' . $suffix]);

            Package::where(['type' => 'iptv', 'vendor' => 'opplex', 'title' => '🦖Dino IPTV' . $suffix])
                ->update(['title' => 'Dino IPTV' . $suffix]);
        }

        $activeCatalogKeys = [];
        foreach ($catalogPlans as $catalogPlan) {
            $allowedDurations = $catalogPlan['durations'];
            $templateTitle = $catalogPlan['template_title'];
            $baseDurationMonths = (int) $catalogPlan['duration_months'];
            unset($catalogPlan['durations'], $catalogPlan['template_title']);

            foreach ($catalogDurations as $months => $durationTitle) {
                if (! in_array($months, $allowedDurations, true)) {
                    continue;
                }

                $existingPrice = $existingServicePrices[$catalogPlan['title']][$months] ?? null;
                $price = $existingPrice
                    ?? ((float) $catalogPlan['price_amount'] * ($months / $baseDurationMonths));
                $priceNumber = $existingPrice !== null
                    ? number_format($price, 2, '.', '')
                    : rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');
                $durationText = $months === 1 ? '1 month' : $months . ' months';
                $generatedTitle = $durationTitle
                    ? $catalogPlan['title'] . ' - ' . $durationTitle
                    : $catalogPlan['title'];

                $activeCatalogKeys[$catalogPlan['vendor'] . '|' . $generatedTitle] = true;

                $iptv[] = array_merge($catalogPlan, [
                    'title' => $generatedTitle,
                    'display_price' => '$' . $priceNumber . ' / ' . $durationText,
                    'price_amount' => $price,
                    'duration_months' => $months,
                    'sort_order' => ((int) $catalogPlan['sort_order'] * 10) + array_search($months, array_keys($catalogDurations), true),
                    'icon' => $catalogLogos[$catalogPlan['title']]
                        ?? $catalogLogos[$templateTitle]
                        ?? 'bi-router',
                    'badge_key' => match ($months) {
                        6 => 'most_popular',
                        12 => 'best_value',
                        default => null,
                    },
                    'is_featured' => in_array($catalogPlan['title'], $featuredServices, true),
                    'is_available' => true,
                    'free_trial_hours' => $catalogFreeTrials[$catalogPlan['title']]
                        ?? $catalogFreeTrials[$templateTitle]
                        ?? null,
                    'instant_activation' => $catalogPlan['title'] === 'Opplex iptv',
                    'connection_prices' => $catalogPlan['title'] === 'FILEX IPTV' && $months === 12
                        ? Package::FILEX_YEARLY_CONNECTION_PRICES
                        : null,
                ]);
            }
        }

        foreach (array_merge($previousCatalogPlans, $catalogPlans) as $previousPlan) {
            foreach ($catalogDurations as $durationTitle) {
                $previousTitle = $durationTitle
                    ? $previousPlan['title'] . ' - ' . $durationTitle
                    : $previousPlan['title'];
                $previousKey = $previousPlan['vendor'] . '|' . $previousTitle;

                if (! isset($activeCatalogKeys[$previousKey])) {
                    Package::where([
                        'type' => 'iptv',
                        'vendor' => $previousPlan['vendor'],
                        'title' => $previousTitle,
                    ])->update(['active' => false, 'is_available' => false]);
                }
            }
        }

        foreach ($iptv as $p) {
            $package = Package::updateOrCreate(
                ['type'=>$p['type'],'vendor'=>$p['vendor'],'title'=>$p['title']],
                $p + ['active'=>true]
            );

            Package::where([
                'type' => $p['type'],
                'vendor' => $p['vendor'],
                'title' => $p['title'],
            ])->where('id', '<>', $package->id)
                ->update(['active' => false, 'is_available' => false]);
        }

        // -------- Reseller (type=reseller, vendor=opplex|starshare) --------
        $resellers = [
            // Opplex reseller (aapke purane numbers)
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Starter Reseller Package',  'credits'=>20,  'display_price'=>'$16.99 / 20 Credits',   'price_amount'=>16.99,  'sort_order'=>1, 'icons'=>['images/icons/service-1.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'0ms'],
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Essential Reseller Bundle', 'credits'=>50,  'display_price'=>'$40.99 / 50 Credits',   'price_amount'=>40.99,  'sort_order'=>2, 'icons'=>['images/icons/service-2.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'150ms'],
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Pro Reseller Suite',        'credits'=>100, 'display_price'=>'$77.99 / 100 Credits',  'price_amount'=>77.99,  'sort_order'=>3, 'icons'=>['images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'300ms'],
            ['type'=>'reseller','vendor'=>'opplex','title'=>'Advanced Reseller Toolkit', 'credits'=>200, 'display_price'=>'$149.99 / 200 Credits', 'price_amount'=>149.99, 'sort_order'=>4, 'icons'=>['images/icons/service-1.svg','images/icons/service-2.svg','images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'450ms'],

            // Starshare reseller (aapke diye gaye rates)
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Starter Reseller Package',  'credits'=>50,  'display_price'=>'$134.99 / 50 Credits',  'price_amount'=>134.99, 'sort_order'=>1, 'icons'=>['images/icons/service-1.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'0ms'],
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Essential Reseller Bundle', 'credits'=>100, 'display_price'=>'$249.99 / 100 Credits', 'price_amount'=>249.99, 'sort_order'=>2, 'icons'=>['images/icons/service-2.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'150ms'],
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Pro Reseller Suite',        'credits'=>200, 'display_price'=>'$480.00 / 200 Credits', 'price_amount'=>480.00, 'sort_order'=>3, 'icons'=>['images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'300ms'],
            ['type'=>'reseller','vendor'=>'starshare','title'=>'Advanced Reseller Toolkit', 'credits'=>300, 'display_price'=>'$659.99 / 300 Credits', 'price_amount'=>659.99, 'sort_order'=>4, 'icons'=>['images/icons/service-1.svg','images/icons/service-2.svg','images/icons/service-3.svg'], 'icon'=>'bi-router', 'features'=>$resellerFeatures, 'button_link'=>'checkout','delay'=>'450ms'],
        ];

        foreach ($resellers as $r) {
            Package::updateOrCreate(
                ['type'=>$r['type'],'vendor'=>$r['vendor'],'title'=>$r['title']],
                $r + ['active'=>true]
            );
        }
    }
}
