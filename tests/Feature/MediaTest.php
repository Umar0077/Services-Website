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
        $media->delete();

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }
}
