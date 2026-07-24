<?php

namespace Database\Seeders;

use App\Models\SiteAsset;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            EcommerceSeeder::class,
            ContactChannelSeeder::class,
            RheaOfficialDataSeeder::class,
        ]);

        SiteAsset::current();
    }
}
