<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::published()->ordered()->with('heroMedia')->get();
        $groups = $services->groupBy('group');

        $seo = SeoData::make(
            'Home HVAC Services: Heating, Cooling & Air Quality',
            'Explore home heating, air conditioning, heat pump, ductless, air quality, and maintenance services, and get connected with an available local provider.',
            route('services.index'),
        )->breadcrumbs([['name' => 'Services', 'url' => route('services.index')]]);

        return view('pages.services.index', compact('services', 'groups', 'seo'));
    }

    public function show(Service $service)
    {
        abort_unless($service->is_published, 404);

        $service->load(['heroMedia', 'ogMedia', 'relatedServices' => fn ($q) => $q->published()->ordered()->with('heroMedia')]);

        $posts = $service->posts()->published()->with(['featuredMedia', 'category', 'author'])->latest('published_at')->limit(3)->get();
        if ($posts->isEmpty()) {
            $posts = Post::published()->with(['featuredMedia', 'category', 'author'])->latest('published_at')->limit(3)->get();
        }

        // Locations where this service is listed, falling back to featured published locations.
        $states = $service->states()->published()->orderBy('name')->get();
        $cities = $service->cities()->published()->with('state')->orderBy('name')->limit(8)->get();
        if ($states->isEmpty() && $cities->isEmpty()) {
            $states = State::published()->where('is_featured', true)->orderBy('name')->limit(8)->get();
            $cities = City::published()->where('is_featured', true)->with('state')->orderBy('name')->limit(8)->get();
        }

        $seo = SeoData::forModel($service, $service->title, $service->short_description, $service->heroMedia?->url())
            ->breadcrumbs([
                ['name' => 'Services', 'url' => route('services.index')],
                ['name' => $service->title, 'url' => $service->publicUrl()],
            ])
            ->schema(Schema::service($service))
            ->schema(Schema::faq($service->faqList()));

        return view('pages.services.show', compact('service', 'posts', 'states', 'cities', 'seo'));
    }
}
