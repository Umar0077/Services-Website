<?php

namespace App\Models;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFaqs, HasSeo, LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'coverage_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (State $state) {
            if ($state->isDirty(['heading', 'intro', 'climate', 'body', 'faqs'])) {
                $state->content_updated_at = now();
            }
        });

        // A state slug change moves every city URL under it.
        static::updated(function (State $state) {
            if ($state->wasChanged('slug')) {
                foreach ($state->cities()->get(['id', 'slug', 'state_id']) as $city) {
                    Redirect::track(
                        route('locations.city', [$state->getOriginal('slug'), $city->slug]),
                        route('locations.city', [$state->slug, $city->slug]),
                    );
                }
            }
        });
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    public function publicUrl(): string
    {
        return route('locations.state', $this);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published;
    }

    /** Pages for unverified coverage can be previewed but are never indexed. */
    public function indexingAllowedByContent(): bool
    {
        return $this->coverage_verified && filled($this->intro);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
