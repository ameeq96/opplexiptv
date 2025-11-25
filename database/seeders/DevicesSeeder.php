<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;

class DevicesSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            ['name' => 'Smart TV', 'icon' => 'bi-tv'],
            ['name' => 'Firestick', 'icon' => 'bi-amazon'],
            ['name' => 'Android',  'icon' => 'bi-android2'],
            ['name' => 'iOS',      'icon' => 'bi-apple'],
            ['name' => 'MAG Box',  'icon' => 'bi-box'],
            ['name' => 'PC/Mac',   'icon' => 'bi-laptop'],
        ];

        foreach ($devices as $d) {
            Device::updateOrCreate(
                ['name' => $d['name']],
                ['icon' => $d['icon']]
            );
        }
    }
}
