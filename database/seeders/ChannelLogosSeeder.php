<?php

namespace Database\Seeders;

use App\Models\ChannelLogo;
use Illuminate\Database\Seeder;

class ChannelLogosSeeder extends Seeder
{
    public function run(): void
    {
        $logos = [
            'images/resource/8.webp',
            'images/resource/2.webp',
            'images/resource/3.webp',
            'images/resource/4.webp',
            'images/resource/5.webp',
            'images/resource/6.webp',
            'images/resource/7.webp',
        ];

        foreach ($logos as $i => $logo) {
            ChannelLogo::updateOrCreate(
                ['image' => $logo],
                ['image' => $logo, 'sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
