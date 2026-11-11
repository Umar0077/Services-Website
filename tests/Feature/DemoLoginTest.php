<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'site.seed_admin.email' => 'admin@hvac.test',
            'site.seed_admin.password' => 'Super-secret-1',
            'site.seed_admin.demo_password' => 'Demo-pass-2',
        ]);
        User::factory()->create(['email' => 'admin@hvac.test', 'password' => 'Super-secret-1', 'role' => Role::SuperAdmin]);
        User::factory()->create(['email' => 'editor@hvac.test', 'password' => 'Demo-pass-2', 'role' => Role::Editor]);
    }

    public function test_demo_login_prefills_and_lists_accounts_when_enabled_locally(): void
    {
        config(['site.demo_login' => true]);

        $this->get('/admin/login')->assertOk()->assertSee('Demo login (local copy only)')->assertSee('editor@hvac.test');

        Livewire::test(Login::class)
            ->assertSet('data.email', 'admin@hvac.test')
            ->call('fillDemo', 'editor')
            ->assertSet('data.email', 'editor@hvac.test')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs(User::where('email', 'editor@hvac.test')->first());
    }

    public function test_demo_login_is_hidden_when_disabled(): void
    {
        config(['site.demo_login' => false]);

        $this->get('/admin/login')->assertOk()->assertDontSee('Demo login')->assertDontSee('Super-secret-1');
        Livewire::test(Login::class)->assertSet('data.email', null);
    }

    public function test_demo_login_never_appears_in_production(): void
    {
        config(['site.demo_login' => true]);
        $this->app['env'] = 'production';

        $this->assertFalse(Login::demoEnabled());
        $this->assertSame([], Login::demoAccounts());
    }
}
