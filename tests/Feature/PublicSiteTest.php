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
}
