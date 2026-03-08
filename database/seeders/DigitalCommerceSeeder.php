<?php

namespace Database\Seeders;

use App\Models\Digital\DigitalCategory;
use App\Models\Digital\DigitalDeliveryPayload;
use App\Models\Digital\DigitalProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DigitalCommerceSeeder extends Seeder
{
    public function run(): void
    {
        $pkrToUsdRate = 280;
        $formatUsd = static fn (float $value): string => '$' . number_format($value, 2);
        $convertPkrTextToUsd = static function (?string $text) use ($pkrToUsdRate, $formatUsd): ?string {
            if (!is_string($text) || $text === '') {
                return $text;
            }

            // Converts patterns like "200 PKR", "PKR 200", "2,000 PKR" into "$x.xx"
            $pattern = '/(?:PKR\s*([\d,]+(?:\.\d+)?)|([\d,]+(?:\.\d+)?)\s*PKR)/i';
            return preg_replace_callback($pattern, function (array $matches) use ($pkrToUsdRate, $formatUsd) {
                $raw = $matches[1] !== '' ? $matches[1] : ($matches[2] ?? '');
                $amount = (float) str_replace(',', '', $raw);
                $usd = ($amount / $pkrToUsdRate) + 1;
                return $formatUsd(round($usd, 2));
            }, $text);
        };
        $sourceImageDir = public_path('images/digital');
        $targetImageDir = public_path('images/digital-products');

        if (!File::exists($targetImageDir)) {
            File::makeDirectory($targetImageDir, 0755, true);
        }

        $sourceImagesByName = File::exists($sourceImageDir)
            ? collect(File::files($sourceImageDir))
                ->keyBy(fn ($file) => strtolower($file->getFilename()))
            : collect();

        // Map product slug => source image filename (as defined in public/images/digital)
        $slugImageMap = [
            'crunchyroll-premium' => 'crunchyroll.webp',
            'youtube-premium' => 'youtubepremium.webp',
            'expressvpn-premium' => 'expressvpn.webp',
            'hulu' => 'hulu.webp',
            'paramount-plus' => 'paramount.webp',
            'spotify-premium' => 'spotify.webp',
            'curiosity-stream' => 'curiosity.webp',
            'quillbot-premium' => 'grammarly.webp',
            'office-365' => 'windows11pro.webp',
            'windscribe-1-month' => 'windscribe.webp',
            'skillshare-premium' => 'disney+hoststar.webp',
            'nordvpn' => 'nordvpn.webp',
            'netflix' => 'netflix.webp',
            'apple-music' => 'applemusic.webp',
            'chegg-unlock' => 'chegg.webp',
            'turnitin' => 'turnitin.webp',
            'chatgpt-plus' => 'chatgpt.webp',
            'grammarly-premium' => 'grammarly.webp',
            'jiocinema-with-vpn' => 'jiocinema.webp',
            'windscribe-vpn-unlimited' => 'windscribe.webp',
            'surfshark-vpn' => 'purevpn.webp',
            'udemy' => 'appletv.webp',
            'hotstar' => 'disney+hoststar.webp',
            'espn-plus' => 'espn+.webp',
            'canva-premium' => 'peacock.webp',
            'tidal-premium' => 'applemusic.webp',
            'shutterstock' => 'windows11pro.webp',
            'scribd' => 'chegg.webp',
            'zee5-premium' => 'disneyland.webp',
            'hbo-max-premium' => 'hbomax.webp',
            'disney-plus' => 'disneyland.webp',
            'prime-video' => 'primevideo.webp',
            'course-hero-1-month' => 'chegg.webp',
            'sling-tv' => 'sling.webp',
        ];

        $categorySeed = [
            ['name' => 'Streaming', 'slug' => 'streaming', 'sort_order' => 1],
            ['name' => 'VPN & Security', 'slug' => 'vpn-security', 'sort_order' => 2],
            ['name' => 'Education', 'slug' => 'education', 'sort_order' => 3],
            ['name' => 'Productivity', 'slug' => 'productivity', 'sort_order' => 4],
            ['name' => 'Music', 'slug' => 'music', 'sort_order' => 5],
            ['name' => 'Sports', 'slug' => 'sports', 'sort_order' => 6],
        ];

        $categories = [];
        foreach ($categorySeed as $row) {
            $categories[$row['slug']] = DigitalCategory::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $products = [
            [
                'category' => 'streaming',
                'title' => 'Crunchyroll Premium',
                'slug' => 'crunchyroll-premium',
                'short_description' => '200 PKR for 1 month.',
                'full_description' => 'Crunchyroll premium account access with fast delivery.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 1,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['region' => 'Global', 'duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'YouTube Premium',
                'slug' => 'youtube-premium',
                'short_description' => '1 month 200 PKR, 4 months 700 PKR.',
                'full_description' => 'YouTube premium subscription access.',
                'price' => 230,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 2,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'vpn-security',
                'title' => 'ExpressVPN Premium',
                'slug' => 'expressvpn-premium',
                'short_description' => '1 device.',
                'full_description' => 'ExpressVPN premium account for one device.',
                'price' => 350,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 3,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 3,
                'metadata' => ['device' => '1'],
            ],
            [
                'category' => 'streaming',
                'title' => 'Hulu',
                'slug' => 'hulu',
                'short_description' => '300 PKR per month.',
                'full_description' => 'Hulu premium profile access.',
                'price' => 300,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 4,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'Paramount Plus',
                'slug' => 'paramount-plus',
                'short_description' => '1 month for 240 PKR.',
                'full_description' => 'Paramount Plus streaming access.',
                'price' => 250,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 5,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'music',
                'title' => 'Spotify Premium',
                'slug' => 'spotify-premium',
                'short_description' => '1 month 180 PKR, 3 months 480 PKR.',
                'full_description' => 'Spotify premium individual account.',
                'price' => 180,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 6,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'Curiosity Stream',
                'slug' => 'curiosity-stream',
                'short_description' => '250 PKR per month.',
                'full_description' => 'Curiosity Stream premium access.',
                'price' => 250,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 7,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'education',
                'title' => 'QuillBot Premium',
                'slug' => 'quillbot-premium',
                'short_description' => 'Premium writing assistant.',
                'full_description' => 'QuillBot premium subscription access.',
                'price' => 300,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 8,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'productivity',
                'title' => 'Office 365',
                'slug' => 'office-365',
                'short_description' => 'Yearly subscription.',
                'full_description' => 'Office 365 yearly plan activation.',
                'price' => 2000,
                'currency' => 'PKR',
                'delivery_type' => 'code',
                'sort_order' => 9,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 3,
                'metadata' => ['duration' => 'yearly'],
            ],
            [
                'category' => 'vpn-security',
                'title' => 'Windscribe 1 Month',
                'slug' => 'windscribe-1-month',
                'short_description' => '150 PKR monthly. USA and India unlimited.',
                'full_description' => 'Windscribe VPN one month plan.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 10,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'education',
                'title' => 'Skillshare Premium',
                'slug' => 'skillshare-premium',
                'short_description' => '1 month 150 PKR.',
                'full_description' => 'Skillshare premium classes access.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 11,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'vpn-security',
                'title' => 'NordVPN',
                'slug' => 'nordvpn',
                'short_description' => '250 PKR monthly.',
                'full_description' => 'NordVPN account credentials.',
                'price' => 250,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 12,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'Netflix',
                'slug' => 'netflix',
                'short_description' => '360 PKR monthly.',
                'full_description' => 'Netflix profile access.',
                'price' => 360,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 13,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'music',
                'title' => 'Apple Music',
                'slug' => 'apple-music',
                'short_description' => 'Premium music subscription.',
                'full_description' => 'Apple Music premium account.',
                'price' => 250,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 14,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'education',
                'title' => 'Chegg Unlock',
                'slug' => 'chegg-unlock',
                'short_description' => 'Chegg unlock support.',
                'full_description' => 'Manual Chegg question unlock service.',
                'price' => 300,
                'currency' => 'PKR',
                'delivery_type' => 'manual',
                'sort_order' => 15,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 10,
                'metadata' => ['service' => 'unlock'],
            ],
            [
                'category' => 'education',
                'title' => 'Turnitin',
                'slug' => 'turnitin',
                'short_description' => 'Turnitin report service.',
                'full_description' => 'Manual Turnitin plagiarism report.',
                'price' => 350,
                'currency' => 'PKR',
                'delivery_type' => 'manual',
                'sort_order' => 16,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 10,
                'metadata' => ['service' => 'report'],
            ],
            [
                'category' => 'productivity',
                'title' => 'ChatGPT Plus',
                'slug' => 'chatgpt-plus',
                'short_description' => 'Premium AI access.',
                'full_description' => 'ChatGPT Plus subscription access.',
                'price' => 800,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 17,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 3,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'productivity',
                'title' => 'Grammarly Premium',
                'slug' => 'grammarly-premium',
                'short_description' => 'Premium grammar tools.',
                'full_description' => 'Grammarly premium account access.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 18,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'JioCinema with VPN',
                'slug' => 'jiocinema-with-vpn',
                'short_description' => 'JioCinema access with VPN.',
                'full_description' => 'JioCinema subscription bundled with VPN support.',
                'price' => 400,
                'currency' => 'PKR',
                'delivery_type' => 'manual',
                'sort_order' => 19,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['bundle' => 'vpn'],
            ],
            [
                'category' => 'vpn-security',
                'title' => 'Windscribe VPN Unlimited',
                'slug' => 'windscribe-vpn-unlimited',
                'short_description' => 'Unlimited VPN.',
                'full_description' => 'Windscribe unlimited plan.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 20,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['plan' => 'unlimited'],
            ],
            [
                'category' => 'education',
                'title' => 'Udemy',
                'slug' => 'udemy',
                'short_description' => 'Course package access.',
                'full_description' => 'Udemy premium course access.',
                'price' => 1600,
                'currency' => 'PKR',
                'delivery_type' => 'manual',
                'sort_order' => 22,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['service' => 'course access'],
            ],
            [
                'category' => 'streaming',
                'title' => 'Disney + Hotstar',
                'slug' => 'hotstar',
                'short_description' => 'VPN provided.',
                'full_description' => 'Hotstar premium with VPN support.',
                'price' => 400,
                'currency' => 'PKR',
                'delivery_type' => 'manual',
                'sort_order' => 23,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['bundle' => 'vpn'],
            ],
            [
                'category' => 'sports',
                'title' => 'ESPN Plus',
                'slug' => 'espn-plus',
                'short_description' => '1 month for 350 PKR.',
                'full_description' => 'ESPN Plus sports streaming plan.',
                'price' => 350,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 24,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'productivity',
                'title' => 'Canva Premium',
                'slug' => 'canva-premium',
                'short_description' => '150 PKR monthly.',
                'full_description' => 'Canva premium account access.',
                'price' => 150,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 25,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'music',
                'title' => 'Tidal Premium',
                'slug' => 'tidal-premium',
                'short_description' => '1 month for 200 PKR.',
                'full_description' => 'Tidal premium music access.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 26,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'productivity',
                'title' => 'Shutterstock',
                'slug' => 'shutterstock',
                'short_description' => '1 month plan around 200 PKR.',
                'full_description' => 'Shutterstock image plan access.',
                'price' => 250,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 27,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'education',
                'title' => 'Scribd',
                'slug' => 'scribd',
                'short_description' => '1 month 200 PKR.',
                'full_description' => 'Scribd premium reading access.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 28,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'ZEE5 Premium',
                'slug' => 'zee5-premium',
                'short_description' => '250 PKR monthly.',
                'full_description' => 'ZEE5 premium content access.',
                'price' => 200,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 30,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'HBO Max Premium',
                'slug' => 'hbo-max-premium',
                'short_description' => '250 PKR monthly.',
                'full_description' => 'HBO Max premium account access.',
                'price' => 250,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 31,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['duration' => '1 month'],
            ],
            [
                'category' => 'streaming',
                'title' => 'Prime Video',
                'slug' => 'prime-video',
                'short_description' => '1 month, 1 screen around 150 PKR.',
                'full_description' => 'Prime Video single screen access.',
                'price' => 150,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 33,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['screen' => '1'],
            ],
            [
                'category' => 'education',
                'title' => 'Course Hero 1 Month',
                'slug' => 'course-hero-1-month',
                'short_description' => '20 unlocks around 450 PKR.',
                'full_description' => 'Course Hero unlock service.',
                'price' => 450,
                'currency' => 'PKR',
                'delivery_type' => 'manual',
                'sort_order' => 34,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 10,
                'metadata' => ['unlocks' => '20'],
            ],
            [
                'category' => 'streaming',
                'title' => 'Sling TV',
                'slug' => 'sling-tv',
                'short_description' => 'Blue and Orange package starts around 150 PKR.',
                'full_description' => 'Sling TV package access.',
                'price' => 150,
                'currency' => 'PKR',
                'delivery_type' => 'credential',
                'sort_order' => 35,
                'is_active' => true,
                'min_qty' => 1,
                'max_qty' => 5,
                'metadata' => ['package' => 'blue-orange'],
            ],
        ];

        // Keep only VPN + OTT platform products.
        // OTT is represented by streaming/sports categories in this project.
        $allowedCategories = ['vpn-security', 'streaming', 'sports'];
        $products = array_values(array_filter(
            $products,
            fn (array $product) => in_array($product['category'] ?? '', $allowedCategories, true)
        ));

        // Force priority products to appear first on frontend lists.
        $prioritySort = [
            'netflix' => 1,
            'prime-video' => 2,
            'hbo-max-premium' => 3,
            'nordvpn' => 4,
        ];
        foreach ($products as &$product) {
            $slug = (string) ($product['slug'] ?? '');
            if (isset($prioritySort[$slug])) {
                $product['sort_order'] = $prioritySort[$slug];
            } else {
                $product['sort_order'] = ((int) ($product['sort_order'] ?? 0)) + 100;
            }
        }
        unset($product);

        // Remove previously seeded non-allowed products from DB.
        $allowedSlugs = array_map(
            fn (array $product) => (string) $product['slug'],
            $products
        );
        DigitalProduct::query()
            ->whereNotIn('slug', $allowedSlugs)
            ->delete();

        foreach ($products as $row) {
            $categorySlug = $row['category'];
            unset($row['category']);

            $row['digital_category_id'] = $categories[$categorySlug]->id;
            $row['price'] = round(((float) $row['price'] / $pkrToUsdRate) + 1, 2);
            $row['currency'] = '$';
            $row['short_description'] = null;
            $row['full_description'] = null;

            if (isset($row['compare_price']) && $row['compare_price'] !== null) {
                $row['compare_price'] = round(((float) $row['compare_price'] / $pkrToUsdRate) + 1, 2);
            }

            $sourceImageName = strtolower((string) ($slugImageMap[$row['slug']] ?? ''));
            $sourceImage = $sourceImageName !== '' ? $sourceImagesByName->get($sourceImageName) : null;
            if ($sourceImage) {
                $extension = strtolower($sourceImage->getExtension() ?: 'png');
                $imageName = $row['slug'] . '.' . $extension;
                File::copy($sourceImage->getRealPath(), $targetImageDir . DIRECTORY_SEPARATOR . $imageName);
                $row['image'] = $imageName;
            }

            $product = DigitalProduct::updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, ['product_type' => 'digital'])
            );

            $existingCount = DigitalDeliveryPayload::query()
                ->where('digital_product_id', $product->id)
                ->count();

            for ($i = $existingCount + 1; $i <= 3; $i++) {
                $payload = match ($product->delivery_type) {
                    'credential' => [
                        'username' => Str::slug($product->title) . $i,
                        'email' => 'demo' . $i . '@example.com',
                        'password' => 'Pass@' . random_int(1000, 9999),
                    ],
                    'code' => [
                        'code' => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
                    ],
                    'link' => [
                        'url' => 'https://delivery.example.com/item/' . Str::random(12),
                    ],
                    'file' => [
                        'file_url' => 'https://files.example.com/download/' . Str::random(10),
                    ],
                    default => [
                        'note' => 'Manual delivery after payment verification.',
                    ],
                };

                DigitalDeliveryPayload::create([
                    'digital_product_id' => $product->id,
                    'payload_type' => $product->delivery_type,
                    'payload' => $payload,
                ]);
            }
        }
    }
}

