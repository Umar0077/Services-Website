<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;

/**
 * Admin sign-in with an optional demo mode for local review.
 *
 * When ADMIN_DEMO_LOGIN=true (and the app is NOT in production), the form is pre-filled with the
 * super admin demo account and a panel lists one-click demo accounts for each role.
 * In production this is always off, so credentials are never shown on a live site.
 */
class Login extends BaseLogin
{
    public static function demoEnabled(): bool
    {
        return config('site.demo_login') && ! app()->isProduction();
    }

    /** @return array<string, array{label: string, email: string, password: string}> */
    public static function demoAccounts(): array
    {
        if (! self::demoEnabled()) {
            return [];
        }

        $seed = config('site.seed_admin');
        $accounts = [
            'super_admin' => ['label' => 'Super Admin', 'email' => $seed['email'] ?? null, 'password' => $seed['password'] ?? null],
            'editor' => ['label' => 'Editor', 'email' => 'editor@hvac.test', 'password' => $seed['demo_password'] ?? null],
            'author' => ['label' => 'Author', 'email' => 'author@hvac.test', 'password' => $seed['demo_password'] ?? null],
        ];

        // Only list accounts that really exist and are active, with a password configured.
        $active = User::query()->where('is_active', true)->whereIn('email', array_column($accounts, 'email'))->pluck('email')->all();

        return array_filter($accounts, fn ($a) => filled($a['email']) && filled($a['password']) && in_array($a['email'], $active, true));
    }

    public function mount(): void
    {
        parent::mount();

        if ($first = array_values(self::demoAccounts())[0] ?? null) {
            $this->form->fill(['email' => $first['email'], 'password' => $first['password'], 'remember' => true]);
        }
    }

    /** Called by the demo account buttons on the login page. */
    public function fillDemo(string $key): void
    {
        $account = self::demoAccounts()[$key] ?? null;
        abort_unless($account, 404);

        $this->form->fill(['email' => $account['email'], 'password' => $account['password'], 'remember' => true]);
    }
}
