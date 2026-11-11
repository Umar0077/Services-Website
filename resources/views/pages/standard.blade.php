<x-layouts.app :seo="$seo">
    <x-page-hero :title="$page->heading ?: $page->title" :intro="$page->intro" :media="$page->heroMedia" :crumbs="$seo->breadcrumbs" />

    <x-sections :sections="$page->sections ?? []" />

    @if (filled($body))
        <section class="section"><div class="container-site"><div class="prose-article mx-auto max-w-[760px]">{!! $body !!}</div></div></section>
    @endif

    <x-cta-band :heading="$page->cta_heading" :text="$page->cta_text" />
</x-layouts.app>
