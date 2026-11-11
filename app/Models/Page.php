<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasSeo, LogsActivity;

    protected $guarded = ['id'];

    public const TEMPLATES = [
        'standard' => 'Standard page',
        'legal' => 'Legal / policy document',
        'landing' => 'Call landing page (paid traffic)',
    ];

    /** Slugs owned by application routes; a CMS page can never use them. */
    public const RESERVED_SLUGS = [
        'admin', 'services', 'blog', 'locations', 'contact', 'disclaimer', 'login', 'logout', 'livewire', 'storage', 'build',
        'sitemap.xml', 'robots.txt', 'llms.txt', 'up', 'filament', 'api',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'is_published' => 'boolean',
            'show_in_footer' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if ($page->isDirty(['title', 'heading', 'intro', 'body', 'sections'])) {
                $page->content_updated_at = now();
            }
        });
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    public function publicUrl(): string
    {
        return route('pages.show', $this);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published;
    }

    public function isSystem(): bool
    {
        return filled($this->system_key);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function urlFor(string $systemKey, string $fallback = '/'): string
    {
        static $cache = [];
        $cache[$systemKey] ??= static::query()->where('system_key', $systemKey)->published()->value('slug');

        return $cache[$systemKey] ? url($cache[$systemKey]) : url($fallback);
    }
}
