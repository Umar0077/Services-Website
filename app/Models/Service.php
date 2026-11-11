<?php

namespace App\Models;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFaqs, HasSeo, LogsActivity;

    protected $guarded = ['id'];

    public const GROUPS = [
        'cooling' => 'Cooling',
        'heating' => 'Heating',
        'air' => 'Air quality & maintenance',
        'emergency' => 'Emergency help',
    ];

    protected function casts(): array
    {
        return [
            'problems' => 'array',
            'options' => 'array',
            'signs' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            if ($service->isDirty(['title', 'short_description', 'overview', 'details', 'problems', 'options', 'signs', 'faqs'])) {
                $service->content_updated_at = now();
            }
        });
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('title');
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    public function relatedServices(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_related', 'service_id', 'related_service_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function states(): BelongsToMany
    {
        return $this->belongsToMany(State::class);
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class);
    }

    public function label(): string
    {
        return $this->nav_label ?: $this->title;
    }

    public function publicUrl(): string
    {
        return route('services.show', $this);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
