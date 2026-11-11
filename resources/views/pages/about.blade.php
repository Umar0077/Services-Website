<x-layouts.app :seo="$seo">
    <x-page-hero :title="$page->heading ?: $page->title" eyebrow="About us" :intro="$page->intro" :media="$page->heroMedia" :crumbs="$seo->breadcrumbs">
        <x-slot:actions>
            <x-phone class="btn btn-primary btn-lg" label="Call" />
            <a href="{{ route('services.index') }}" class="btn btn-ghost-light btn-lg">Explore services</a>
        </x-slot:actions>
    </x-page-hero>

    <x-sections :sections="$page->sections ?? []" />

    @if (filled($body))
        <section class="section"><div class="container-site"><div class="prose-article mx-auto max-w-[760px]">{!! $body !!}</div></div></section>
    @endif

    @if ($services->isNotEmpty())
        <section class="section bg-white" aria-labelledby="about-services">
            <div class="container-site">
                <x-section-heading id="about-services" eyebrow="Services" title="Help for every part of" highlight="your home system" />
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)<x-service-card :service="$service" variant="compact" />@endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section-tight" aria-labelledby="about-coverage">
        <div class="container-site grid gap-8 rounded-[20px] border border-line bg-white p-6 md:grid-cols-[1fr_auto] md:items-center md:p-10">
            <div>
                <h2 id="about-coverage" class="text-[1.625rem] font-bold text-ink">Service coverage</h2>
                <p class="mt-2 max-w-2xl">We accept requests from homeowners across the United States. Provider participation varies by location, so availability is confirmed when we connect you.</p>
                <x-availability-note class="mt-4 max-w-2xl" />
            </div>
            <a href="{{ route('locations.index') }}" class="btn btn-dark">Browse service areas <x-glyph name="arrow-right" /></a>
        </div>
    </section>

    <x-cta-band :heading="$page->cta_heading" :text="$page->cta_text" />
</x-layouts.app>
