<?php

namespace App\Models\Concerns;

use App\Models\Media;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared SEO columns: seo_title, seo_description, canonical_url, robots_index, robots_follow,
 * og_title, og_description, og_media_id. Models implement publicUrl() and isPubliclyVisible().
 *
 * Any save or delete bumps the content cache version so sitemaps and llms.txt regenerate.
 */
trait HasSeo
{
    public static function bootHasSeo(): void
    {
        static::saved(fn () => ContentCache::flush());
        static::deleted(fn () => ContentCache::flush());

        // Changing a slug keeps the old URL working with a permanent redirect.
        static::updated(function ($model) {
            if ($model->wasChanged($model->urlAttributes()) && $model->getOriginal('slug')) {
                $old = $model->previousPublicUrl();
                if ($old && $old !== $model->publicUrl()) {
                    \App\Models\Redirect::track($old, $model->publicUrl());
                }
            }
        });
    }

    /** Attributes that make up the public URL. */
    public function urlAttributes(): array
    {
        return ['slug'];
    }

    public function previousPublicUrl(): ?string
    {
        $old = clone $this;
        $old->setRawAttributes($this->getOriginal());

        return $old->publicUrl();
    }

    public function initializeHasSeo(): void
    {
        $this->mergeCasts([
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'content_updated_at' => 'datetime',
        ]);
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    abstract public function publicUrl(): string;

    abstract public function isPubliclyVisible(): bool;

    /** Extra content rules, e.g. unverified locations stay noindex. */
    public function indexingAllowedByContent(): bool
    {
        return true;
    }

    /** Robots meta allows indexing. */
    public function allowsIndexing(): bool
    {
        return $this->isPubliclyVisible() && $this->robots_index && $this->indexingAllowedByContent();
    }

    /** Belongs in the sitemap: indexable and canonical to itself. */
    public function isIndexable(): bool
    {
        return $this->allowsIndexing() && ! $this->hasForeignCanonical();
    }

    public function hasForeignCanonical(): bool
    {
        return filled($this->canonical_url)
            && rtrim($this->canonical_url, '/') !== rtrim($this->publicUrl(), '/');
    }

    public function canonical(): string
    {
        return $this->canonical_url ?: $this->publicUrl();
    }

    public function robotsDirective(): string
    {
        return ($this->allowsIndexing() ? 'index' : 'noindex').','.($this->robots_follow ? 'follow' : 'nofollow');
    }

    public function lastModified(): ?\DateTimeInterface
    {
        return $this->content_updated_at ?? $this->updated_at;
    }
}
