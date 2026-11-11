<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\City;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\State;
use Tests\SeededTestCase;

class SeoTest extends SeededTestCase
{
    public function test_sitemap_index_lists_only_non_empty_child_sitemaps(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $this->assertStringContainsString('sitemap-services.xml', $xml);
        $this->assertStringContainsString('sitemap-posts.xml', $xml);
        $this->assertStringNotContainsString('sitemap-states.xml', $xml); // no state is coverage-verified yet
        simplexml_load_string($xml) ?: $this->fail('Invalid XML');
    }

    public function test_sitemap_excludes_drafts_noindex_and_unverified_locations(): void
    {
        $draft = Post::first();
        $draft->update(['status' => PostStatus::Draft]);
        $noindex = Service::where('slug', 'ac-repair')->first();
        $noindex->update(['robots_index' => false]);

        $posts = $this->get('/sitemap-posts.xml')->getContent();
        $this->assertStringNotContainsString($draft->publicUrl().'<', $posts);
        $services = $this->get('/sitemap-services.xml')->getContent();
        $this->assertStringNotContainsString('/services/ac-repair<', $services);
        $this->assertStringContainsString('/services/heating-repair<', $services);

        $this->assertStringNotContainsString('/locations/texas<', $this->get('/sitemap-states.xml')->getContent());
        $this->get('/locations/texas')->assertSee('content="noindex,follow"', false);
    }

    public function test_verifying_coverage_adds_state_to_sitemap_and_llms(): void
    {
        State::where('code', 'TX')->first()->update(['coverage_verified' => true]);

        $this->assertStringContainsString('/locations/texas<', $this->get('/sitemap-states.xml')->getContent());
        $this->assertStringContainsString('sitemap-states.xml', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('[Texas]', $this->get('/llms.txt')->getContent());
        $this->get('/locations/texas')->assertSee('content="index,follow', false);
    }

    public function test_city_needs_verification_and_enough_unique_content_to_be_indexed(): void
    {
        $city = City::where('slug', 'houston')->first();
        $this->assertFalse($city->isIndexable());

        $city->update(['service_verified' => true]);
        $this->assertTrue($city->fresh()->isIndexable());

        $city->update(['local_info' => '<p>Short.</p>', 'intro' => 'Houston.']);
        $this->assertFalse($city->fresh()->isIndexable(), 'Thin pages must not be indexed');
    }

    public function test_llms_txt_updates_when_content_is_published(): void
    {
        $post = Post::first();
        $post->update(['status' => PostStatus::Draft]);
        $this->assertStringNotContainsString($post->title, $this->get('/llms.txt')->getContent());

        $post->update(['status' => PostStatus::Published, 'published_at' => now()->subMinute()]);
        $this->assertStringContainsString($post->title, $this->get('/llms.txt')->getContent());
    }

    public function test_robots_blocks_everything_outside_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');

        $this->app['env'] = 'production';
        $this->get('/robots.txt')->assertSee('Sitemap: '.route('sitemap'))->assertSee('Disallow: /admin')->assertDontSee("Disallow: /\n", false);
    }

    public function test_pages_have_canonical_title_and_description(): void
    {
        $service = Service::where('slug', 'ac-repair')->first();
        $this->get($service->publicUrl())
            ->assertSee('<link rel="canonical" href="'.$service->publicUrl().'">', false)
            ->assertSee('<title>Residential Air Conditioning Repair | HVAC Services</title>', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"@type":"FAQPage"', false);
    }

    public function test_changing_a_slug_creates_a_permanent_redirect(): void
    {
        $service = Service::where('slug', 'ac-repair')->first();
        $service->update(['slug' => 'air-conditioner-repair']);

        $this->assertDatabaseHas('redirects', ['from_path' => '/services/ac-repair', 'to_path' => '/services/air-conditioner-repair']);
        $this->get('/services/ac-repair')->assertStatus(301)->assertRedirect('/services/air-conditioner-repair');

        // Changing again re-points the old redirect (no chains).
        $service->update(['slug' => 'home-ac-repair']);
        $this->assertSame('/services/home-ac-repair', Redirect::where('from_path', '/services/ac-repair')->value('to_path'));
    }

    public function test_scheduled_posts_publish_when_due(): void
    {
        $post = Post::first();
        $post->forceFill(['status' => PostStatus::Scheduled, 'published_at' => now()->subMinute()])->saveQuietly();
        $this->get($post->publicUrl())->assertNotFound();

        $this->artisan('posts:publish-scheduled')->assertSuccessful();
        $this->get($post->publicUrl())->assertOk();
    }
}
