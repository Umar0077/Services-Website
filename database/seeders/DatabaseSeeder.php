<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * CoreSeeder          — admin user, 50 states, services, system pages, categories, media (production-safe)
     * LaunchContentSeeder — launch blog articles and written location content (published; locations not indexed
     *                       until coverage is verified)
     * DemoSeeder          — local/testing only: sample users per role, sample leads, a sample tracking number
     */
    public function run(): void
    {
        $this->call(CoreSeeder::class);
        $this->call(LaunchContentSeeder::class);

        if (app()->environment('local', 'testing')) {
            $this->call(DemoSeeder::class);
        }
    }
}
