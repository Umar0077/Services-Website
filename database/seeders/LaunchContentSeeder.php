<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Enums\Role;
use App\Models\Category;
use App\Models\City;
use App\Models\Media;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LaunchContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->locations();
        $this->posts();
    }

    private function locations(): void
    {
        $data = require __DIR__.'/data/states.php';
        $services = Service::query()->pluck('id');
        $hero = Media::query()->where('original_name', 'technician-servicing-outdoor-unit.jpg')->value('id');

        foreach ($data['content'] as $code => $content) {
            $state = State::query()->where('code', $code)->firstOrFail();
            $state->fill([
                'heading' => $content['heading'],
                'intro' => $content['intro'],
                'climate' => $content['climate'],
                'body' => $content['body'],
                'faqs' => $content['faqs'],
                'is_featured' => $content['featured'],
                'is_published' => true,
                'hero_media_id' => $state->hero_media_id ?? $hero,
                'seo_title' => "HVAC Services in {$state->name} | Heating & AC Help",
                // coverage_verified intentionally left false: a person must confirm provider coverage.
            ])->save();
            $state->services()->syncWithoutDetaching($services);

            foreach ($data['cities'][$code] ?? [] as $slug => $city) {
                $model = City::query()->updateOrCreate(['state_id' => $state->id, 'slug' => $slug], [
                    'name' => $city['name'],
                    'heading' => "HVAC services in {$city['name']}, {$code}",
                    'intro' => $city['intro'],
                    'local_info' => $city['local_info'],
                    'faqs' => $city['faqs'],
                    'is_featured' => $city['featured'],
                    'is_published' => true,
                    'service_verified' => false,
                    'seo_title' => "HVAC Services in {$city['name']}, {$code} | Heating & Cooling",
                ]);
                $model->services()->syncWithoutDetaching($services);
            }
        }
    }

    private function posts(): void
    {
        $author = User::query()->firstOrCreate(['email' => 'editorial@hvacservices.invalid'], [
            'name' => 'HVAC Services Editorial Team',
            'password' => Str::password(32),
            'role' => Role::Author,
            'is_active' => false, // byline account only; cannot sign in
            'bio' => 'Our editorial team writes practical guides to help homeowners understand their heating and cooling systems and prepare for conversations with HVAC professionals.',
        ]);

        $posts = require __DIR__.'/data/posts.php';
        foreach ($posts as $row) {
            // Close each article with contextual links to its related service pages (internal linking).
            $services = Service::query()->whereIn('slug', $row['services'])->get();
            $links = $services->map(fn (Service $s) => '<a href="/services/'.$s->slug.'">'.e(Str::lower($s->title)).'</a>')->all();
            $closing = $links
                ? '<h2>Getting professional help</h2><p>If these checks do not solve the problem, a qualified technician can diagnose the system on site. '
                    .'Learn more about '.Str::replaceLast(', ', ' and ', implode(', ', $links)).', or call HVAC Services to get connected with an available independent provider in your area.</p>'
                : '';

            $post = Post::query()->updateOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'excerpt' => $row['excerpt'],
                'body' => $row['body'].$closing,
                'faqs' => $row['faqs'],
                'status' => PostStatus::Published,
                'published_at' => now()->subDays($row['days_ago'])->setTime(9, 0),
                'is_featured' => $row['featured'],
                'author_id' => $author->id,
                'category_id' => Category::query()->where('slug', $row['category'])->value('id'),
                'featured_media_id' => Media::query()->where('original_name', $row['image'])->value('id'),
                'primary_keyword' => $row['primary_keyword'],
                'secondary_keywords' => $row['secondary_keywords'],
            ]);

            $post->tags()->sync(collect($row['tags'])->map(fn ($name) => Tag::query()->firstOrCreate(
                ['slug' => Str::slug($name)], ['name' => Str::title($name)]
            )->id));
            $post->services()->sync(Service::query()->whereIn('slug', $row['services'])->pluck('id'));
        }

        // Related posts: same category first.
        foreach (Post::all() as $post) {
            $post->relatedPosts()->sync(
                Post::query()->whereKeyNot($post->id)->where('category_id', $post->category_id)->limit(2)->pluck('id')
            );
        }
    }
}
