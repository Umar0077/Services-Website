<?php

namespace App\Models;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    use HasFaqs, HasSeo, LogsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'service_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (City $city) {
            if ($city->isDirty(['heading', 'intro', 'local_info', 'body', 'faqs'])) {
                $city->content_updated_at = now();
            }
        });
    }

    /** Published city whose parent state is also published. */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->whereHas('state', fn ($q) => $q->where('is_published', true));
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
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
        return route('locations.city', [$this->state, $this]);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published && $this->state?->is_published;
    }

    /**
     * Prevents doorway pages: a city is indexable only when service is verified and it has its own local content.
     */
    public function indexingAllowedByContent(): bool
    {
        return $this->service_verified
            && str_word_count(strip_tags($this->intro.' '.$this->local_info)) >= 120;
    }

    public function urlAttributes(): array
    {
        return ['slug', 'state_id'];
    }

    public function previousPublicUrl(): ?string
    {
        $state = State::find($this->getOriginal('state_id'));

        return $state ? route('locations.city', [$state->slug, $this->getOriginal('slug')]) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function label(): string
    {
        return $this->name.', '.$this->state?->code;
    }
}
