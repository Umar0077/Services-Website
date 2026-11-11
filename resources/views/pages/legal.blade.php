<x-layouts.app :seo="$seo">
    <x-page-hero compact :title="$page->heading ?: $page->title" eyebrow="Legal" :intro="$page->intro" :crumbs="$seo->breadcrumbs" />

    <section class="bg-white py-14 md:py-20">
        <div class="container-site grid gap-12 lg:grid-cols-[240px_minmax(0,760px)] lg:justify-center lg:gap-16">
            <aside class="lg:sticky lg:top-[calc(var(--header-h)+24px)] lg:self-start" aria-label="On this page">
                @if (count($toc) >= 3)
                    <p class="text-sm font-semibold tracking-[0.06em] text-ink uppercase">On this page</p>
                    <ol class="mt-4 space-y-2 border-l border-line text-[0.9375rem]">
                        @foreach ($toc as $item)
                            @continue($item['level'] !== 2)
                            <li class="pl-4"><a href="#{{ $item['id'] }}" class="text-body hover:text-teal-900">{{ $item['text'] }}</a></li>
                        @endforeach
                    </ol>
                @endif
                <p class="mt-8 text-sm text-muted">Last changed {{ $page->lastModified()?->format('F j, Y') }}</p>
            </aside>
            <div class="prose-article min-w-0">{!! $body !!}</div>
        </div>
    </section>
</x-layouts.app>
