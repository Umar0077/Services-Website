<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Service;
use App\Support\RichText;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;

class PageController extends Controller
{
    public function __invoke(Page $page)
    {
        abort_unless($page->is_published, 404);

        $page->load(['heroMedia', 'ogMedia']);

        $seo = SeoData::forModel($page, $page->title, $page->intro ?: RichText::excerpt($page->body), $page->heroMedia?->url())
            ->breadcrumbs([['name' => $page->title, 'url' => $page->publicUrl()]]);

        $body = RichText::render($page->body, withAnchors: $page->template === 'legal');
        $toc = $page->template === 'legal' ? RichText::tableOfContents($page->body) : [];

        $view = match ($page->template) {
            'legal' => 'pages.legal',
            'landing' => 'pages.landing',
            default => $page->system_key === 'about' ? 'pages.about' : 'pages.standard',
        };

        if ($page->template === 'landing') {
            $seo->schema(Schema::organization());
        }

        $services = in_array($view, ['pages.about', 'pages.landing'], true)
            ? Service::published()->ordered()->get()
            : collect();

        return view($view, compact('page', 'seo', 'body', 'toc', 'services'));
    }
}
