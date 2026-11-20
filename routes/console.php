<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\ContentCache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/** Scheduled posts go live when their publish time arrives; sitemaps and llms.txt refresh with them. */
Artisan::command('posts:publish-scheduled', function () {
    $count = Post::query()
        ->where('status', PostStatus::Scheduled)
        ->where('published_at', '<=', now())
        ->update(['status' => PostStatus::Published->value, 'updated_at' => now()]);

    if ($count) {
        ContentCache::flush();
    }

    $this->info("Published {$count} scheduled post(s).");
})->purpose('Publish scheduled blog posts whose time has come');

/** Set a new password for an admin user from the command line (prints it once). */
Artisan::command('app:reset-password {email}', function (string $email) {
    $user = \App\Models\User::query()->where('email', $email)->first();
    if (! $user) {
        $this->error('No user with that email.');

        return 1;
    }
    $password = \Illuminate\Support\Str::password(20);
    $user->forceFill(['password' => $password])->save();
    $this->info("New password for {$email}: {$password}");
})->purpose('Generate a new password for an admin user');

/** Deployment readiness check: fails if public/hot is present or compiled Vite assets are missing. */
Artisan::command('deploy:check', function () {
    $errors = [];

    if (is_file(public_path('hot'))) {
        $errors[] = 'Stale public/hot file detected. Run "npm run build" or delete public/hot before deploying.';
    }

    $manifestPath = public_path('build/manifest.json');
    if (! is_file($manifestPath)) {
        $errors[] = 'Missing public/build/manifest.json. Run "npm run build" to compile frontend assets.';
    } else {
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest)) {
            $errors[] = 'public/build/manifest.json is not valid JSON.';
        } else {
            foreach (['resources/css/app.css', 'resources/js/app.js'] as $entry) {
                $file = $manifest[$entry]['file'] ?? null;
                if (! $file || ! is_file(public_path('build/'.$file))) {
                    $errors[] = "Missing compiled asset for {$entry} in public/build/.";
                }
            }
        }
    }

    if (blank(config('app.key'))) {
        $errors[] = 'APP_KEY is not set.';
    }

    if (app()->isProduction()) {
        if (config('app.debug')) {
            $errors[] = 'APP_DEBUG must be false in production.';
        }
        if (! str_starts_with((string) config('app.url'), 'https://')) {
            $errors[] = 'APP_URL must use https:// in production.';
        }
    }

    if ($errors) {
        foreach ($errors as $error) {
            $this->error($error);
        }

        return 1;
    }

    $this->info('Deployment check passed: public/hot is absent and compiled Vite assets are verified.');

    return 0;
})->purpose('Verify production build assets and fail if public/hot is present');

Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('queue:work --stop-when-empty --tries=3')->everyMinute()->withoutOverlapping();
Schedule::command('model:prune')->daily();
