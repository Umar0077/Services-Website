<?php

namespace App\Providers;

use App\Models;
use App\Policies;
use App\Support\Settings;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
    }

    public function boot(): void
    {
        // Shared SEO columns for every public content table.
        Blueprint::macro('seo', function () {
            /** @var Blueprint $this */
            $this->string('seo_title')->nullable();
            $this->string('seo_description', 320)->nullable();
            $this->string('canonical_url')->nullable();
            $this->boolean('robots_index')->default(true);
            $this->boolean('robots_follow')->default(true);
            $this->string('og_title')->nullable();
            $this->string('og_description', 320)->nullable();
            $this->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();
        });

        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
            \Illuminate\Support\Facades\Vite::useHotFile(storage_path('framework/vite.hot'));
        }

        Paginator::defaultView('components.pagination');

        foreach ([
            Models\Post::class => Policies\PostPolicy::class,
            Models\Category::class => Policies\TaxonomyPolicy::class,
            Models\Tag::class => Policies\TaxonomyPolicy::class,
            Models\Media::class => Policies\MediaPolicy::class,
            Models\Page::class => Policies\ContentPolicy::class,
            Models\Service::class => Policies\ContentPolicy::class,
            Models\State::class => Policies\ContentPolicy::class,
            Models\City::class => Policies\ContentPolicy::class,
            Models\Lead::class => Policies\LeadPolicy::class,
            Models\Redirect::class => Policies\AdminOnlyPolicy::class,
            Models\TrackingNumber::class => Policies\AdminOnlyPolicy::class,
            Models\ActivityLog::class => Policies\ActivityLogPolicy::class,
            Models\User::class => Policies\UserPolicy::class,
        ] as $model => $policy) {
            Gate::policy($model, $policy);
        }

        Gate::define('manage-settings', fn (Models\User $user) => $user->role->isAdmin());

        RateLimiter::for('leads', function (Request $request) {
            return [
                Limit::perMinute(3)->by('lead-min:'.$request->ip()),
                Limit::perDay(20)->by('lead-day:'.$request->ip()),
            ];
        });

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof Models\User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                Models\ActivityLog::record('login', $event->user, 'Signed in');
            }
        });

        View::composer(['components.header', 'components.footer', 'components.mobile-nav'], \App\View\Composers\NavigationComposer::class);

        View::composer('*', function ($view) {
            static $shared = null;
            $shared ??= [
                'site' => app(Settings::class),
            ];
            $view->with($shared);
        });
    }
}
