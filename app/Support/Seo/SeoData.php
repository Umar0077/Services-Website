<?php

namespace App\Support\Seo;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Everything the <head> needs for one page. Built by controllers, rendered by components/seo.blade.php. */
class SeoData
{
    /** @var array<int, array{name: string, url: string}> */
    public array $breadcrumbs = [];

    /** @var array<int, array> JSON-LD graphs */
    public array $schema = [];

    public function __construct(
        public string $title,
        public string $description,
        public string $canonical,
        public string $robots = 'index,follow',
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $image = null,
        public string $ogType = 'website',
        public bool $appendSiteName = true,
    ) {}

    public static function make(string $title, ?string $description = null, ?string $canonical = null): self
    {
        $site = app(Settings::class);

        return new self(
            title: $title,
            description: Str::limit($description ?: (string) $site->get('default_seo_description'), 300, ''),
            canonical: $canonical ?: url()->current(),
        );
    }

    /** From any model using HasSeo, with fallbacks for title/description/image. */
    public static function forModel(Model $model, string $fallbackTitle, ?string $fallbackDescription = null, ?string $fallbackImage = null): self
    {
        $seo = self::make($model->seo_title ?: $fallbackTitle, $model->seo_description ?: $fallbackDescription, $model->canonical());
        $seo->robots = $model->robotsDirective();
        $seo->ogTitle = $model->og_title ?: null;
        $seo->ogDescription = $model->og_description ?: null;
        $seo->image = $model->ogMedia?->url() ?? $fallbackImage;
        $seo->appendSiteName = blank($model->seo_title); // custom SEO titles are used exactly as written

        return $seo;
    }

    public function breadcrumbs(array $crumbs): self
    {
        $this->breadcrumbs = array_merge([['name' => 'Home', 'url' => url('/')]], $crumbs);
        $this->schema[] = Schema::breadcrumbs($this->breadcrumbs);

        return $this;
    }

    public function schema(?array $graph): self
    {
        if ($graph) {
            $this->schema[] = $graph;
        }

        return $this;
    }

    public function fullTitle(): string
    {
        $site = app(Settings::class);
        $name = $site->name();

        if (! $this->appendSiteName || Str::contains($this->title, $name)) {
            return $this->title;
        }

        return $this->title.$site->get('title_separator', ' | ').$name;
    }

    public function imageUrl(): string
    {
        $image = $this->image ?: app(Settings::class)->asset('default_og_image');

        return $image && ! str_starts_with($image, 'http') ? url($image) : (string) $image;
    }
}
