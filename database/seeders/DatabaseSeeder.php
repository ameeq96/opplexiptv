<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DevicesSeeder::class,
            ConnectionPlanSeeder::class,
            PackagesSeeder::class,
            BlogSeeder::class,
            ShopProductsSeeder::class,
            HomeServicesSeeder::class,
            TestimonialsSeeder::class,
            ChannelLogosSeeder::class,
            MenuItemsSeeder::class,
            PricingSectionSeeder::class,
            FooterSeeder::class,
        ]);

        Admin::create([
            'name' => 'Ameeq',
            'email' => 'admin@opplexiptv.com',
            'password' => Hash::make('opplexiptv@1234'),
        ]);
    }
}
