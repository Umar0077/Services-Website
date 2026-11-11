<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Stores images in the media library with safe, generated file names and builds responsive WebP variants.
 * Only raster images are accepted (no SVG, which can carry scripts).
 */
class MediaService
{
    public const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public const MAX_KB = 10240;

    public const WIDTHS = [480, 720, 1080, 1600];

    public const MAX_WIDTH = 2000;

    private const DIR = 'media';

    /** Register a file already written to the public disk (Filament uploads land in media/tmp first). */
    public function registerStoredFile(string $tmpPath, ?string $originalName, array $attributes = []): Media
    {
        $disk = Storage::disk('public');
        $absolute = $disk->path($tmpPath);

        $mime = mime_content_type($absolute) ?: 'application/octet-stream';
        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            $disk->delete($tmpPath);
            throw new \InvalidArgumentException('Only JPG, PNG, WebP or GIF images can be uploaded.');
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        };

        $base = Str::slug(pathinfo((string) $originalName, PATHINFO_FILENAME)) ?: 'image';
        $base = Str::limit($base, 60, '').'-'.Str::lower(Str::random(6));
        $folder = self::DIR.'/'.now()->format('Y/m');
        $path = "{$folder}/{$base}.{$extension}";
        $disk->move($tmpPath, $path);

        $media = new Media(array_merge([
            'disk' => 'public',
            'path' => $path,
            'filename' => "{$base}.{$extension}",
            'original_name' => $originalName ? mb_substr($originalName, 0, 250) : null,
            'mime_type' => $mime,
            'size' => $disk->size($path),
            'uploaded_by' => auth()->id(),
        ], $attributes));

        $this->processImage($media);
        $media->save();

        return $media;
    }

    public function storeUpload(UploadedFile $file, array $attributes = []): Media
    {
        $tmp = $file->store(self::DIR.'/tmp', 'public');

        return $this->registerStoredFile($tmp, $file->getClientOriginalName(), $attributes);
    }

    /** Used by seeders to import bundled images. */
    public function importLocalFile(string $absolutePath, array $attributes = []): Media
    {
        $tmp = self::DIR.'/tmp/'.Str::random(12).'-'.basename($absolutePath);
        Storage::disk('public')->put($tmp, file_get_contents($absolutePath));

        return $this->registerStoredFile($tmp, basename($absolutePath), $attributes);
    }

    private function processImage(Media $media): void
    {
        if ($media->mime_type === 'image/gif' || ! config('site.media_variants', true)) {
            [$media->width, $media->height] = getimagesize(Storage::disk('public')->path($media->path)) ?: [null, null];

            return;
        }

        $manager = new ImageManager(new Driver);
        $disk = Storage::disk('public');
        $image = $manager->read($disk->path($media->path));
        $media->width = $image->width();
        $media->height = $image->height();

        $variants = [];
        // Standard widths no larger than the original, plus the original width itself (capped) so large
        // screens never upscale a smaller variant.
        $widths = array_filter(self::WIDTHS, fn ($w) => $w <= $media->width);
        $widths[] = min($media->width, self::MAX_WIDTH);
        $widths = array_values(array_unique($widths));
        sort($widths);

        foreach ($widths as $width) {
            $variantPath = preg_replace('/\.\w+$/', "-{$width}.webp", $media->path);
            $encoded = $manager->read($disk->path($media->path))->scaleDown(width: $width)->toWebp(quality: 78);
            $disk->put($variantPath, (string) $encoded);
            $variants[$width] = $variantPath;
        }
        $media->variants = $variants;
    }
}
