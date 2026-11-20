<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use App\Support\Disclaimer;
use App\Support\TargetingCheck;
use Tests\SeededTestCase;

class PublicSiteTest extends SeededTestCase
{
    public function test_every_public_page_renders(): void
    {
        $urls = ['/', '/services', '/blog', '/blog?q=furnace', '/locations', '/contact', '/disclaimer', '/about',
            '/call-hvac', '/privacy-policy', '/terms', '/cookie-policy', '/service-disclosures', '/blog/category/heating'];

        foreach (Service::published()->get() as $s) {
            $urls[] = $s->publicUrl();
        }
        foreach (Post::published()->get() as $p) {
            $urls[] = $p->publicUrl();
        }
        foreach (State::published()->get() as $s) {
            $urls[] = $s->publicUrl();
        }
        foreach (City::published()->with('state')->get() as $c) {
            $urls[] = $c->publicUrl();
        }

        foreach ($urls as $url) {
            $this->get($url)->assertOk()->assertSee('<h1', false);
        }
    }

    public function test_unpublished_and_removed_content_returns_404(): void
    {
        $this->get('/services/commercial-hvac')->assertNotFound();
        $this->get('/locations/alabama')->assertNotFound(); // seeded but unpublished
        $this->get('/does-not-exist')->assertNotFound();

        $post = Post::first();
        $post->update(['status' => 'draft']);
        $this->get('/blog/'.$post->slug)->assertNotFound();
    }

    public function test_legacy_urls_and_trailing_slashes_redirect_permanently(): void
    {
        $this->get('/about.html')->assertRedirect('/about')->assertStatus(301);
        $this->get('/index.html')->assertRedirect('/')->assertStatus(301);
        $this->get('/landing/contact.html')->assertRedirect('/contact')->assertStatus(301);
    }

    public function test_trailing_slashes_redirect_to_the_canonical_url(): void
    {
        // The test client strips trailing slashes, so call the middleware directly.
        $request = \Illuminate\Http\Request::create('/services/?utm_source=x');
        $response = app(\App\Http\Middleware\HandleRedirects::class)->handle($request, fn () => response('next'));
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(url('/services?utm_source=x'), $response->headers->get('Location'));
    }

    public function test_approved_disclaimer_appears_exactly_in_footer_and_page(): void
    {
        $approved = 'HVAC Services is an independent referral service to assist homeowners in connecting with local service providers. All contractors/providers are independent and HVAC Services does not endorse, represent, or assume liability for any work performed. It is the responsibility of the homeowner to verify that the hired contractor furnishes the necessary license and insurance required for the work being performed. All persons depicted in a photo or video are actors or models and not contractors listed on HVAC Services.';
        $availability = 'Same-day and 24/7 emergency services are subject to provider participation, location, technician availability, and demand. Availability may vary by market, dispatch volume, and appointment capacity.';

        $this->assertStringContainsString($approved, Disclaimer::text());
        foreach (['/', '/disclaimer', '/call-hvac', '/services/emergency-hvac', '/locations/texas/houston'] as $url) {
            $this->get($url)->assertSee($approved)->assertSee($availability);
        }
        // Contact form links to the disclaimer before submission.
        $this->get('/contact')->assertSee(route('disclaimer'));
    }

    public function test_call_hvac_landing_page_structure_tracking_and_compliance(): void
    {
        $response = $this->get('/call-hvac?utm_source=google&utm_medium=cpc&utm_campaign=hvac-repair');
        $response->assertOk()
            ->assertCookie(\App\Http\Middleware\ResolveTrackingNumber::ATTRIBUTION_COOKIE)
            ->assertSee('content="noindex,follow"', false)
            ->assertSee('Residential Heating &amp; Cooling Assistance', false)
            ->assertSee('Heating or Cooling Problems?')
            ->assertSee('Connect With Local HVAC Service Providers')
            ->assertSee('Call for HVAC Assistance')
            ->assertSee('Call for HVAC Help')
            ->assertSee('Air Conditioning Repair')
            ->assertSee('Heating &amp; Furnace Repair', false)
            ->assertSee('HVAC Installation &amp; Replacement', false)
            ->assertSee('Heat Pumps &amp; Mini-Splits', false)
            ->assertSee('HVAC Maintenance')
            ->assertSee('Local Service Connections')
            ->assertSee('href="tel:+18882175445"', false)
            ->assertSee('(888) 217-5445')
            ->assertSee(route('disclaimer'))
            ->assertDontSee('aria-label="Main"', false);

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertSame(3, substr_count($html, 'data-track="call"'));

        // Excluded from XML sitemap while noindex
        $this->assertStringNotContainsString('/call-hvac', $this->get('/sitemap-pages.xml')->getContent());

        // Legacy .html URLs redirect with query string preserved
        $this->get('/call-hvac.html?utm_source=google')->assertRedirect('/call-hvac?utm_source=google')->assertStatus(301);
    }

