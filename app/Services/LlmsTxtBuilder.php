<?php

namespace App\Services;

use App\Models\City;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use App\Support\Phone;
use App\Support\RichText;
use App\Support\Settings;

/** Generates /llms.txt (and /llms-full.txt) from the same indexable content as the sitemap. */
class LlmsTxtBuilder
{
    public function __construct(private Settings $site) {}

    public function build(bool $full = false): string
    {
        $lines = [];
        $lines[] = '# '.$this->site->name();
        $lines[] = '';
        $lines[] = '> '.$this->site->get('footer_about');
        $lines[] = '';
        $lines[] = 'Audience: U.S. homeowners (residential HVAC only).';
        foreach (\App\Support\Disclaimer::paragraphs() as $p) {
            if (! $p['label']) {
                $lines[] = $p['text'];
            }
        }
        $lines[] = 'Phone: '.Phone::format($this->site->get('phone')).' · Website: '.url('/');
        $lines[] = '';

        $lines[] = '## Key pages';
        $lines[] = '- [Home]('.url('/').'): Overview of services and how the referral process works';
        $lines[] = '- [All HVAC services]('.route('services.index').')';
        $lines[] = '- [Service areas]('.route('locations.index').')';
        $lines[] = '- [HVAC resource center]('.route('blog.index').')';
        $lines[] = '- [Contact]('.route('contact').'): Call or submit a service request';
        $lines[] = '- [Disclaimer]('.route('disclaimer').'): Independent referral service disclosures';
        foreach (Page::published()->where('template', '!=', 'landing')->orderBy('title')->get() as $page) {
            if ($page->isIndexable()) {
                $lines[] = "- [{$page->title}]({$page->publicUrl()})".($page->intro ? ': '.RichText::excerpt($page->intro, 140) : '');
            }
        }
        $lines[] = '';

        $lines[] = '## Services';
        foreach (Service::published()->ordered()->get() as $service) {
            if ($service->isIndexable()) {
                $lines[] = "- [{$service->title}]({$service->publicUrl()}): {$service->short_description}";
                if ($full && $service->overview) {
                    $lines[] = '  '.RichText::excerpt($service->overview, 600);
                }
            }
        }
        $lines[] = '';

        $posts = Post::published()->with('category')->latest('published_at')->get()->filter->isIndexable();
        if ($posts->isNotEmpty()) {
            $lines[] = '## Guides and articles';
            foreach ($posts as $post) {
                $summary = $post->excerpt ? RichText::excerpt($post->excerpt, 160) : '';
                $lines[] = "- [{$post->title}]({$post->publicUrl()})".($summary ? ": {$summary}" : '');
                if ($full) {
                    $lines[] = '  '.RichText::excerpt($post->body, 1200);
                }
            }
            $lines[] = '';
        }

        $states = State::published()->orderBy('name')->get()->filter->isIndexable();
        if ($states->isNotEmpty()) {
            $lines[] = '## Service areas by state';
            foreach ($states as $state) {
                $lines[] = "- [{$state->name}]({$state->publicUrl()})".($state->intro ? ': '.RichText::excerpt($state->intro, 140) : '');
            }
            $lines[] = '';
        }

        $cities = City::published()->with('state')->orderBy('name')->get()->filter->isIndexable();
        if ($cities->isNotEmpty()) {
            $lines[] = '## City pages';
            foreach ($cities as $city) {
                $lines[] = "- [{$city->label()}]({$city->publicUrl()})";
            }
            $lines[] = '';
        }

        $lines[] = '## Optional';
        $lines[] = '- [XML sitemap]('.route('sitemap').')';
        if (! $full) {
            $lines[] = '- [Extended version of this file]('.route('llms.full').')';
        }

        return implode("\n", $lines)."\n";
    }
}
