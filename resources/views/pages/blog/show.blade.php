<x-layouts.app :seo="$seo">
    @if ($preview)
        <div class="bg-[#FEF0C7] text-[#7A2E0E]" role="status">
            <div class="container-site flex flex-wrap items-center justify-between gap-2 py-3 text-sm font-medium">
                <span>Preview — this post is <strong>{{ $post->status->getLabel() }}</strong> and not visible to the public.</span>
                <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $post]) }}" class="underline underline-offset-2">Back to editor</a>
            </div>
        </div>
    @endif

    <article>
        <header class="bg-white">
            <div class="container-site pt-10 pb-10 md:pt-14">
                <x-breadcrumbs :items="$seo->breadcrumbs" class="mb-8" />
                <div class="max-w-[860px]">
                    @if ($post->category)
                        <a href="{{ route('blog.category', $post->category) }}" class="eyebrow hover:underline">{{ $post->category->name }}</a>
                    @endif
                    {{-- Article titles use Inter: long titles read better than in a condensed display face. --}}
                    <h1 class="mt-4 text-[2.25rem] leading-[1.1] font-extrabold tracking-[-0.025em] text-ink md:text-[3.25rem]">{{ $post->title }}</h1>
                    @if ($post->excerpt)
                        <p class="lead mt-5 max-w-[720px]">{{ $post->excerpt }}</p>
                    @endif
                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-line pt-6 text-sm">
                        @if ($post->author)
                            <span class="flex items-center gap-3">
                                <span class="inline-flex size-10 items-center justify-center rounded-full bg-teal-900 font-semibold text-white" aria-hidden="true">{{ Str::of($post->author->name)->explode(' ')->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->implode('') }}</span>
                                <span><span class="block font-semibold text-ink">{{ $post->author->name }}</span>@if($post->author->job_title)<span class="text-muted">{{ $post->author->job_title }}</span>@endif</span>
                            </span>
                        @endif
                        @if ($post->published_at)
                            <span class="flex items-center gap-2 text-muted"><x-glyph name="calendar" class="size-4" />Published <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time></span>
                        @endif
                        @if ($post->content_updated_at && $post->published_at && $post->content_updated_at->gt($post->published_at->copy()->addDay()))
                            <span class="flex items-center gap-2 text-muted"><x-glyph name="refresh" class="size-4" />Updated <time datetime="{{ $post->content_updated_at->toDateString() }}">{{ $post->content_updated_at->format('F j, Y') }}</time></span>
                        @endif
                        <span class="flex items-center gap-2 text-muted"><x-glyph name="clock" class="size-4" />{{ $post->reading_minutes }} min read</span>
                    </div>
                </div>
            </div>
            @if ($post->featuredMedia)
                <div class="container-site">
                    <figure class="mx-auto max-w-[1100px]">
                        <x-picture :media="$post->featuredMedia" sizes="(min-width: 1100px) 1100px, 100vw" eager class="aspect-[2/1] w-full rounded-[20px] object-cover" />
                        @if ($post->featuredMedia->caption)<figcaption class="mt-3 text-sm text-muted">{{ $post->featuredMedia->caption }}</figcaption>@endif
                    </figure>
                </div>
            @endif
        </header>

        <div class="bg-white pb-16 md:pb-24">
            <div class="container-site grid gap-12 pt-12 lg:grid-cols-[260px_minmax(0,720px)] lg:justify-center lg:gap-16 lg:pt-16">
                {{-- Table of contents --}}
                <aside class="lg:sticky lg:top-[calc(var(--header-h)+24px)] lg:self-start" aria-label="In this article">
                    @if (count($toc) >= 3)
                        <nav x-data="{ open: window.innerWidth >= 1024 }" class="rounded-[var(--radius-card)] border border-line bg-page p-5">
                            <button type="button" class="flex w-full items-center justify-between text-left text-sm font-semibold tracking-[0.06em] text-ink uppercase lg:pointer-events-none" @click="open = !open" :aria-expanded="open.toString()">
                                In this article <x-glyph name="chevron-down" class="size-4 lg:hidden" />
                            </button>
                            <ol x-show="open" x-collapse class="mt-4 space-y-2.5 border-l border-line text-[0.9375rem]">
                                @foreach ($toc as $item)
                                    <li class="{{ $item['level'] === 3 ? 'pl-7' : 'pl-4' }}"><a href="#{{ $item['id'] }}" class="-ml-px block border-l-2 border-transparent pl-0 leading-snug text-body hover:text-teal-900">{{ $item['text'] }}</a></li>
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                    <div class="surface-teal on-dark mt-6 hidden rounded-[var(--radius-card)] p-5 lg:block">
                        <p class="font-semibold text-white">Need help with your system?</p>
                        <p class="mt-1.5 text-sm">Call to get connected with a local provider.</p>
                        <x-phone class="btn btn-primary btn-sm mt-4 w-full" />
                    </div>
                </aside>

                <div class="min-w-0">
                    <div class="prose-article">{!! $body !!}</div>

                    {{-- Contextual CTA --}}
                    <div class="mt-12 grid gap-5 rounded-[20px] border border-mint-200 bg-mint p-6 md:grid-cols-[1fr_auto] md:items-center md:p-8">
                        <div>
                            <p class="text-lg font-bold text-ink">Talk to a local HVAC provider</p>
                            <p class="mt-1 text-[0.9375rem]">Describe what is happening and we will work to connect you with an available independent provider.</p>
                        </div>
                        <x-phone class="btn btn-primary" label="Call" />
                    </div>

                    @if (count($faqs))
                        <section class="mt-14" aria-labelledby="article-faq">
                            <h2 id="article-faq" class="text-[1.625rem] font-bold text-ink">Frequently asked questions</h2>
                            <x-faq :items="$faqs" id="article-faq-list" class="mt-6" />
                        </section>
                    @endif

                    @if ($post->services->isNotEmpty() || $post->tags->isNotEmpty())
                        <div class="mt-12 border-t border-line pt-8">
                            @if ($post->services->isNotEmpty())
                                <p class="text-sm font-semibold tracking-[0.06em] text-muted uppercase">Related services</p>
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($post->services as $service)
                                        <li><a href="{{ $service->publicUrl() }}" class="inline-flex min-h-10 items-center gap-2 rounded-full border border-line px-4 text-sm font-medium text-ink hover:border-orange/60"><x-glyph :name="$service->icon" class="size-4 text-teal-700" />{{ $service->label() }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                            @if ($post->tags->isNotEmpty())
                                <p class="mt-6 text-sm text-muted">Topics:
                                    @foreach ($post->tags as $t)<a href="{{ route('blog.tag', $t) }}" class="text-link">{{ $t->name }}</a>@if(! $loop->last), @endif @endforeach
                                </p>
                            @endif
                        </div>
                    @endif

                    @if ($post->author?->bio)
                        <div class="mt-10 flex gap-4 rounded-[var(--radius-card)] bg-page p-6">
                            <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-full bg-teal-900 font-semibold text-white" aria-hidden="true">{{ Str::substr($post->author->name, 0, 1) }}</span>
                            <div>
                                <p class="font-semibold text-ink">About {{ $post->author->name }}</p>
                                <p class="mt-1 text-[0.9375rem] leading-relaxed">{{ $post->author->bio }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="section" aria-labelledby="related-posts">
            <div class="container-site">
                <x-section-heading id="related-posts" eyebrow="Keep reading" title="Related articles" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)<x-blog-card :post="$item" />@endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band />
</x-layouts.app>
