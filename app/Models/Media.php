<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use LogsActivity;

    protected $table = 'media';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['variants' => 'array', 'size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    /** Columns across the schema that reference media rows. Used to block deleting media that is in use. */
    public const REFERENCES = [
        'posts' => ['featured_media_id', 'og_media_id'],
        'services' => ['hero_media_id', 'og_media_id'],
        'states' => ['hero_media_id', 'og_media_id'],
        'cities' => ['hero_media_id', 'og_media_id'],
        'pages' => ['hero_media_id', 'og_media_id'],
        'categories' => ['og_media_id'],
    ];

    protected static function booted(): void
    {
        static::deleted(function (Media $media) {
            $disk = Storage::disk($media->disk);
            $disk->delete(array_filter([$media->path, ...array_values($media->variants ?? [])]));
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /** "url 480w, url 960w" from generated WebP variants. */
    public function srcset(): ?string
    {
        if (empty($this->variants)) {
            return null;
        }

        return collect($this->variants)
            ->sortKeys()
            ->map(fn ($path, $width) => Storage::disk($this->disk)->url($path)." {$width}w")
            ->implode(', ');
    }

    public function usageCount(): int
    {
        $count = 0;
        foreach (self::REFERENCES as $table => $columns) {
            $count += DB::table($table)->where(function ($q) use ($columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, $this->id);
                }
            })->count();
        }

        // Images inserted into rich-text bodies.
        $count += DB::table('posts')->where('body', 'like', '%'.$this->path.'%')->count();

        return $count;
    }

    public function humanSize(): string
    {
        $size = $this->size;
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($size < 1024) {
                return round($size, 1).' '.$unit;
            }
            $size /= 1024;
        }

        return round($size, 1).' TB';
    }
}
