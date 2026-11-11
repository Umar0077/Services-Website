<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\Role;
use App\Filament\Pages\Settings;
use App\Filament\Resources;
use App\Models\Post;
use App\Models\User;
use App\Support\Disclaimer;
use Tests\SeededTestCase;

class AuthorizationTest extends SeededTestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/leads')->assertRedirect('/admin/login');
    }

    public function test_inactive_users_cannot_enter_the_admin(): void
    {
        $user = User::factory()->create(['role' => Role::Admin, 'is_active' => false]);
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_authors_are_limited_to_their_own_drafts(): void
    {
        $author = $this->user(Role::Author);
        $this->actingAs($author);

        $this->get(Resources\Posts\PostResource::getUrl('index'))->assertOk()->assertDontSee('Why Is My AC Running');
        // Other people's posts are outside the author's query scope, so they are not even revealed (404).
        $this->assertContains($this->get(Resources\Posts\PostResource::getUrl('edit', ['record' => Post::first()]))->status(), [403, 404]);
        $this->get(Resources\Leads\LeadResource::getUrl('index'))->assertForbidden();
        $this->get(Resources\Services\ServiceResource::getUrl('index'))->assertForbidden();
        $this->get(Settings::getUrl())->assertForbidden();

        // Even a crafted save cannot publish.
        $post = Post::create(['title' => 'Draft by author', 'slug' => 'draft-by-author', 'status' => PostStatus::Published, 'author_id' => $author->id]);
        $this->assertSame(PostStatus::Draft, $post->fresh()->status);
    }

    public function test_editors_manage_content_but_not_users_or_settings(): void
    {
        $this->actingAs($this->user(Role::Editor));

        $this->get(Resources\Services\ServiceResource::getUrl('index'))->assertOk();
        $this->get(Resources\Leads\LeadResource::getUrl('index'))->assertOk();
        $this->get(Resources\Users\UserResource::getUrl('index'))->assertForbidden();
        $this->get(Resources\Redirects\RedirectResource::getUrl('index'))->assertForbidden();
        $this->get(Settings::getUrl())->assertForbidden();
    }

    public function test_admins_cannot_manage_super_admins(): void
    {
        $admin = $this->user(Role::Admin);
        $super = $this->user(Role::SuperAdmin);

        $this->assertFalse($admin->can('update', $super));
        $this->assertFalse($admin->can('delete', $super));
        $this->assertFalse($admin->can('delete', $admin));
        $this->assertTrue($super->can('update', $admin));
        $this->assertArrayNotHasKey('super_admin', $this->actingAs($admin) ? Resources\Users\UserResource::assignableRoles() : []);
    }

    public function test_system_pages_and_used_media_cannot_be_deleted(): void
    {
        $admin = $this->user(Role::SuperAdmin);
        $about = \App\Models\Page::where('system_key', 'about')->first();
        $this->assertFalse($admin->can('delete', $about));

        $used = \App\Models\Service::first()->heroMedia;
        $this->assertFalse($admin->can('delete', $used));
    }

    public function test_disclaimer_changes_are_versioned(): void
    {
        $admin = $this->user(Role::SuperAdmin);
        $original = Disclaimer::text();

        Disclaimer::update($original."\n\nUpdated paragraph.", $admin, 'Counsel review');

        $this->assertDatabaseCount('disclaimer_versions', 2); // original + new
        $this->assertStringContainsString('Updated paragraph.', Disclaimer::text());
        $this->get('/disclaimer')->assertSee('Updated paragraph.');
        $this->assertFalse(Disclaimer::update(Disclaimer::text(), $admin), 'Unchanged text is not re-versioned');
    }
}
