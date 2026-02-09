<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        $top = [
            ['label' => 'Home', 'url' => '/', 'sort_order' => 0],
            ['label' => 'Our Packages', 'url' => '/packages', 'sort_order' => 1],
            ['label' => 'IPTV Applications', 'url' => '/iptv-applications', 'sort_order' => 2],
            ['label' => 'FAQ\'s', 'url' => '/faqs', 'sort_order' => 3],
            ['label' => 'More', 'url' => '#', 'sort_order' => 4],
        ];

        $parents = [];
        foreach ($top as $item) {
            $parents[$item['label']] = MenuItem::updateOrCreate(
                ['label' => $item['label'], 'parent_id' => null],
                $item + ['is_active' => true, 'open_new_tab' => false]
            );
        }

        $more = [
            ['label' => 'About Us', 'url' => '/about', 'sort_order' => 0],
            ['label' => 'Contact Us', 'url' => '/contact', 'sort_order' => 1],
            ['label' => 'Blogs', 'url' => '/blogs', 'sort_order' => 2],
            ['label' => 'Reseller Panel', 'url' => '/reseller-panel', 'sort_order' => 3],
            ['label' => 'Pricing', 'url' => '/pricing', 'sort_order' => 4],
            ['label' => 'Movies/Series', 'url' => '/movies', 'sort_order' => 5],
        ];

        foreach ($more as $item) {
            MenuItem::updateOrCreate(
                ['label' => $item['label'], 'parent_id' => $parents['More']->id],
                $item + ['is_active' => true, 'open_new_tab' => false]
            );
        }
    }
}
