<?php

namespace Tests\Feature;

use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['site.media_variants' => true]);
    }

    public function test_images_are_stored_with_safe_names_and_responsive_variants(): void
    {
        $file = UploadedFile::fake()->image('My Photo <script>.JPG', 1400, 900);
        $media = app(MediaService::class)->storeUpload($file, ['alt' => 'Test']);

        $this->assertMatchesRegularExpression('#^media/\d{4}/\d{2}/my-photo-script-[a-z0-9]{6}\.jpg$#', $media->path);
        $this->assertSame([1400, 900], [$media->width, $media->height]);
        $this->assertSame([480, 720, 1080, 1400], array_keys($media->variants));
        Storage::disk('public')->assertExists($media->variants[480]);
    }

    public function test_non_image_and_svg_uploads_are_rejected(): void
    {
        foreach ([
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
            UploadedFile::fake()->createWithContent('vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->createWithContent('fake.jpg', '<?php system($_GET["x"]);'),
        ] as $file) {
            try {
                app(MediaService::class)->storeUpload($file);
                $this->fail('Upload should have been rejected: '.$file->getClientOriginalName());
            } catch (\InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
        $this->assertSame(0, \App\Models\Media::count());
    }

    public function test_deleting_media_removes_all_files(): void
    {
        $media = app(MediaService::class)->storeUpload(UploadedFile::fake()->image('a.png', 1000, 600));
        $paths = [$media->path, ...array_values($media->variants)];
        $this->assertTrue((bool) $media->delete());

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_image_used_in_page_section_is_detected_and_cannot_be_deleted(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => \App\Enums\Role::SuperAdmin]);
        $media = app(MediaService::class)->storeUpload(UploadedFile::fake()->image('section.jpg', 1000, 600), ['alt' => 'Section photo']);
        $unused = app(MediaService::class)->storeUpload(UploadedFile::fake()->image('unused.jpg', 1000, 600), ['alt' => 'Unused photo']);

        $page = \App\Models\Page::query()->create([
            'title' => 'Custom Landing',
            'slug' => 'custom-landing',
            'template' => 'standard',
            'is_published' => false, // even draft page references must protect the media
            'sections' => [
                [
                    'type' => 'image_text',
                    'data' => [
                        'eyebrow' => 'Overview',
                        'heading' => 'Section heading',
                        'media_id' => $media->id,
                        'image_side' => 'left',
                        'body' => '<p>Section text</p>',
                    ],
                ],
            ],
        ]);

        $this->assertSame(1, $media->usageCount());
        $this->assertTrue($media->isInUse());
        $this->assertFalse($admin->can('delete', $media));
        $this->assertFalse((bool) $media->delete(), 'Deleting an in-use media model must be blocked');
        $this->assertDatabaseHas('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->path);

        // Truly unused image can be deleted
        $this->assertSame(0, $unused->usageCount());
        $this->assertFalse($unused->isInUse());
        $this->assertTrue($admin->can('delete', $unused));
        $unusedPath = $unused->path;
        $this->assertTrue((bool) $unused->delete());
        $this->assertDatabaseMissing('media', ['id' => $unused->id]);
        Storage::disk('public')->assertMissing($unusedPath);

        // Once the page section reference is removed, the image can be deleted
        $page->update(['sections' => []]);
        $this->assertSame(0, $media->fresh()->usageCount());
        $this->assertTrue($admin->can('delete', $media->fresh()));
    }

    public function test_nested_page_sections_and_multiple_references_are_detected(): void
    {
        $media = app(MediaService::class)->storeUpload(UploadedFile::fake()->image('multi.jpg', 1000, 600), ['alt' => 'Multi photo']);

        \App\Models\Page::query()->create([
            'title' => 'Nested Sections Page',
            'slug' => 'nested-sections-page',
            'template' => 'standard',
            'is_published' => true,
            'hero_media_id' => $media->id,
            'sections' => [
                [
                    'type' => 'image_text',
                    'data' => [
                        'heading' => 'Block 1',
                        'media_id' => (string) $media->id,
                        'nested' => [
                            'gallery' => [
                                ['image_id' => $media->id],
                                ['media_ids' => [$media->id, 999999]],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'data' => [
                        'heading' => 'Block 2',
                        'body' => '<p><img src="/storage/'.$media->path.'" alt="Inline"></p>',
                    ],
                ],
            ],
        ]);

        \App\Models\Post::query()->create([
            'title' => 'Post with inline image',
            'slug' => 'post-with-inline-image',
            'status' => \App\Enums\PostStatus::Draft,
            'body' => '<p>See image: <img src="/storage/'.$media->path.'"></p>',
        ]);

        app(\App\Support\Settings::class)->set(['home_hero_media_id' => $media->id]);

        // 1 (page hero_media_id) + 4 (page sections: media_id, nested image_id, nested media_ids, inline path) + 1 (post inline path) + 1 (settings home_hero_media_id) = 7
        $this->assertSame(7, $media->usageCount());
        $this->assertFalse((bool) $media->delete());
        Storage::disk('public')->assertExists($media->path);
    }
}
