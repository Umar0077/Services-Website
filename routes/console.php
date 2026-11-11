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

Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('queue:work --stop-when-empty --tries=3')->everyMinute()->withoutOverlapping();
Schedule::command('model:prune')->daily();
