<?php

namespace Database\Seeders;

use App\Models\HomeService;
use Illuminate\Database\Seeder;

class HomeServicesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => __('messages.iptv_packages', [], 'en'),
                'description' => __('messages.iptv_packages_desc', [], 'en'),
                'link' => '/packages',
                'icon' => 'service-4.webp',
            ],
            [
                'title' => __('messages.reseller_panel', [], 'en'),
                'description' => __('messages.reseller_panel_desc', [], 'en'),
                'link' => '/reseller-panel',
                'icon' => 'service-5.webp',
            ],
            [
                'title' => __('messages.iptv_sports', [], 'en'),
                'description' => __('messages.iptv_sports_desc', [], 'en'),
                'link' => '/packages',
                'icon' => 'service-4.webp',
            ],
            [
                'title' => __('messages.iptv_vod', [], 'en'),
                'description' => __('messages.iptv_vod_desc', [], 'en'),
                'link' => '/movies',
                'icon' => 'service-4.webp',
            ],
            [
                'title' => __('messages.iptv_devices', [], 'en'),
                'description' => __('messages.iptv_devices_desc', [], 'en'),
                'link' => '/packages',
                'icon' => 'service-4.webp',
            ],
        ];

        foreach ($items as $i => $item) {
            HomeService::updateOrCreate(
                ['title' => $item['title']],
                $item + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
