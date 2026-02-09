<?php

namespace Database\Seeders;

use App\Models\ChannelLogo;
use Illuminate\Database\Seeder;

class ChannelLogosSeeder extends Seeder
{
    public function run(): void
    {
        $logos = [
            'images/resource/pogo.webp',
            'images/resource/ptv-sports.webp',
            'images/resource/ary-digital.webp',
            'images/resource/cartoon-network.webp',
            'images/resource/star-plus.webp',
            'images/resource/sony.webp',
            'images/resource/star-sports.webp',
        ];

        foreach ($logos as $i => $logo) {
            ChannelLogo::updateOrCreate(
                ['image' => $logo],
                ['image' => $logo, 'sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
