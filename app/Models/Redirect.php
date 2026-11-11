<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_hit_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect) {
            $redirect->from_path = self::normalize($redirect->from_path);
        });
        static::saved(fn () => Cache::forget('redirect-map'));
        static::deleted(fn () => Cache::forget('redirect-map'));
    }

    public static function normalize(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';

        return '/'.trim(strtolower($path), '/');
    }

    /** @return array<string, array{0: string, 1: int, 2: int}> from_path => [to, status, id] */
    public static function map(): array
    {
        return Cache::rememberForever('redirect-map', fn () => static::query()
            ->where('is_active', true)
            ->get(['id', 'from_path', 'to_path', 'status_code'])
            ->mapWithKeys(fn ($r) => [$r->from_path => [$r->to_path, $r->status_code, $r->id]])
            ->all());
    }

    /**
     * Create a 301 from an old public path to a new one (used when slugs change).
     * Also re-points existing redirects at the new target so chains never form.
     */
    public static function track(string $oldUrl, string $newUrl): void
    {
        $from = self::normalize($oldUrl);
        $to = self::normalize($newUrl);
        if ($from === $to) {
            return;
        }

        static::query()->where('to_path', $from)->update(['to_path' => $to]);
        static::query()->where('from_path', $to)->delete(); // the new URL must not redirect away
        static::query()->updateOrCreate(['from_path' => $from], ['to_path' => $to, 'status_code' => 301, 'is_active' => true]);
        Cache::forget('redirect-map');
    }
}
