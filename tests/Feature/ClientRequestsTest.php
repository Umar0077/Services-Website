<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\PostStatus;
use App\Enums\Role;
use App\Filament\Resources;
use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\State;
use Filament\Facades\Filament;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\SeededTestCase;

/** Error alerts, admin pagination, and HTML blog posts (client requests, Nov 2026). */
class ClientRequestsTest extends SeededTestCase
{
    // ---- Error alerts -------------------------------------------------------------------------------

    public function test_expired_session_on_website_form_returns_with_input_and_a_toast(): void
    {
        Route::post('/_test-csrf', fn () => throw new TokenMismatchException)->middleware('web');

        $this->from('/contact')->post('/_test-csrf', ['name' => 'Kept Name'])
            ->assertRedirect('/contact')
            ->assertSessionHas('toast', fn ($t) => $t['title'] === 'Your session expired')
            ->assertSessionHasInput('name', 'Kept Name');
    }

    public function test_too_many_requests_on_website_form_returns_with_a_toast(): void
    {
        Route::post('/_test-throttle', fn () => throw new ThrottleRequestsException)->middleware('web');

        $this->from('/contact')->post('/_test-throttle')
            ->assertRedirect('/contact')
            ->assertSessionHas('toast', fn ($t) => $t['type'] === 'warning');
    }

    public function test_server_error_on_website_form_shows_toast_in_production_mode(): void
    {
        config(['app.debug' => false]);
        Route::post('/_test-boom', fn () => throw new \RuntimeException('boom'))->middleware('web');

        $this->from('/contact')->post('/_test-boom')
            ->assertRedirect('/contact')
            ->assertSessionHas('toast', fn ($t) => $t['type'] === 'error');
    }

    public function test_website_shows_toast_for_form_validation_errors(): void
    {
        $this->followingRedirects()->from('/contact')->post('/contact', ['name' => ''])
            ->assertSee('Please check the form');
    }

    public function test_admin_pages_load_the_error_toast_script(): void
    {
        $this->actingAs($this->user(Role::SuperAdmin));
        // Toasts are drawn client-side (id="hvac-toasts"), so they work even when the server cannot be reached.
        $this->get('/admin')->assertOk()
            ->assertSee('interceptRequest', false)
            ->assertSee('id="hvac-toasts"', false)
            ->assertSee('Connection problem', false)
            ->assertSee('Session expired', false);
    }

    public function test_admin_validation_failure_sends_a_toast(): void
    {
        $this->actingAs($this->user(Role::SuperAdmin));

        Livewire::test(Resources\Posts\Pages\CreatePost::class)
            ->fillForm(['title' => '', 'slug' => ''])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required'])
            ->assertNotified();
    }

    // ---- Admin pagination ---------------------------------------------------------------------------

    public function test_every_admin_list_paginates_without_errors(): void
    {
        $this->actingAs($this->user(Role::SuperAdmin));
        $this->makeManyRecords();

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $list = $resource::getPages()['index']->getPage();
            $test = Livewire::test($list)->assertSuccessful();

            $test->set('tableRecordsPerPage', 10)->call('gotoPage', 2)->assertSuccessful();
            $test->call('gotoPage', 1)->assertSuccessful();
        }

        // A full page 2 shows the right rows.
        Livewire::test(Resources\Leads\Pages\ListLeads::class)
            ->set('tableRecordsPerPage', 10)
            ->call('gotoPage', 2)
            ->assertCanSeeTableRecords(Lead::query()->latest()->skip(10)->take(10)->get())
            ->assertCanNotSeeTableRecords(Lead::query()->latest()->take(10)->get());
    }

    private function makeManyRecords(): void
    {
        $state = State::first();
        for ($i = 1; $i <= 30; $i++) {
            Lead::create(['name' => "Lead {$i}", 'phone' => '2025550100', 'zip' => '77002', 'status' => LeadStatus::New, 'created_at' => now()->subMinutes($i)]);
            Post::create(['title' => "Extra post {$i}", 'slug' => "extra-post-{$i}", 'status' => PostStatus::Draft, 'body' => '<p>x</p>']);
            Redirect::create(['from_path' => "/old-{$i}", 'to_path' => '/']);
            Media::create(['path' => "media/x-{$i}.jpg", 'filename' => "x-{$i}.jpg", 'mime_type' => 'image/jpeg']);
            ActivityLog::record('test', null, "Entry {$i}");
            $state->cities()->create(['name' => "Town {$i}", 'slug' => "town-{$i}"]);
        }
        Post::first()->update(['title' => 'Edited once']); // creates a revision with a user
    }

    // ---- HTML blog posts ----------------------------------------------------------------------------

    public function test_posts_can_be_written_in_html_and_are_sanitized(): void
    {
        $this->actingAs($this->user(Role::Editor));

        Livewire::test(Resources\Posts\Pages\CreatePost::class)
            ->fillForm([
                'title' => 'Written In HTML',
                'slug' => 'written-in-html',
                'body_format' => 'html',
                'body_html' => '<h2>Section One</h2><p class="lead">Hello <strong>homeowners</strong>.</p><script>alert(1)</script><img src="/x.jpg" onerror="alert(2)" alt="x">',
                'status' => PostStatus::Published->value,
                'published_at' => now()->subMinute(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'written-in-html')->firstOrFail();
        $this->assertSame('html', $post->body_format);
        $this->assertStringContainsString('<h2>Section One</h2>', $post->body); // body kept in sync for search, SEO, revisions

        $html = $this->get('/blog/written-in-html')->assertOk()->getContent();
        $this->assertStringContainsString('id="section-one"', $html); // heading anchors still work
        $this->assertStringContainsString('<strong>homeowners</strong>', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString('onerror', $html);
    }

    public function test_switching_to_html_starts_from_the_existing_article(): void
    {
        $this->actingAs($this->user(Role::Editor));
        $post = Post::where('slug', 'signs-your-furnace-needs-repair')->first();

        $test = Livewire::test(Resources\Posts\Pages\EditPost::class, ['record' => $post->getRouteKey()])
            ->set('data.body_format', 'html');

        $this->assertStringContainsString('Unusual noises', (string) $test->get('data.body_html'));
    }
}
