<x-layouts.app :seo="$seo">
    @php
        $mentionsAvailability = $service->group === 'emergency'
            || preg_match('/same[- ]day|24\/7|after[- ]hours|emergency/i', $service->hero_text.' '.$service->overview.' '.$service->details);
        $signs = collect($service->signs ?? [])->map(fn ($s) => is_array($s) ? ($s['text'] ?? '') : $s)->filter();
    @endphp

    <x-page-hero :title="$service->hero_heading ?: $service->title" :eyebrow="\App\Models\Service::GROUPS[$service->group] ?? 'Service'"
        :intro="$service->hero_text ?: $service->short_description" :media="$service->heroMedia" :crumbs="$seo->breadcrumbs">
        <x-slot:actions>
            <x-phone class="btn btn-primary btn-lg" label="Call" />
            <a href="{{ route('contact', ['service' => $service->slug]) }}" class="btn btn-ghost-light btn-lg">Request {{ strtolower($service->label()) }} help</a>
        </x-slot:actions>
        @if ($mentionsAvailability)
            <x-availability-note dark class="mt-8 max-w-[620px]" />
        @endif
    </x-page-hero>

    {{-- Overview + sticky request card --}}
    <section class="section">
        <div class="container-site grid gap-12 lg:grid-cols-[1fr_380px] lg:gap-16">
            <div>
                <p class="eyebrow mb-4">Overview</p>
                <h2 class="h-section">About {{ strtolower($service->label()) }}</h2>
                <div class="prose-article mt-8">{!! \App\Support\RichText::render($service->overview) !!}</div>

                @if (! empty($service->problems))
                    <h2 class="mt-16 text-[1.75rem] font-bold tracking-[-0.01em] text-ink">Common problems</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($service->problems as $problem)
                            <div class="card reveal p-5">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex size-9 items-center justify-center rounded-lg bg-orange-50 text-orange-ink"><x-glyph name="alert" class="size-[18px]" /></span>
                                    <h3 class="font-bold text-ink">{{ $problem['title'] ?? '' }}</h3>
                                </div>
                                <p class="mt-3 text-[0.9375rem] leading-relaxed">{{ $problem['text'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="lg:sticky lg:top-[calc(var(--header-h)+24px)] lg:self-start" aria-label="Get help">
                <div class="surface-teal on-dark rounded-[20px] p-7">
                    <p class="display text-[2.25rem] text-white">Get connected</p>
                    <p class="mt-2 text-[0.9375rem] leading-relaxed">Describe the problem and we will work to connect you with an available independent provider in your area.</p>
                    <x-phone class="btn btn-primary btn-lg mt-6 w-full" label="Call" />
                    <a href="{{ route('contact', ['service' => $service->slug]) }}" class="btn btn-ghost-light mt-3 w-full">Send a request online</a>
                    <p class="mt-5 border-t border-white/10 pt-4 text-[0.8125rem] leading-relaxed text-on-dark-muted">
                        Free referral service. Providers are independent. <a href="{{ route('disclaimer') }}" class="text-white underline underline-offset-2">Disclaimer</a>
                    </p>
                </div>
                @if ($signs->isNotEmpty())
                    <div class="card mt-6 p-6">
                        <h2 class="text-lg font-bold text-ink">Signs you may need professional help</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($signs as $sign)
                                <li class="flex gap-3 text-[0.9375rem] leading-snug"><x-glyph name="check-circle" class="mt-0.5 size-5 shrink-0 text-orange-ink" />{{ $sign }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    {{-- Service options --}}
    @if (! empty($service->options))
        <section class="section bg-mint" aria-labelledby="options-heading">
            <div class="container-site">
                <x-section-heading id="options-heading" eyebrow="Service options" title="What a provider" highlight="may offer"
                    intro="Options depend on your system, your home, and the provider you choose." class="reveal" />
                <div class="mt-10 grid gap-5 sm:grid-cols-2 {{ count($service->options) >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
                    @foreach ($service->options as $option)
                        <div class="reveal rounded-[var(--radius-card)] bg-white p-6 shadow-[var(--shadow-card)]">
                            <span class="display text-4xl text-orange">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-2 text-lg font-bold text-ink">{{ $option['title'] ?? '' }}</h3>
                            <p class="mt-2 text-[0.9375rem] leading-relaxed">{{ $option['text'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Details --}}
    @if (filled($service->details))
        <section class="section">
            <div class="container-site">
                <div class="prose-article mx-auto max-w-[760px]">{!! \App\Support\RichText::render($service->details) !!}</div>
            </div>
        </section>
    @endif

    {{-- FAQs --}}
    @if (count($service->faqList()))
        <section class="section bg-white" aria-labelledby="service-faq">
            <div class="container-site grid gap-10 lg:grid-cols-[0.9fr_1.4fr] lg:gap-16">
                <x-section-heading id="service-faq" eyebrow="FAQ" :title="$service->label()" highlight="questions" />
                <x-faq :items="$service->faqList()" id="service-faq-list" />
            </div>
        </section>
    @endif

    {{-- Related services --}}
    @if ($service->relatedServices->isNotEmpty())
        <section class="section" aria-labelledby="related-services">
            <div class="container-site">
                <x-section-heading id="related-services" eyebrow="Related" title="Related services" />
                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    @foreach ($service->relatedServices as $related)
                        <x-service-card :service="$related" variant="compact" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Locations --}}
    @if ($states->isNotEmpty() || $cities->isNotEmpty())
        <section class="section-tight bg-white" aria-labelledby="service-locations">
            <div class="container-site">
                <h2 id="service-locations" class="text-[1.5rem] font-bold text-ink">{{ $service->label() }} by location</h2>
                <ul class="mt-6 flex flex-wrap gap-2.5">
                    @foreach ($cities as $city)
                        <li><a href="{{ $city->publicUrl() }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-line bg-page px-4 text-[0.9375rem] font-medium text-ink hover:border-teal-900/30 hover:bg-white"><x-glyph name="map-pin" class="size-4 text-orange-ink" />{{ $city->label() }}</a></li>
                    @endforeach
                    @foreach ($states as $state)
                        <li><a href="{{ $state->publicUrl() }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-line bg-page px-4 text-[0.9375rem] font-medium text-ink hover:border-teal-900/30 hover:bg-white"><x-glyph name="map-pin" class="size-4 text-teal-700" />{{ $state->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Related articles --}}
    @if ($posts->isNotEmpty())
        <section class="section" aria-labelledby="service-articles">
            <div class="container-site">
                <div class="flex items-end justify-between gap-6">
                    <x-section-heading id="service-articles" eyebrow="Learn more" title="Helpful guides" />
                    <a href="{{ route('blog.index') }}" class="link-arrow hidden shrink-0 md:inline-flex">All articles <x-glyph name="arrow-right" /></a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)<x-blog-card :post="$post" />@endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band :availability="$mentionsAvailability" />
</x-layouts.app>
