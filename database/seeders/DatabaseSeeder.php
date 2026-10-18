<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // No default credentials or synthetic enquiries are created.
        $this->call(FrontendContentSeeder::class);
    }
}
