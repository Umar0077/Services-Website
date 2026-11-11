<x-layouts.app :seo="$seo">
    <x-page-hero :title="$state->heading ?: 'HVAC services in '.$state->name" eyebrow="{{ $state->name }}" :intro="$state->intro" :media="$state->heroMedia" :crumbs="$seo->breadcrumbs">
        <x-slot:actions>
            <x-phone class="btn btn-primary btn-lg" label="Call" />
            <a href="{{ route('contact') }}" class="btn btn-ghost-light btn-lg">Request help online</a>
        </x-slot:actions>
    </x-page-hero>

    <section class="section">
        <div class="container-site grid gap-12 lg:grid-cols-[1fr_380px] lg:gap-16">
            <div>
                @if (filled($state->climate))
                    <p class="eyebrow mb-4">Climate considerations</p>
                    <h2 class="h-section">Heating &amp; cooling in {{ $state->name }}</h2>
                    <div class="prose-article mt-8">{!! \App\Support\RichText::render($state->climate) !!}</div>
                @endif
                @if (filled($state->body))
                    <div class="prose-article mt-10">{!! \App\Support\RichText::render($state->body) !!}</div>
                @endif
            </div>
            <aside class="space-y-6 lg:sticky lg:top-[calc(var(--header-h)+24px)] lg:self-start">
                <div class="surface-teal on-dark rounded-[20px] p-7">
                    <p class="display text-[2.25rem] text-white">Request help in {{ $state->name }}</p>
                    <p class="mt-2 text-[0.9375rem] leading-relaxed">We work to connect homeowners with available independent providers. Provider coverage varies by area.</p>
                    <x-phone class="btn btn-primary btn-lg mt-6 w-full" label="Call" />
                    <x-availability-note dark class="mt-5 border-t border-white/10 pt-4" />
                </div>
                @if ($cities->isNotEmpty())
                    <div class="card p-6">
                        <h2 class="text-lg font-bold text-ink">Cities in {{ $state->name }}</h2>
                        <ul class="mt-3 divide-y divide-line">
                            @foreach ($cities as $city)
                                <li><a href="{{ $city->publicUrl() }}" class="group flex min-h-12 items-center justify-between font-medium text-ink">{{ $city->name }}<x-glyph name="arrow-right" class="size-4 text-orange-ink transition-transform group-hover:translate-x-1" /></a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    @if ($services->isNotEmpty())
        <section class="section bg-white" aria-labelledby="state-services">
            <div class="container-site">
                <x-section-heading id="state-services" eyebrow="Services" title="Home HVAC services in" :highlight="$state->name" />
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)<x-service-card :service="$service" variant="compact" />@endforeach
                </div>
            </div>
        </section>
    @endif

    @if (count($state->faqList()))
        <section class="section" aria-labelledby="state-faq">
            <div class="container-site grid gap-10 lg:grid-cols-[0.9fr_1.4fr] lg:gap-16">
                <x-section-heading id="state-faq" eyebrow="FAQ" :title="$state->name" highlight="questions" />
                <x-faq :items="$state->faqList()" id="state-faq-list" />
            </div>
        </section>
    @endif

    @if ($posts->isNotEmpty())
        <section class="section bg-white" aria-labelledby="state-guides">
            <div class="container-site">
                <x-section-heading id="state-guides" eyebrow="Guides" title="Helpful reading" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)<x-blog-card :post="$post" />@endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band availability />
</x-layouts.app>
