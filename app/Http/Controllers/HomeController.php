<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use App\Support\Settings;

class HomeController extends Controller
{
    public function __invoke(Settings $site)
    {
        $services = Service::published()->ordered()->with('heroMedia')->get();

        $states = State::published()->orderBy('name')->withCount(['cities' => fn ($q) => $q->where('is_published', true)])->get();
        $featuredCities = City::published()->where('is_featured', true)->with('state')->orderBy('name')->limit(12)->get();

        $posts = Post::published()->with(['featuredMedia', 'category', 'author'])->latest('published_at')->limit(3)->get();

        $faqs = collect($site->get('home_faqs', []))->filter(fn ($f) => filled($f['question'] ?? null))->values()->all();

        $seo = SeoData::make((string) $site->get('default_seo_title'), (string) $site->get('default_seo_description'), url('/'));
        $seo->appendSiteName = false;
        $seo->schema(Schema::organization())->schema(Schema::website())->schema(Schema::faq($faqs));

        $heroMedia = \App\Models\Media::find($site->get('home_hero_media_id'))
            ?? \App\Models\Media::query()->where('original_name', 'technician-servicing-condenser.jpg')->first();
        $whyMedia = \App\Models\Media::query()->where('original_name', 'technician-reviewing-options-with-customer.jpg')->first();
        $seo->image = $heroMedia?->url();

        return view('pages.home', compact('services', 'states', 'featuredCities', 'posts', 'faqs', 'seo', 'heroMedia', 'whyMedia'));
    }
}
