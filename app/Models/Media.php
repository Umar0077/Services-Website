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
        static::deleting(function (Media $media) {
            if ($media->isInUse()) {
                return false;
            }
        });

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

    public function isInUse(): bool
    {
        return $this->usageCount() > 0;
    }

    public function usageCount(): int
    {
        if (! $this->exists) {
            return 0;
        }

        $id = (int) $this->id;
        $path = trim((string) $this->path);
        $count = 0;

        foreach (self::REFERENCES as $table => $columns) {
            $count += DB::table($table)->where(function ($q) use ($columns, $id) {
                foreach ($columns as $column) {
                    $q->orWhere($column, $id);
                }
            })->count();
        }

        // CMS page sections (including nested section blocks and inline section HTML).
        $sectionRows = DB::table('pages')
            ->whereNotNull('sections')
            ->where(function ($q) use ($id, $path) {
                $q->where('sections', 'like', '%'.$id.'%');
                if ($path !== '') {
                    $q->orWhere('sections', 'like', '%'.$path.'%');
                }
            })
            ->pluck('sections');

        foreach ($sectionRows as $rawSections) {
            $decoded = is_string($rawSections) ? json_decode($rawSections, true) : $rawSections;
            if (is_array($decoded)) {
                $count += self::countSectionReferences($decoded, $id, $path);
            }
        }

        // Images inserted into rich-text / HTML bodies across published or draft content.
        if ($path !== '') {
            $count += DB::table('posts')->where(function ($q) use ($path) {
                $q->where('body', 'like', '%'.$path.'%')
                    ->orWhere('body_html', 'like', '%'.$path.'%');
            })->count();
            $count += DB::table('pages')->where('body', 'like', '%'.$path.'%')->count();
            $count += DB::table('services')->where(function ($q) use ($path) {
                $q->where('overview', 'like', '%'.$path.'%')
                    ->orWhere('details', 'like', '%'.$path.'%');
            })->count();
            $count += DB::table('states')->where(function ($q) use ($path) {
                $q->where('climate', 'like', '%'.$path.'%')
                    ->orWhere('body', 'like', '%'.$path.'%');
            })->count();
            $count += DB::table('cities')->where(function ($q) use ($path) {
                $q->where('local_info', 'like', '%'.$path.'%')
                    ->orWhere('body', 'like', '%'.$path.'%');
            })->count();
        }

        // Site settings (homepage hero media ID or branding paths).
        $site = app(\App\Support\Settings::class);
        if ((int) $site->get('home_hero_media_id') === $id) {
            $count++;
        }
        if ($path !== '') {
            foreach (['logo_light_background', 'logo_dark_background', 'favicon', 'default_og_image'] as $settingKey) {
                $settingVal = (string) $site->get($settingKey, '');
                if ($settingVal !== '' && str_contains($settingVal, $path)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /** Recursively count references to a media ID or file path inside decoded page sections. */
    public static function countSectionReferences(array $node, int $mediaId, string $path = ''): int
    {
        $count = 0;

        foreach ($node as $key => $value) {
            $isMediaIdKey = is_string($key) && (
                in_array($key, ['media_id', 'image_id', 'photo_id'], true)
                || str_ends_with($key, '_media_id')
                || str_ends_with($key, '_image_id')
            );
            $isMediaListKey = is_string($key) && (
                in_array($key, ['media_ids', 'image_ids', 'photo_ids'], true)
                || str_ends_with($key, '_media_ids')
                || str_ends_with($key, '_image_ids')
            );

            if ($isMediaIdKey) {
                if ((is_int($value) || (is_string($value) && ctype_digit($value))) && (int) $value === $mediaId) {
                    $count++;
                } elseif (is_array($value)) {
                    $count += self::countSectionReferences($value, $mediaId, $path);
                }
                continue;
            }

            if ($isMediaListKey && is_array($value)) {
                foreach ($value as $item) {
                    if ((is_int($item) || (is_string($item) && ctype_digit($item))) && (int) $item === $mediaId) {
                        $count++;
                    } elseif (is_array($item)) {
                        $count += self::countSectionReferences($item, $mediaId, $path);
                    }
                }
                continue;
            }

            if (is_array($value)) {
                $count += self::countSectionReferences($value, $mediaId, $path);
            } elseif (is_string($value) && $path !== '' && str_contains($value, $path)) {
                $count++;
            }
        }

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
