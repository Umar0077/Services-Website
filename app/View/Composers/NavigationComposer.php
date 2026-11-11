<?php

namespace App\View\Composers;

use App\Models\City;
use App\Models\Page;
use App\Models\Service;
use App\Models\State;
use App\Support\ContentCache;
use Illuminate\View\View;

/** Header and footer navigation, built from published content and cached until content changes. */
class NavigationComposer
{
    private static ?array $nav = null;

    public function compose(View $view): void
    {
        self::$nav ??= ContentCache::remember('navigation', fn () => [
            'services' => Service::published()->ordered()->get()->map(fn (Service $s) => [
                'label' => $s->label(),
                'url' => $s->publicUrl(),
                'icon' => $s->icon,
                'group' => $s->group,
                'short' => $s->short_description,
            ])->groupBy('group')->toArray(),
            'states' => State::published()->orderByDesc('is_featured')->orderBy('name')->get()->map(fn (State $s) => [
                'name' => $s->name,
                'url' => $s->publicUrl(),
                'featured' => $s->is_featured,
            ])->all(),
            'cities' => City::published()->where('is_featured', true)->with('state')->orderBy('name')->limit(8)->get()->map(fn (City $c) => [
                'name' => $c->label(),
                'url' => $c->publicUrl(),
            ])->all(),
            'legal' => Page::published()->where('show_in_footer', true)->orderBy('title')->get()->map(fn (Page $p) => [
                'title' => $p->title,
                'url' => $p->publicUrl(),
            ])->all(),
            'about' => Page::urlFor('about', '/about'),
        ], 3600);

        $view->with('nav', self::$nav);
    }
}
