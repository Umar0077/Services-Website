<?php

namespace Tests;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\CoreSeeder;
use Database\Seeders\LaunchContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

/** Tests that run against the real launch content (services, pages, states, cities, posts). */
abstract class SeededTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed([CoreSeeder::class, LaunchContentSeeder::class]);
    }

    protected function user(Role $role): User
    {
        return User::factory()->create(['role' => $role]);
    }
}