    public function test_published_content_targets_homeowners_only(): void
    {
        $texts = collect()
            ->merge(Service::published()->get()->map(fn ($s) => implode(' ', [$s->title, $s->short_description, $s->overview, $s->details, json_encode($s->problems), json_encode($s->options), json_encode($s->faqs)])))
            ->merge(Post::published()->get()->map(fn ($p) => $p->title.' '.$p->body))
            ->merge(State::published()->get()->map(fn ($s) => $s->intro.' '.$s->climate.' '.$s->body))
            ->merge(City::published()->get()->map(fn ($c) => $c->intro.' '.$c->local_info));

        foreach ($texts as $text) {
            $warnings = array_filter(TargetingCheck::run($text), fn ($w) => str_contains($w, 'commercial'));
            $this->assertSame([], array_values($warnings), mb_substr(strip_tags($text), 0, 120));
        }
        $this->get('/')->assertDontSee('Commercial HVAC');
    }

    public function test_no_fabricated_review_or_rating_markup(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString('aggregateRating', $html);
        $this->assertStringNotContainsString('"Review"', $html);
        $this->assertStringContainsString('"@type":"Organization"', $html);
    }

    public function test_rich_text_is_sanitized(): void
    {
        $post = Post::first();
        $post->update(['body' => '<p>Safe</p><script>alert(1)</script><a href="javascript:alert(1)">x</a><img src=x onerror=alert(1)><p onclick="x()">y</p>']);

        $html = $this->get($post->publicUrl())->getContent();
        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringContainsString('<p>Safe</p>', $html);
    }

    public function test_deploy_check_fails_when_hot_file_exists_and_compiled_assets_load(): void
    {
        $hotPath = public_path('hot');
        @unlink($hotPath);

        $this->artisan('deploy:check')->assertSuccessful();

        $homeHtml = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('/build/assets/', $homeHtml);
        $this->assertStringNotContainsString(':5173', $homeHtml);

        file_put_contents($hotPath, 'http://localhost:5173');
        try {
            $this->artisan('deploy:check')->assertFailed();
        } finally {
            @unlink($hotPath);
        }
    }

    public function test_html_and_visual_blog_revisions_restore_correctly_and_sanitize_html(): void
    {
        $post = Post::published()->first();
        $originalStatus = $post->status;
        $originalPublishedAt = $post->published_at?->toIso8601String();
        $post->update([
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO Description',
            'body_format' => 'html',
            'body_html' => '<h2>HTML Version 1</h2><p>First HTML paragraph.</p><script>alert("xss")</script>',
        ]);

        $this->assertSame('<h2>HTML Version 1</h2><p>First HTML paragraph.</p><script>alert("xss")</script>', $post->fresh()->body);

        // Save Version 2 in HTML mode (creates revision for HTML Version 1)
        $post->update([
            'title' => 'Updated HTML Title V2',
            'excerpt' => 'Updated Excerpt V2',
            'body_format' => 'html',
            'body_html' => '<h2>HTML Version 2</h2><p>Second HTML paragraph.</p>',
        ]);

        $htmlV1Revision = $post->revisions()->where('body', 'like', '%HTML Version 1%')->firstOrFail();
        $this->assertSame('html', $htmlV1Revision->body_format);

        // Restore HTML Version 1 revision
        $post->restoreRevision($htmlV1Revision);
        $post->refresh();

        $this->assertSame('html', $post->body_format);
        $this->assertStringContainsString('HTML Version 1', $post->body);
        $this->assertStringContainsString('HTML Version 1', $post->body_html);
        $this->assertStringNotContainsString('HTML Version 2', $post->body);
        $this->assertSame('Custom SEO Title', $post->seo_title);
        $this->assertSame('Custom SEO Description', $post->seo_description);
        $this->assertSame($originalStatus, $post->status);
        $this->assertSame($originalPublishedAt, $post->published_at?->toIso8601String());

        // Public rendering of restored HTML revision is sanitized
        $rendered = $this->get($post->publicUrl())->assertOk()->getContent();
        $this->assertStringContainsString('HTML Version 1', $rendered);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $rendered);

