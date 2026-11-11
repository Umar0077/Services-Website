<?php

namespace App\Http\Controllers;

use App\Services\LlmsTxtBuilder;
use App\Services\SitemapBuilder;
use App\Support\ContentCache;

class SeoController extends Controller
{
    public const SITEMAPS = ['pages', 'services', 'posts', 'categories', 'states', 'cities'];

    public function sitemapIndex(SitemapBuilder $builder)
    {
        $xml = ContentCache::remember('sitemap-index', function () use ($builder) {
            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach (self::SITEMAPS as $type) {
                $entries = $builder->entries($type);
                if ($entries->isEmpty()) {
                    continue; // never list an empty child sitemap
                }
                $lastmod = $entries->pluck('lastmod')->filter()->max();
                $out .= '  <sitemap><loc>'.e(route('sitemap.child', $type)).'</loc>'
                    .($lastmod ? '<lastmod>'.$lastmod->format('c').'</lastmod>' : '')."</sitemap>\n";
            }

            return $out.'</sitemapindex>'."\n";
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function sitemap(string $type, SitemapBuilder $builder)
    {
        $xml = ContentCache::remember("sitemap-{$type}", function () use ($type, $builder) {
            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach ($builder->entries($type) as $entry) {
                $out .= '  <url><loc>'.e($entry['loc']).'</loc>'
                    .($entry['lastmod'] ? '<lastmod>'.$entry['lastmod']->format('c').'</lastmod>' : '')."</url>\n";
            }

            return $out.'</urlset>'."\n";
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots()
    {
        $lines = app()->isProduction() && ! config('site.block_indexing', false)
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /livewire', 'Disallow: /contact/thank-you', 'Disallow: /blog/preview', 'Allow: /', '', 'Sitemap: '.route('sitemap')]
            // Never let staging/local copies get indexed.
            : ['User-agent: *', 'Disallow: /', '', '# Non-production environment: indexing disabled.'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function llms(LlmsTxtBuilder $builder)
    {
        return response(ContentCache::remember('llms', fn () => $builder->build()), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function llmsFull(LlmsTxtBuilder $builder)
    {
        return response(ContentCache::remember('llms-full', fn () => $builder->build(full: true)), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
