<?php

namespace App\Services;

use App\Models\Category;
use App\Models\City;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use Illuminate\Support\Collection;

/**
 * Builds sitemap entries from live content. Only indexable, self-canonical, published records are included,
 * so drafts, noindex pages, unverified locations, deleted and redirected content drop out automatically.
 */
class SitemapBuilder
{
    /** @return Collection<int, array{loc: string, lastmod: ?\DateTimeInterface}> */
    public function entries(string $type): Collection
    {
        return match ($type) {
            'pages' => $this->pages(),
            'services' => $this->fromModels(Service::published()->ordered()->get()),
            'posts' => $this->fromModels(Post::published()->get()),
            'categories' => $this->fromModels(Category::query()->whereHas('posts', fn ($q) => $q->published())->get()),
            'states' => $this->fromModels(State::published()->get()),
            'cities' => $this->fromModels(City::published()->with('state')->get()),
        };
    }

    private function pages(): Collection
    {
        $latest = fn (string $model) => $model::query()->max('updated_at');

        $static = collect([
            ['loc' => url('/'), 'lastmod' => collect([$latest(Service::class), $latest(Post::class), $latest(Page::class)])->filter()->max()],
            ['loc' => route('services.index'), 'lastmod' => $latest(Service::class)],
            ['loc' => route('blog.index'), 'lastmod' => Post::published()->max('published_at')],
            ['loc' => route('locations.index'), 'lastmod' => $latest(State::class)],
            ['loc' => route('contact'), 'lastmod' => null],
            ['loc' => route('disclaimer'), 'lastmod' => \App\Models\DisclaimerVersion::query()->max('created_at')],
        ])->map(fn ($e) => ['loc' => $e['loc'], 'lastmod' => $e['lastmod'] ? \Illuminate\Support\Carbon::parse($e['lastmod']) : null]);

        return $static->merge($this->fromModels(Page::published()->get()));
    }

    private function fromModels(Collection $models): Collection
    {
        return $models
            ->filter(fn ($model) => $model->isIndexable())
            ->map(fn ($model) => ['loc' => $model->publicUrl(), 'lastmod' => $model->lastModified()])
            ->values();
    }
}
