<x-layouts.app :seo="$seo">
    <x-page-hero compact :crumbs="$seo->breadcrumbs"
        :eyebrow="$category ? 'Category' : ($tag ? 'Topic' : 'HVAC resource center')"
        :title="$category?->name ?? ($tag ? $tag->name : 'Guides for homeowners')"
        :intro="$category?->description ?? 'Practical, plain-English guides to help you understand your heating and cooling system and make confident decisions.'">
        <form action="{{ route('blog.index') }}" method="GET" role="search" class="mt-8 max-w-xl">
            <label for="blog-search" class="sr-only">Search articles</label>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <x-glyph name="search" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted" />
                    <input id="blog-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Search articles, e.g. furnace noise" class="input pl-12">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </x-page-hero>

    <section class="section-tight">
        <div class="container-site">
            {{-- Category filter --}}
            @if ($categories->isNotEmpty())
                <nav aria-label="Blog categories" class="-mx-5 overflow-x-auto px-5 pb-2 sm:mx-0 sm:px-0">
                    <ul class="flex gap-2 whitespace-nowrap">
                        <li><a href="{{ route('blog.index') }}" @class(['inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold transition-colors', 'border-teal-900 bg-teal-900 text-white' => ! $category && ! $tag && $search === '', 'border-line bg-white text-ink hover:border-teal-900/30' => $category || $tag || $search !== '']) @if(! $category && ! $tag && $search === '') aria-current="page" @endif>All articles</a></li>
                        @foreach ($categories as $cat)
                            @php($isActive = $category?->is($cat))
                            <li><a href="{{ route('blog.category', $cat) }}" @class(['inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold transition-colors', 'border-teal-900 bg-teal-900 text-white' => $isActive, 'border-line bg-white text-ink hover:border-teal-900/30' => ! $isActive]) @if($isActive) aria-current="page" @endif>{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            @if ($search !== '')
                <p class="mt-8 text-[0.9375rem]" role="status">
                    {{ $posts->total() }} {{ Str::plural('result', $posts->total()) }} for <strong class="text-ink">“{{ $search }}”</strong>.
                    <a href="{{ route('blog.index') }}" class="text-link">Clear search</a>
                </p>
            @endif

            @if ($featured)
                <div class="mt-10"><x-blog-card :post="$featured" featured /></div>
            @endif

            @if ($posts->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <div class="reveal"><x-blog-card :post="$post" /></div>
                    @endforeach
                </div>
                {{ $posts->links() }}
            @elseif (! $featured)
                <div class="mt-12">
                    <x-empty-state title="No articles found" :text="$search !== '' ? 'Try a different word, or browse all articles.' : 'New guides are on the way.'">
                        <a href="{{ route('blog.index') }}" class="btn btn-outline">Browse all articles</a>
                    </x-empty-state>
                </div>
            @endif
        </div>
    </section>

    <x-cta-band />
</x-layouts.app>
