<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConnectionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Stream on 1 device', 'price' => 15.00, 'max_devices' => 1, 'active' => true],
            ['name' => 'Up to 2 devices',    'price' => 23.00, 'max_devices' => 2, 'active' => true],
            ['name' => 'Up to 3 devices',    'price' => 30.00, 'max_devices' => 3, 'active' => true],
            ['name' => 'Up to 4 devices',    'price' => 40.00, 'max_devices' => 4, 'active' => true],
            ['name' => 'Up to 5 devices',    'price' => 50.00, 'max_devices' => 5, 'active' => true],
        ];

        foreach ($rows as $r) {
            Plan::updateOrCreate(
                ['max_devices' => $r['max_devices']],
                $r
            );
        }
    }
}
