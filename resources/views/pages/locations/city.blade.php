<x-layouts.app :seo="$seo">
    <x-page-hero :title="$city->heading ?: 'HVAC services in '.$city->name.', '.$state->code" eyebrow="{{ $city->name }}, {{ $state->name }}" :intro="$city->intro" :media="$city->heroMedia ?? $state->heroMedia" :crumbs="$seo->breadcrumbs">
        <x-slot:actions>
            <x-phone class="btn btn-primary btn-lg" label="Call" />
            <a href="{{ route('contact') }}" class="btn btn-ghost-light btn-lg">Request help online</a>
        </x-slot:actions>
    </x-page-hero>

    <section class="section">
        <div class="container-site grid gap-12 lg:grid-cols-[1fr_380px] lg:gap-16">
            <div class="min-w-0">
                <p class="eyebrow mb-4">Local considerations</p>
                <h2 class="h-section">Home comfort in {{ $city->name }}</h2>
                <div class="prose-article mt-8">{!! \App\Support\RichText::render($city->local_info) !!}</div>
                @if (filled($city->body))
                    <div class="prose-article mt-10">{!! \App\Support\RichText::render($city->body) !!}</div>
                @endif
            </div>
            <aside class="min-w-0 space-y-6 lg:sticky lg:top-[calc(var(--header-h)+24px)] lg:self-start">
                <div class="surface-teal on-dark rounded-[20px] p-7">
                    <p class="display text-[2.25rem] text-white">Request help in {{ $city->name }}</p>
                    <p class="mt-2 text-[0.9375rem] leading-relaxed">Call to describe the problem. We work to connect you with an available independent provider; coverage varies by area.</p>
                    <x-phone class="btn btn-primary btn-lg mt-6 w-full" label="Call" />
                    <x-availability-note dark class="mt-5 border-t border-white/10 pt-4" />
                </div>
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-ink">More in {{ $state->name }}</h2>
                    <ul class="mt-3 divide-y divide-line">
                        <li><a href="{{ $state->publicUrl() }}" class="group flex min-h-12 items-center justify-between font-semibold text-ink">{{ $state->name }} overview<x-glyph name="arrow-right" class="size-4 text-orange-ink" /></a></li>
                        @foreach ($nearby as $other)
                            <li><a href="{{ $other->publicUrl() }}" class="group flex min-h-12 items-center justify-between font-medium text-ink">{{ $other->name }}<x-glyph name="arrow-right" class="size-4 text-orange-ink transition-transform group-hover:translate-x-1" /></a></li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </section>

    @if ($services->isNotEmpty())
        <section class="section bg-white" aria-labelledby="city-services">
            <div class="container-site">
                <x-section-heading id="city-services" eyebrow="Services" title="Home HVAC services in" :highlight="$city->name" />
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)<x-service-card :service="$service" variant="compact" />@endforeach
                </div>
            </div>
        </section>
    @endif

    @if (count($city->faqList()))
        <section class="section" aria-labelledby="city-faq">
            <div class="container-site grid gap-10 lg:grid-cols-[0.9fr_1.4fr] lg:gap-16">
                <x-section-heading id="city-faq" eyebrow="FAQ" :title="$city->name" highlight="questions" />
                <x-faq :items="$city->faqList()" id="city-faq-list" />
            </div>
        </section>
    @endif

    @if ($posts->isNotEmpty())
        <section class="section bg-white" aria-labelledby="city-guides">
            <div class="container-site">
                <x-section-heading id="city-guides" eyebrow="Guides" title="Helpful reading" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)<x-blog-card :post="$post" />@endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band availability />
</x-layouts.app>
