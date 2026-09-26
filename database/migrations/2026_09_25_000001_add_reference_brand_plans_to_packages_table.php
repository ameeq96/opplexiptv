<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $plans = [
            ['vendor' => 'starshare', 'title' => 'FILEX IPTV', 'display_price' => 'From $5 / month', 'price_amount' => 5.00, 'sort_order' => 10, 'features' => [
                'Basic Plan', '17,000+ channels', '24/7 support', '24-hour free trial',
                '250,000+ movies and series', 'Fast HD-quality streaming', 'Budget-friendly',
            ]],
            ['vendor' => 'opplex', 'title' => 'Opplex iptv', 'display_price' => 'From $5 / month', 'price_amount' => 5.00, 'sort_order' => 11, 'features' => [
                'Basic Plan', '15,000+ channels', '12-hour free trial', '70,000+ movies and series',
                'Budget-friendly', 'Fast activation with 24/7 support',
            ]],
            ['vendor' => 'opplex', 'title' => 'Crystal IPTV', 'display_price' => 'From $10 / month', 'price_amount' => 10.00, 'sort_order' => 20, 'features' => [
                'Standard Plan', '28,000+ live TV channels', '150,000+ movies and series',
                'Full HD and 4K quality', '24/7 support', 'Anti-buffering and anti-freezing', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'Loin ott iptv', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 21, 'features' => [
                'Standard Plan', '20,000+ live TV channels', '24/7 support', '24-hour free trial',
                '150,000+ movies and series', '4K HD quality', 'Buffer-free support across devices',
            ]],
            ['vendor' => 'opplex', 'title' => 'Extra IPTV', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 22, 'features' => [
                'Standard Plan', '18,000+ channels', '24/7 support', '24-hour free trial',
                'Smooth playback', '4K quality', 'Multi-device support',
            ]],
            ['vendor' => 'opplex', 'title' => 'Mega Ott IPTV', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 23, 'features' => [
                'Standard Plan', 'Live TV channels', 'Movies and series', 'Buffer-free streaming',
                'Worldwide content', '24/7 support', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'GEO world iptv', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 24, 'features' => [
                'Standard Plan', '15,000+ live TV channels', '24/7 support', '24-hour free trial',
                '100,000+ movies and series', 'All-device support', '4K HD quality',
            ]],
            ['vendor' => 'opplex', 'title' => 'Oxynet IPTV', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 25, 'features' => [
                'Standard Plan', '15,000+ channels', '24-hour free trial', '70,000+ movies and series',
                '4K HD content', '24/7 support',
            ]],
            ['vendor' => 'opplex', 'title' => 'RYNEX IPTV', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 26, 'features' => [
                'Standard Plan', '15,000+ channels', '24/7 support', '80,000+ movies and series',
                'HD quality', 'All-device support',
            ]],
            ['vendor' => 'opplex', 'title' => 'Cloud iptv', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 27, 'features' => [
                'Standard Plan', 'Live TV channels', '24/7 support', '24-hour free trial',
                'Movies and series', '4K HD quality', 'Buffer-free streaming',
            ]],
            ['vendor' => 'opplex', 'title' => 'B1G IPTV', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 28, 'features' => [
                'Standard Plan', '27,000+ channels', '24/7 support', '6-hour free trial',
                '100,000+ movies and series', '4K HD quality', 'Buffer-free and freeze-free streaming',
            ]],
            ['vendor' => 'opplex', 'title' => 'ASTV IPTV', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 29, 'features' => [
                'Standard Plan', '20,000+ live TV channels', 'Unlimited movies and series',
                'HD and 4K quality', '24/7 support', '24-hour free trial',
            ]],
            ['vendor' => 'starshare', 'title' => 'Star Sher', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 30, 'features' => [
                'Standard Plan', 'Live TV channels', 'Movies and series', 'HD quality',
                'All-device support', '24/7 support', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'Diamond IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 40, 'features' => [
                'Premium Plan', '30,000+ live TV channels', 'Unlimited movies and series',
                'Buffer-free streaming', '4K HD quality', '24/7 support', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'NEO 4K', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 41, 'features' => [
                'Premium Plan', '30,000+ live TV channels', '150,000+ movies and series',
                'Full HD and 4K quality', '24/7 support', 'Only reseller panel available',
            ]],
            ['vendor' => 'opplex', 'title' => 'Tivi one', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 42, 'features' => [
                'Premium Plan', '30,000+ live TV channels', 'Movies and series', '4K HD quality',
                'Worldwide content', '24/7 support', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => '🦖Dino IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 43, 'features' => [
                'Premium Plan', '30,000+ live TV channels', '150,000+ movies and series',
                'Full HD and 4K quality', '24/7 support', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'XTRA IPTV (DSTV)', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 44, 'features' => [
                'Premium Plan', '10,000+ live TV channels', '24/7 support', '50,000+ movies',
                'HD and 4K resolution', 'Smooth, buffer-free streaming', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'TS4K STROM IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 45, 'features' => [
                'Premium Plan', '25,000+ channels', 'Global content', '150,000+ movies and series',
                '4K Ultra HD quality', 'Buffer-free streaming', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'TREX IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 46, 'features' => [
                'Premium Plan', '550,000+ live TV channels', '250,000+ movies and series',
                'All-device support', '4K Ultra HD quality', 'Worldwide content', '24/7 support', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'Sky gilas GTV', 'display_price' => 'From $10 / month', 'price_amount' => 10.00, 'sort_order' => 47, 'features' => [
                'Premium Plan', 'International channels', 'Buffer-free and freeze-free streaming',
                '24/7 support', '24-hour free trial', 'Movies and series', 'All-device support',
            ]],
            ['vendor' => 'opplex', 'title' => 'STRONG 8K IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 48, 'features' => [
                'Premium Plan', '30,000+ channels', '150,000+ movies and series', 'Global content',
                'HD and 4K quality', 'Buffer-free streaming', 'Only reseller panel available',
            ]],
            ['vendor' => 'opplex', 'title' => 'Plixi IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 49, 'features' => [
                'Premium Plan', '24/7 support', '10,000+ channels', '10,000+ movies and series',
                '4K Ultra HD quality', 'All-device support', '24-hour free trial',
            ]],
            ['vendor' => 'opplex', 'title' => 'PROMAX Ott IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 50, 'features' => [
                'Premium Plan', '20,000+ live TV channels', '24/7 support', '24-hour free trial',
                '100,000+ movies and series', '4K quality',
            ]],
            ['vendor' => 'opplex', 'title' => 'Hello Sky IPTV', 'display_price' => 'From $15 / month', 'price_amount' => 15.00, 'sort_order' => 51, 'features' => [
                'Premium Plan', 'Live TV channels', '24/7 support', '24-hour free trial',
                'Movies, series, kids and sports', '4K HD content', 'Buffer-free and freeze-free streaming',
            ]],
            ['vendor' => 'opplex', 'title' => 'Golden IPTV (GOTT)', 'display_price' => 'From $10 / month', 'price_amount' => 10.00, 'sort_order' => 52, 'features' => [
                'Premium Plan', '27,000+ live TV channels', 'Buffer-free and freeze-free streaming',
                '24-hour free trial', '200,000+ movies and series', 'All-device support', 'HD and 4K quality',
            ]],
            ['vendor' => 'opplex', 'title' => 'Enfinty IPTV', 'display_price' => 'From $8 / month', 'price_amount' => 8.00, 'sort_order' => 53, 'features' => [
                'Premium Plan', 'HD channels', '24/7 support', '24-hour free trial',
                'Worldwide movies and series', '4K HD quality', 'Buffer-free streaming',
            ]],
            ['vendor' => 'opplex', 'title' => 'Dream 4k premium', 'display_price' => 'From $10 / month', 'price_amount' => 10.00, 'sort_order' => 54, 'features' => [
                'Premium Plan', '30,000+ channels', '24/7 support', '24-hour free trial',
                '150,000+ movies and series', 'High-resolution 4K quality', 'Buffer-free streaming',
            ]],
            ['vendor' => 'opplex', 'title' => 'Eagle 4k iptv', 'display_price' => 'From $10 / month', 'price_amount' => 10.00, 'sort_order' => 55, 'features' => [
                'Most Premium Plan', 'True 8K streaming', '24/7 support', '24-hour free trial',
                '30,000+ channels', '100,000+ movies and series', '4K HD quality',
            ]],
            ['vendor' => 'opplex', 'title' => '5G Live IPTV (Zaintv)', 'display_price' => 'From $10 / month', 'price_amount' => 10.00, 'sort_order' => 56, 'features' => [
                'Premium Plan', '10,000+ channels', '24/7 support', '24-hour free trial',
                '40,000+ movies and series', '4K Ultra HD quality', 'Buffer-free and freeze-free streaming',
            ]],
        ];

        $now = now();

        DB::transaction(function () use ($plans, $now): void {
            foreach ($plans as $plan) {
                $exists = DB::table('packages')
                    ->where('type', 'iptv')
                    ->where('vendor', $plan['vendor'])
                    ->where('title', $plan['title'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('packages')->insert([
                    'type' => 'iptv',
                    'vendor' => $plan['vendor'],
                    'title' => $plan['title'],
                    'display_price' => $plan['display_price'],
                    'price_amount' => $plan['price_amount'],
                    'icon' => 'bi-router',
                    'features' => json_encode(
                        $plan['features'],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                    ),
                    'duration_months' => 1,
                    'sort_order' => $plan['sort_order'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Plan rows may already be referenced by customer orders, so rollback keeps the imported data intact.
    }
};