        // Switch to visual editor ('rich') mode and save Version 3
        $post->update([
            'body_format' => 'rich',
            'body' => '<h2>Visual Version 3</h2><p>Visual editor content.</p>',
        ]);
        $this->assertSame('rich', $post->fresh()->body_format);
        $this->assertSame('<h2>Visual Version 3</h2><p>Visual editor content.</p>', $post->fresh()->body);
        $this->assertSame('<h2>Visual Version 3</h2><p>Visual editor content.</p>', $post->fresh()->body_html);

        // Switch back to HTML mode and save Version 4
        $post->update([
            'body_format' => 'html',
            'body_html' => '<h2>HTML Version 4</h2><p>Fourth version in HTML.</p>',
        ]);

        // Restore the Visual Version 3 revision across modes
        $richV3Revision = $post->revisions()->where('body', 'like', '%Visual Version 3%')->firstOrFail();
        $this->assertSame('rich', $richV3Revision->body_format);
        $post->restoreRevision($richV3Revision);
        $post->refresh();

        $this->assertSame('rich', $post->body_format);
        $this->assertSame('<h2>Visual Version 3</h2><p>Visual editor content.</p>', $post->body);
        $this->assertSame('<h2>Visual Version 3</h2><p>Visual editor content.</p>', $post->body_html);
    }

    public function test_blog_pagination_has_no_duplicates_across_pages_same_dates_categories_and_search(): void
    {
        $category = \App\Models\Category::first();
        $sameTimestamp = now()->subHours(2);

        // Create 15 additional published posts with identical published_at to test tie-breaking and 3 pages of pagination
        for ($i = 1; $i <= 15; $i++) {
            Post::query()->create([
                'title' => "Pagination Test Article {$i}",
                'slug' => "pagination-test-article-{$i}",
                'excerpt' => "Unique searchable keyword pagetesttoken excerpt {$i}.",
                'body' => "<p>Unique searchable keyword pagetesttoken body {$i}.</p>",
                'status' => \App\Enums\PostStatus::Published,
                'published_at' => $sameTimestamp,
                'is_featured' => $i === 7,
                'category_id' => $category->id,
            ]);
        }

        $totalPublished = Post::published()->count();
        $this->assertGreaterThan(19, $totalPublished);

        // Collect all post IDs across every pagination page of /blog
        $seenIds = [];
        $page1 = $this->get('/blog');
        $page1->assertOk();
        $featured = $page1->viewData('featured');
        $this->assertNotNull($featured, 'Page 1 must display a featured post');
        $seenIds[] = $featured->id;

        $paginator1 = $page1->viewData('posts');
        $this->assertSame($totalPublished - 1, $paginator1->total());
        foreach ($paginator1->items() as $item) {
            $this->assertNotContains($item->id, $seenIds, "Post {$item->id} duplicated on page 1");
            $seenIds[] = $item->id;
        }

        $lastPage = $paginator1->lastPage();
        $this->assertGreaterThanOrEqual(3, $lastPage);

        for ($p = 2; $p <= $lastPage; $p++) {
            $resp = $this->get("/blog?page={$p}")->assertOk();
            $this->assertNull($resp->viewData('featured'), "Featured hero card must not repeat on page {$p}");
            $paginator = $resp->viewData('posts');
            $this->assertSame($totalPublished - 1, $paginator->total());
            foreach ($paginator->items() as $item) {
                $this->assertNotContains($item->id, $seenIds, "Post {$item->id} duplicated on page {$p}");
                $seenIds[] = $item->id;
            }
        }

        $this->assertCount($totalPublished, $seenIds, 'Every published post must appear exactly once across all pagination pages');

        // Category pagination: no duplicates between page 1 and page 2
        $catPage1 = $this->get(route('blog.category', $category))->assertOk();
        $catSeen = [$catPage1->viewData('featured')->id];
        foreach ($catPage1->viewData('posts')->items() as $item) {
            $this->assertNotContains($item->id, $catSeen);
            $catSeen[] = $item->id;
        }
        $catPage2 = $this->get(route('blog.category', ['category' => $category->slug, 'page' => 2]))->assertOk();
        foreach ($catPage2->viewData('posts')->items() as $item) {
            $this->assertNotContains($item->id, $catSeen, "Category post {$item->id} duplicated on page 2");
            $catSeen[] = $item->id;
        }

        // Search pagination: preserves query parameter and has no duplicates across page 1 and page 2
        $searchPage1 = $this->get('/blog?q=pagetesttoken')->assertOk();
        $this->assertNull($searchPage1->viewData('featured'));
        $searchPaginator1 = $searchPage1->viewData('posts');
        $this->assertSame(15, $searchPaginator1->total());
        $this->assertStringContainsString('q=pagetesttoken', $searchPaginator1->url(2));
        $searchIds1 = collect($searchPaginator1->items())->pluck('id')->all();

        $searchPage2 = $this->get('/blog?q=pagetesttoken&page=2')->assertOk();
        $searchIds2 = collect($searchPage2->viewData('posts')->items())->pluck('id')->all();
        $this->assertSame([], array_intersect($searchIds1, $searchIds2));
        $this->assertCount(15, array_unique(array_merge($searchIds1, $searchIds2)));
    }

    public function test_five_new_service_pages_render_with_seo_cms_sitemap_and_idempotent_seeding(): void
    {
        $newServices = [
            'hvac-installation' => [
                'title' => 'Residential HVAC Installation Services',
                'h1' => 'Professional HVAC installation options for your home',
                'seo_title' => 'Residential HVAC Installation Services | HVAC Services',
            ],
            'hvac-replacement' => [
                'title' => 'Residential HVAC Replacement Services',
                'h1' => 'Ready to replace your home\'s HVAC system?',
                'seo_title' => 'Residential HVAC Replacement Services | HVAC Services',
            ],
            'heating' => [
                'title' => 'Residential Heating Services',
                'h1' => 'Keep your home comfortable with reliable heating options',
                'seo_title' => 'Residential Heating Services | Furnace & Home Heating Help',
            ],
            'heat-pump' => [
                'title' => 'Residential Heat Pump Services',
                'h1' => 'Explore energy-efficient heating and cooling with heat pumps',
                'seo_title' => 'Residential Heat Pump Services | Installation, Repair & Tune-Ups',
            ],
            'ductless' => [
                'title' => 'Residential Ductless Mini-Split Services',
                'h1' => 'Flexible heating and cooling for your home',
                'seo_title' => 'Residential Ductless Mini-Split Services | Room-by-Room HVAC',
            ],
        ];

        $countBefore = Service::count();
        $this->seed(\Database\Seeders\CoreSeeder::class);
        $this->assertSame($countBefore, Service::count(), 'CoreSeeder must be idempotent and never duplicate services');

        $sitemapXml = $this->get('/sitemap-services.xml')->assertOk()->getContent();
        $llmsTxt = $this->get('/llms.txt')->assertOk()->getContent();
        $directoryHtml = $this->get('/services')->assertOk()->getContent();

        $seenSeoTitles = [];
        $seenDescriptions = [];

        foreach ($newServices as $slug => $expected) {
            $service = Service::where('slug', $slug)->firstOrFail();
            $this->assertTrue($service->is_published);
            $this->assertSame($expected['title'], $service->title);
            $this->assertGreaterThanOrEqual(3, $service->relatedServices()->published()->count());
            $this->assertGreaterThanOrEqual(3, count($service->faqList()));

            $url = "/services/{$slug}";
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();

            $response->assertSee($expected['h1'])
                ->assertSee('<title>'.e($expected['seo_title']).'</title>', false)
                ->assertSee('<link rel="canonical" href="'.$service->publicUrl().'">', false)
                ->assertSee('"@type":"BreadcrumbList"', false)
                ->assertSee('"@type":"Service"', false)
                ->assertSee('"@type":"FAQPage"', false)
                ->assertSee('How the referral process works');

            $this->assertSame(1, substr_count($html, '<h1'));
            $this->assertNotContains($service->seo_title, $seenSeoTitles);
            $this->assertNotContains($service->seo_description, $seenDescriptions);
            $seenSeoTitles[] = $service->seo_title;
            $seenDescriptions[] = $service->seo_description;

            $this->assertStringContainsString($service->publicUrl().'<', $sitemapXml);
            $this->assertStringContainsString($service->publicUrl(), $llmsTxt);
            $this->assertStringContainsString('/services/'.$slug, $directoryHtml);

            // Trailing slash redirects cleanly with 301
            $req = \Illuminate\Http\Request::create("/services/{$slug}/");
            $redir = app(\App\Http\Middleware\HandleRedirects::class)->handle($req, fn () => response('next'));
            $this->assertSame(301, $redir->getStatusCode());
            $this->assertSame(url($url), $redir->headers->get('Location'));
        }

        // Unpublishing a service removes it from public access and sitemap
        $target = Service::where('slug', 'hvac-installation')->firstOrFail();
        $target->update(['is_published' => false]);
        $this->get('/services/hvac-installation')->assertNotFound();
        $this->assertStringNotContainsString('/services/hvac-installation<', $this->get('/sitemap-services.xml')->getContent());
        $target->update(['is_published' => true]);
    }

    public function test_complete_website_navigation_and_internal_link_audit(): void
    {
        $sourcePages = [
            '/',
            '/about',
            '/services',
            '/services/hvac-installation',
            '/services/hvac-replacement',
            '/services/heating',
            '/services/heat-pump',
            '/services/ductless',
            '/services/ac-repair',
            '/locations',
            '/locations/texas',
            '/locations/texas/houston',
            '/blog',
            Post::published()->first()->publicUrl(),
            '/contact',
            '/disclaimer',
            '/call-hvac',
            '/privacy-policy',
            '/terms',
            '/cookie-policy',
            '/service-disclosures',
        ];

        $checkedUrls = [];
        $failures = [];
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        foreach ($sourcePages as $source) {
            $resp = $this->get($source);
            if ($resp->status() !== 200) {
                $failures[] = "Source page {$source} returned HTTP {$resp->status()}";
                continue;
            }

            $html = $resp->getContent();
            preg_match_all('/<a\b[^>]*\bhref="([^"]*)"/i', $html, $matches);

            foreach ($matches[1] as $rawHref) {
                $href = html_entity_decode(trim($rawHref));

                if ($href === '' || $href === '#') {
                    $failures[] = "Page {$source} contains an empty or bare '#' href attribute";
                    continue;
                }

                if (str_starts_with($href, 'tel:') || str_starts_with($href, 'mailto:')) {
                    continue;
                }

                if (str_starts_with($href, '#')) {
                    $anchorId = substr($href, 1);
                    if (! str_contains($html, 'id="'.$anchorId.'"')) {
                        $failures[] = "Page {$source} links to missing anchor #{$anchorId}";
                    }
                    continue;
                }

                $parsed = parse_url($href);
                if (isset($parsed['host']) && $parsed['host'] !== $appHost) {
                    continue; // external link
                }

                $path = $parsed['path'] ?? '/';
                if (str_ends_with(strtolower($path), '.html')) {
                    $failures[] = "Page {$source} contains stale .html link: {$href}";
                    continue;
                }

                $targetUrl = $path.(isset($parsed['query']) ? '?'.$parsed['query'] : '');
                if (! isset($checkedUrls[$targetUrl])) {
                    $targetResp = $this->get($targetUrl);
                    $checkedUrls[$targetUrl] = $targetResp->status();
                }

                if ($checkedUrls[$targetUrl] !== 200) {
                    $failures[] = "Broken link on {$source} -> {$targetUrl} (HTTP {$checkedUrls[$targetUrl]})";
                }
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
        $this->assertGreaterThan(35, count($checkedUrls));
    }
}
