<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache for generated public output (sitemaps, llms.txt, navigation).
 * Any content change calls flush(), which bumps the version so every cached entry is rebuilt on next request.
 */
class ContentCache
{
    private const VERSION_KEY = 'content-cache-version';

    public static function remember(string $key, Closure $callback, int $seconds = 86400): mixed
    {
        return Cache::remember(self::key($key), $seconds, $callback);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, (int) Cache::get(self::VERSION_KEY, 1) + 1);
    }

    private static function key(string $key): string
    {
        return 'content:v'.Cache::get(self::VERSION_KEY, 1).':'.$key;
    }
}
