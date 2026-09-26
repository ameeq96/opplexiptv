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
            AdminTranslationsSeeder::class,
            DigitalCommerceSeeder::class,
        ]);

        if (Admin::query()->doesntExist()) {
            $adminEmail = (string) config('auth.admin_seed.email');
            $adminPassword = (string) config('auth.admin_seed.password');

            if ($adminEmail === '' || !$this->isStrongAdminPassword($adminPassword)) {
                throw new \RuntimeException(
                    'A valid ADMIN_SEED_EMAIL and strong ADMIN_SEED_PASSWORD are required to create the first Owner.'
                );
            }

            Admin::create([
                'name' => config('auth.admin_seed.name', 'Administrator'),
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'role' => Admin::ROLE_OWNER,
                'must_change_password' => true,
                'password_changed_at' => null,
            ]);
        }
    }

    private function isStrongAdminPassword(string $password): bool
    {
        return strlen($password) >= 12
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1
            && preg_match('/[^A-Za-z0-9]/', $password) === 1;
    }
}
