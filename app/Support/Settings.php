<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Site-wide settings: config/site.php defaults overridden by rows in the `settings` table.
 * Usage: app(Settings::class)->get('phone') or $site->get('phone') in Blade.
 */
class Settings
{
    private ?array $values = null;

    public function all(): array
    {
        if ($this->values === null) {
            $stored = Cache::rememberForever('site-settings', function () {
                try {
                    return Setting::query()->pluck('value', 'key')->all();
                } catch (\Throwable) {
                    return []; // database not migrated yet
                }
            });
            $this->values = array_merge(config('site'), array_filter($stored, fn ($v) => $v !== null && $v !== ''));
        }

        return $this->values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $this->refresh();
        ContentCache::flush();
    }

    public function refresh(): void
    {
        Cache::forget('site-settings');
        $this->values = null;
    }

    public function name(): string
    {
        return (string) $this->get('site_name');
    }

    /** Resolve a settings image (either a /public path or a storage path from an upload). */
    public function asset(string $key): ?string
    {
        $value = $this->get($key);
        if (blank($value)) {
            return null;
        }

        return str_starts_with($value, '/') || str_starts_with($value, 'http') ? url($value) : asset('storage/'.$value);
    }
}
