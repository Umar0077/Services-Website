<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFaqs, HasSeo, LogsActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'show_toc' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            // HTML mode: the hand-written code is the article. Keep body and body_html in sync so
            // rendering, search, revisions, SEO checks, and direct updates to either column stay consistent.
            if ($post->body_format === 'html') {
                if ($post->isDirty('body') && ! $post->isDirty('body_html')) {
                    $post->body_html = (string) $post->body;
                } else {
                    $post->body = (string) ($post->body_html ?? $post->body);
                    $post->body_html = (string) $post->body;
                }
            } else {
                $post->body_html = (string) $post->body;
            }

            // Authors cannot publish or schedule, whatever the submitted form says.
            $user = auth()->user();
            if ($user && ! $user->can('publish', Post::class) && $post->isDirty('status')) {
                $post->status = $post->exists ? $post->getOriginal('status') : PostStatus::Draft;
            }

            $words = str_word_count(strip_tags((string) $post->body));
            $post->reading_minutes = max(1, (int) ceil($words / 225));

            if ($post->isDirty(['title', 'body', 'excerpt'])) {
                $post->content_updated_at = now();
            }

            // A "published" post dated in the future is really scheduled, and vice versa.
            if ($post->status === PostStatus::Published && $post->published_at?->isFuture()) {
                $post->status = PostStatus::Scheduled;
            }
            if ($post->status === PostStatus::Published && ! $post->published_at) {
                $post->published_at = now();
            }
        });

        // Keep a revision whenever the content changes.
        static::updated(function (Post $post) {
            if ($post->wasChanged(['title', 'body', 'excerpt'])) {
                $post->revisions()->create([
                    'user_id' => auth()->id(),
                    'title' => $post->getOriginal('title'),
                    'excerpt' => $post->getOriginal('excerpt'),
                    'body' => $post->getOriginal('body'),
                    'body_format' => $post->getOriginal('body_format') ?: 'rich',
                    'created_at' => now(),
                ]);
            }
        });
    }

    /** Restore a previous revision while preserving SEO metadata, slug, and publishing status. */
    public function restoreRevision(PostRevision $revision): void
    {
        $format = $revision->body_format ?: ($this->body_format ?: 'rich');

        $this->update([
            'title' => $revision->title,
            'excerpt' => $revision->excerpt,
            'body_format' => $format,
            'body' => $revision->body,
            'body_html' => $revision->body,
        ]);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published)->where('published_at', '<=', now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function relatedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_related', 'post_id', 'related_post_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class)->latest('created_at');
    }

    public function publicUrl(): string
    {
        return route('blog.show', $this);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === PostStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now())
            && ! $this->trashed();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
