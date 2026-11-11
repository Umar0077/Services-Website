<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;

class LocationController extends Controller
{
    public function index()
    {
        // Only published states are linked. Unpublished states are listed (without links) so the directory is complete.
        $states = State::query()->orderBy('name')
            ->withCount(['cities' => fn ($q) => $q->where('is_published', true)])
            ->get(['id', 'name', 'code', 'slug', 'is_published', 'is_featured']);
        $featuredCities = City::published()->where('is_featured', true)->with('state')->orderBy('name')->get();

        $seo = SeoData::make(
            'HVAC Service Areas by State and City',
            'Browse HVAC service areas across the United States. Find your state or city to learn about local heating and cooling needs and request help from an independent provider.',
            route('locations.index'),
        )->breadcrumbs([['name' => 'Locations', 'url' => route('locations.index')]]);

        return view('pages.locations.index', compact('states', 'featuredCities', 'seo'));
    }

    public function state(State $state)
    {
        abort_unless($state->is_published, 404);

        $state->load(['heroMedia', 'ogMedia']);
        $cities = $state->cities()->where('is_published', true)->orderBy('name')->get()->each->setRelation('state', $state);
        $services = $state->services()->published()->ordered()->get();
        if ($services->isEmpty()) {
            $services = Service::published()->ordered()->get();
        }
        $posts = Post::published()->with(['featuredMedia', 'category', 'author'])->latest('published_at')->limit(3)->get();

        $seo = SeoData::forModel($state, $state->heading ?: "HVAC Services in {$state->name}", $state->intro, $state->heroMedia?->url())
            ->breadcrumbs([
                ['name' => 'Locations', 'url' => route('locations.index')],
                ['name' => $state->name, 'url' => $state->publicUrl()],
            ])
            ->schema(Schema::faq($state->faqList()));

        return view('pages.locations.state', compact('state', 'cities', 'services', 'posts', 'seo'));
    }

    public function city(State $state, City $city)
    {
        abort_unless($state->is_published && $city->is_published, 404);

        $city->setRelation('state', $state);
        $city->load(['heroMedia', 'ogMedia']);
        $services = $city->services()->published()->ordered()->get();
        if ($services->isEmpty()) {
            $services = $state->services()->published()->ordered()->get();
        }
        if ($services->isEmpty()) {
            $services = Service::published()->ordered()->get();
        }
        $nearby = $state->cities()->where('is_published', true)->whereKeyNot($city->id)->orderBy('name')->limit(8)->get()->each->setRelation('state', $state);
        $posts = Post::published()->with(['featuredMedia', 'category', 'author'])->latest('published_at')->limit(3)->get();

        $seo = SeoData::forModel($city, $city->heading ?: "HVAC Services in {$city->name}, {$state->code}", $city->intro, $city->heroMedia?->url())
            ->breadcrumbs([
                ['name' => 'Locations', 'url' => route('locations.index')],
                ['name' => $state->name, 'url' => $state->publicUrl()],
                ['name' => $city->name, 'url' => $city->publicUrl()],
            ])
            ->schema(Schema::faq($city->faqList()));

        return view('pages.locations.city', compact('state', 'city', 'services', 'nearby', 'posts', 'seo'));
    }
}
