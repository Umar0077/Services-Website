<?php

namespace App\Providers\Filament;

use App\Filament\Widgets;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Toast when a form fails validation, naming the first problem (fields are also highlighted).
        \Filament\Pages\BasePage::$reportValidationErrorUsing = function (\Illuminate\Validation\ValidationException $exception) {
            $count = count($exception->errors());
            \Filament\Notifications\Notification::make()
                ->title($count > 1 ? "Please fix {$count} highlighted fields" : 'Please fix the highlighted field')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        };

        // Admin tables: consistent, fast pagination everywhere (see docs/02-ARCHITECTURE.md → Admin tables).
        \Filament\Tables\Table::configureUsing(function (\Filament\Tables\Table $table) {
            $table
                ->paginationPageOptions([10, 25, 50, 100])
                ->defaultPaginationPageOption(25)
                ->extremePaginationLinks()
                ->persistSortInSession()
                ->persistFiltersInSession()
                ->persistSearchInSession()
                ->searchDebounce('500ms')
                ->striped();
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            // Local-only demo accounts panel under the sign-in form (never shown in production).
            ->renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.auth.demo-accounts'))
            // Toast alerts for any failed request (server errors, expired session, lost connection...).
            ->renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_AFTER, fn () => view('filament.error-toasts'))
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('HVAC Services')
            ->brandLogo(asset('images/brand/logo-hvac-services.png'))
            ->darkModeBrandLogo(asset('images/brand/logo-hvac-services-white.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/brand/favicon.png'))
            ->colors([
                'primary' => Color::hex('#E64A19'),
                'gray' => Color::Slate,
                'info' => Color::hex('#1E4155'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->navigationGroups([
                NavigationGroup::make('Content'),
                NavigationGroup::make('Blog'),
                NavigationGroup::make('Locations'),
                NavigationGroup::make('Leads'),
                NavigationGroup::make('SEO & tracking'),
                NavigationGroup::make('Administration'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->widgets([
                Widgets\QuickActions::class,
                Widgets\ContentStats::class,
                Widgets\LatestLeads::class,
                Widgets\RecentActivity::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
