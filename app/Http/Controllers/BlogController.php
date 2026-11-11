<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\RichText;
use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(mb_substr((string) $request->query('q', ''), 0, 100));

        return $this->listing($request, Post::published(), $search, null, null, SeoData::make(
            $search ? "Search results for “{$search}”" : 'HVAC Resource Center: Guides & Advice',
            'Practical guides on air conditioning, heating, heat pumps, indoor air quality, and HVAC maintenance for U.S. homeowners and property managers.',
            route('blog.index'),
        ));
    }

    public function category(Request $request, Category $category)
    {
        $seo = SeoData::forModel($category, $category->name.' Articles', $category->description);

        return $this->listing($request, Post::published()->where('category_id', $category->id), '', $category, null, $seo);
    }

    public function tag(Request $request, Tag $tag)
    {
        $seo = SeoData::make('Articles tagged “'.$tag->name.'”', null, route('blog.tag', $tag));
        $seo->robots = 'noindex,follow'; // thin archive pages

        return $this->listing($request, Post::published()->whereHas('tags', fn ($q) => $q->whereKey($tag->id)), '', null, $tag, $seo);
    }

    private function listing(Request $request, Builder $query, string $search, ?Category $category, ?Tag $tag, SeoData $seo)
    {
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%"));
            $seo->robots = 'noindex,follow';
        }

        $page = max(1, (int) $request->query('page', 1));
        $showFeatured = $search === '' && ! $tag && $page === 1;

        $featured = $showFeatured
            ? (clone $query)->with(['featuredMedia', 'category', 'author'])->orderByDesc('is_featured')->latest('published_at')->first()
            : null;

        $posts = $query->with(['featuredMedia', 'category', 'author'])
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->id))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        if ($page > 1) {
            $seo->canonical = $posts->url($page);
            $seo->title .= " — Page {$page}";
        }

        $crumbs = [['name' => 'Blog', 'url' => route('blog.index')]];
        if ($category) {
            $crumbs[] = ['name' => $category->name, 'url' => route('blog.category', $category)];
        }
        if ($tag) {
            $crumbs[] = ['name' => $tag->name, 'url' => route('blog.tag', $tag)];
        }
        $seo->breadcrumbs($crumbs);

        $categories = Category::query()->whereHas('posts', fn ($q) => $q->published())->orderBy('sort_order')->orderBy('name')->get();

        return view('pages.blog.index', compact('posts', 'featured', 'categories', 'category', 'tag', 'search', 'seo'));
    }

    public function show(Post $post)
    {
        abort_unless($post->isPubliclyVisible(), 404);

        return $this->article($post);
    }

    /** Signed, authenticated preview of drafts and scheduled posts from the admin. */
    public function preview(Request $request, Post $post)
    {
        abort_unless($request->user()?->can('view', $post), 403);

        $response = $this->article($post, preview: true);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    private function article(Post $post, bool $preview = false)
    {
        $post->load(['featuredMedia', 'ogMedia', 'category', 'author', 'tags', 'services' => fn ($q) => $q->published()]);

        $related = $post->relatedPosts()->published()->with(['featuredMedia', 'category', 'author'])->limit(3)->get();
        if ($related->count() < 3) {
            $related = $related->merge(
                Post::published()->whereKeyNot($post->id)->whereNotIn('id', $related->pluck('id'))
                    ->when($post->category_id, fn ($q) => $q->orderByRaw('category_id = ? desc', [$post->category_id]))
                    ->with(['featuredMedia', 'category', 'author'])->latest('published_at')->limit(3 - $related->count())->get()
            );
        }

        $body = RichText::render($post->body, withAnchors: true);
        $toc = $post->show_toc ? RichText::tableOfContents($post->body) : [];
        $faqs = $post->faqList();

        $seo = SeoData::forModel($post, $post->title, $post->excerpt ?: RichText::excerpt($post->body), $post->featuredMedia?->url());
        $seo->ogType = 'article';
        if ($preview) {
            $seo->robots = 'noindex,nofollow';
        }

        $crumbs = [['name' => 'Blog', 'url' => route('blog.index')]];
        if ($post->category) {
            $crumbs[] = ['name' => $post->category->name, 'url' => route('blog.category', $post->category)];
        }
        $crumbs[] = ['name' => $post->title, 'url' => $post->publicUrl()];
        $seo->breadcrumbs($crumbs)->schema(Schema::article($post))->schema(Schema::faq($faqs));

        return response()->view('pages.blog.show', compact('post', 'related', 'body', 'toc', 'faqs', 'seo', 'preview'));
    }
}
