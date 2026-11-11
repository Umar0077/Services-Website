<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\CoreSeeder;
use Database\Seeders\LaunchContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Every admin screen renders for a super admin. */
class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->seed([CoreSeeder::class, LaunchContentSeeder::class]);
    }

    public function test_admin_screens_render(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin]);
        $this->actingAs($admin);

        $urls = ['/admin'];
        foreach (\Filament\Facades\Filament::getPanel('admin')->getResources() as $resource) {
            $urls[] = $resource::getUrl('index');
            if ($resource::hasPage('create')) {
                $urls[] = $resource::getUrl('create');
            }
            $model = $resource::getModel();
            if ($resource::hasPage('edit') && ($record = $model::query()->first())) {
                $urls[] = $resource::getUrl('edit', ['record' => $record]);
            }
        }
        foreach (\Filament\Facades\Filament::getPanel('admin')->getPages() as $page) {
            $urls[] = $page::getUrl();
        }

        foreach (array_unique($urls) as $url) {
            $this->get($url)->assertOk();
        }
        $this->assertGreaterThan(2, count($urls));
    }
}
