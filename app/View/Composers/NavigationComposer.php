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
    public function compose(View $view): void
    {
        $nav = ContentCache::remember('navigation', fn () => [
            'services' => Service::published()->ordered()->get()->map(fn (Service $s) => [
                'slug' => $s->slug,
                'label' => $s->label(),
                'url' => route('services.show', $s, false),
                'icon' => $s->icon,
                'group' => $s->group,
                'short' => $s->short_description,
            ])->groupBy('group')->toArray(),
            'states' => State::published()->orderByDesc('is_featured')->orderBy('name')->get()->map(fn (State $s) => [
                'slug' => $s->slug,
                'name' => $s->name,
                'url' => route('locations.state', $s, false),
                'featured' => $s->is_featured,
            ])->all(),
            'cities' => City::published()->where('is_featured', true)->with('state')->orderBy('name')->limit(8)->get()->map(fn (City $c) => [
                'name' => $c->label(),
                'url' => route('locations.city', [$c->state, $c], false),
            ])->all(),
            'legal' => Page::published()->where('show_in_footer', true)->orderBy('title')->get()->map(fn (Page $p) => [
                'title' => $p->title,
                'url' => route('pages.show', $p, false),
            ])->all(),
            'about' => ($aboutSlug = Page::query()->where('system_key', 'about')->published()->value('slug'))
                ? '/'.$aboutSlug
                : '/about',
        ], 3600);

        $view->with('nav', $nav);
    }
}
