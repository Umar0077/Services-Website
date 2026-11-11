{{-- Paid-traffic call landing page (/call-hvac). Focused on one action: the call. Not indexed by default. --}}
@php
    $number = \App\Support\Phone::current();
    $formattedPhone = \App\Support\Phone::format($number);
    $telHref = \App\Support\Phone::tel($number);

    $rawHeading = trim((string) ($page->heading ?: ''));
    if ($rawHeading === '' || $rawHeading === 'Heating or cooling problem? Call to get connected.') {
        $rawHeading = 'Heating or Cooling Problems? Connect With Local HVAC Service Providers';
    }
    [$headlinePrimary, $headlineSecondary] = array_pad(
        preg_split('/(?<=[?.!])\s+/', $rawHeading, 2) ?: [$rawHeading, null],
        2,
        null
    );

    $heroIntro = trim((string) ($page->intro ?: ''));
    if ($heroIntro === '' || str_starts_with($heroIntro, 'Describe what is happening with your system')) {
        $heroIntro = "Need help with your home's heating or cooling system? Call to get connected with participating local HVAC service providers in your area.";
    }

    $defaultCards = [
        [
            'icon' => 'snowflake',
            'title' => 'Air Conditioning Repair',
            'text' => 'Common home AC problems, reduced cooling, unusual noises, and system malfunctions.',
        ],
        [
            'icon' => 'flame',
            'title' => 'Heating & Furnace Repair',
            'text' => 'Residential heating issues and furnace-related service needs.',
        ],
        [
            'icon' => 'refresh',
            'title' => 'HVAC Installation & Replacement',
            'text' => 'Homeowners exploring replacement or installation options.',
        ],
        [
            'icon' => 'thermometer',
            'title' => 'Heat Pumps & Mini-Splits',
            'text' => 'Residential heat pump and ductless cooling and heating solutions.',
        ],
        [
            'icon' => 'wrench',
            'title' => 'HVAC Maintenance',
            'text' => 'Routine maintenance and seasonal system servicing.',
        ],
        [
            'icon' => 'map-pin',
            'title' => 'Local Service Connections',
            'text' => 'Helping homeowners connect with participating service providers where available.',
        ],
    ];

    $featureSection = collect($page->sections ?? [])->firstWhere('type', 'features');
    $cmsItems = data_get($featureSection, 'data.items', []);
    $iconCycle = ['snowflake', 'flame', 'refresh', 'thermometer', 'wrench', 'map-pin'];

    $serviceCards = is_array($cmsItems) && count($cmsItems) >= 6
        ? collect($cmsItems)->values()->map(function (array $item, int $idx) use ($iconCycle) {
            $titleLower = strtolower((string) ($item['title'] ?? ''));
            $icon = match (true) {
                str_contains($titleLower, 'air conditioning') || str_contains($titleLower, 'ac ') => 'snowflake',
                str_contains($titleLower, 'heat') && str_contains($titleLower, 'pump') => 'thermometer',
                str_contains($titleLower, 'heating') || str_contains($titleLower, 'furnace') => 'flame',
                str_contains($titleLower, 'install') || str_contains($titleLower, 'replace') => 'refresh',
                str_contains($titleLower, 'maintenance') || str_contains($titleLower, 'tune') => 'wrench',
                str_contains($titleLower, 'local') || str_contains($titleLower, 'connect') => 'map-pin',
                default => $iconCycle[$idx % count($iconCycle)],
            };

            return [
                'icon' => $icon,
                'title' => $item['title'] ?? '',
                'text' => $item['text'] ?? '',
            ];
        })->all()
        : $defaultCards;

    $extraSections = collect($page->sections ?? [])->reject(fn ($s) => ($s['type'] ?? null) === 'features')->values()->all();

    $rawCtaHeading = trim((string) ($page->cta_heading ?: ''));
    $ctaHeading = ($rawCtaHeading === '' || $rawCtaHeading === 'Speak with someone now')
        ? "Need Help With Your Home's HVAC System?"
        : $rawCtaHeading;

    $rawCtaText = trim((string) ($page->cta_text ?: ''));
    $ctaText = ($rawCtaText === '' || str_starts_with($rawCtaText, 'Calls are answered by our connection service'))
        ? 'Call to explore available heating and cooling service options in your area.'
        : $rawCtaText;

    $heroAlt = $page->heroMedia?->alt ?: 'HVAC technician checking refrigerant gauges on a residential outdoor air conditioning unit';
@endphp

<x-layouts.app :seo="$seo" :minimal="true" body-class="flex min-h-dvh flex-col bg-page pb-[88px] md:pb-0">
    <x-slot:header>
        {{-- SECTION 1: TOP ANNOUNCEMENT BAR --}}
        <div class="border-b border-white/10 bg-teal-950 text-on-dark" role="region" aria-label="Service announcement">
            <div class="mx-auto flex max-w-[720px] items-center justify-center gap-2 px-4 py-2 text-center">
                <x-glyph name="home" class="size-3.5 shrink-0 text-orange" />
                <p class="text-[0.75rem] font-semibold tracking-[0.08em] text-white uppercase sm:text-[0.8125rem]">
                    Residential Heating &amp; Cooling Assistance
                </p>
            </div>
        </div>

        {{-- SECTION 2: MINIMAL LOGO HEADER --}}
        <header class="border-b border-white/10 bg-teal-900">
            <div class="mx-auto flex max-w-[720px] items-center justify-center px-4 py-3.5 sm:py-4">
                <a href="{{ route('home') }}" class="inline-flex items-center rounded-md focus-visible:outline-orange" aria-label="{{ $site->name() }}">
                    <x-logo variant="dark" class="h-10 w-auto max-w-[210px] sm:h-12 sm:max-w-[250px] md:h-[52px]" eager />
                </a>
            </div>
        </header>
    </x-slot:header>

    {{-- HERO CONVERSION SECTION (SECTIONS 3–7) --}}
    <section class=" on-dark relative isolate overflow-hidden border-b border-teal-800" style="background: linear-gradient(135deg, #0E2938 0%, #153445 100%);" aria-labelledby="hero-heading">
        <div class="mx-auto w-full max-w-[720px] px-4 pt-7 pb-10 sm:px-6 sm:pt-10 sm:pb-14 md:pt-12 md:pb-16">
            {{-- SECTION 3: SERVICE AVAILABILITY BADGES --}}
            <div class="flex flex-wrap items-center justify-center gap-2 text-center sm:gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/[0.06] px-3 py-1 text-[0.75rem] font-semibold tracking-[0.05em] text-white uppercase sm:px-3.5 sm:py-1.5 sm:text-[0.8125rem]">
                    <x-glyph name="wrench" class="size-3.5 text-orange" />
                    <span>Heating &amp; Cooling Assistance</span>
                </span>
                <a href="#landing-disclaimer" class="inline-flex items-center gap-1.5 rounded-full border border-orange/40 bg-orange/12 px-3 py-1 text-[0.75rem] font-semibold tracking-[0.04em] text-orange uppercase transition-colors hover:border-orange/70 hover:bg-orange/18 sm:px-3.5 sm:py-1.5 sm:text-[0.8125rem]">
                    <x-glyph name="clock" class="size-3.5 text-orange" />
                    <span>Same-Day Options Subject to Availability*</span>
                </a>
            </div>

            {{-- SECTION 4: MAIN HERO HEADLINE --}}
            <h1 id="hero-heading" class="mt-5 text-center font-display text-[clamp(2.25rem,1.5rem+3.8vw,4rem)] leading-[0.95] font-normal tracking-[0.015em] text-white uppercase sm:mt-6">
                <span class="block">{{ $headlinePrimary }}</span>
                @if ($headlineSecondary)
                    <span class="mt-1 block text-orange">{{ $headlineSecondary }}</span>
                @endif
            </h1>

            {{-- SECTION 5: HERO DESCRIPTION --}}
            <p class="mx-auto mt-4 max-w-[590px] text-center text-[0.975rem] leading-relaxed text-on-dark sm:mt-5 sm:text-[1.0625rem]">
                {{ $heroIntro }}
            </p>

            {{-- SECTION 6: PRIMARY CALL-TO-ACTION --}}
            <div class="mt-6 sm:mt-8">
                <a href="{{ $telHref }}"
                   class="phone-link group block w-full rounded-[16px] p-4 text-teal-950 transition-all duration-200 hover:-translate-y-0.5 hover:brightness-[1.03] active:translate-y-0 active:scale-[0.995] sm:p-5"
                   style="background: linear-gradient(135deg, #FF5722 0%, #E64A19 100%); box-shadow: 0 1px 0 rgba(255, 255, 255, 0.28) inset, 0 14px 32px -10px rgba(230, 74, 25, 0.65);"
                   data-track="call"
                   data-cta-location="hero"
                   aria-label="Call for HVAC assistance at {{ $formattedPhone }}">
                    <span class="flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-teal-900 text-white shadow-sm transition-transform duration-200 group-hover:scale-105 sm:size-14">
                            <x-glyph name="phone" class="size-6 text-orange sm:size-7" />
                        </span>
                        <span class="text-center sm:text-left">
                            <span class="block text-[0.75rem] font-extrabold tracking-[0.09em] text-teal-950 uppercase sm:text-[0.8125rem]">
                                Call for HVAC Assistance
                            </span>
                            <span class="phone-display mt-0.5 block text-[1.75rem] leading-none font-extrabold tracking-tight text-teal-950 sm:text-[2.25rem] md:text-[2.5rem]">
                                {{ $formattedPhone }}
                            </span>
                        </span>
                    </span>
                    <span class="mt-3 flex items-center justify-center gap-1.5 border-t border-teal-950/15 pt-2.5 text-center text-[0.8125rem] font-semibold text-teal-950/90 sm:text-sm">
                        <span>Tap to call &middot; Connect with available independent service providers</span>
                        <x-glyph name="arrow-right" class="size-4 shrink-0 transition-transform duration-200 group-hover:translate-x-1" />
                    </span>
                </a>

                <ul class="mt-5 grid grid-cols-1 gap-2.5 text-[0.875rem] font-medium text-white sm:grid-cols-3 sm:gap-3" aria-label="Referral service highlights">
                    <li class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-center">
                        <x-glyph name="check-circle" class="size-4 shrink-0 text-orange" />
                        <span>Free referral service</span>
                    </li>
                    <li class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-center">
                        <x-glyph name="check-circle" class="size-4 shrink-0 text-orange" />
                        <span>Independent local providers</span>
                    </li>
                    <li class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-center">
                        <x-glyph name="check-circle" class="size-4 shrink-0 text-orange" />
                        <span>You decide whether to hire</span>
                    </li>
                </ul>

                <x-availability-note dark class="mt-4 rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2.5 text-left" />
            </div>

            {{-- SECTION 7: HVAC HERO IMAGE --}}
            <figure class="mt-7 overflow-hidden rounded-[18px] border border-white/15 bg-teal-800 shadow-[var(--shadow-lift)] sm:mt-9">
                <div class="relative">
                    <x-picture
                        :media="$page->heroMedia"
                        :fallback="asset('images/hvac/hero-technician-condenser.jpg')"
                        sizes="(min-width: 768px) 720px, 100vw"
                        eager
                        class="aspect-[16/10] w-full object-cover object-center sm:aspect-[16/9]"
                        :alt="$heroAlt"
                    />
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-teal-950/60 via-transparent to-transparent" aria-hidden="true"></div>
                </div>
                <figcaption class="flex flex-wrap items-center justify-between gap-2 border-t border-white/10 bg-teal-950/90 px-4 py-2.5 text-xs text-on-dark sm:px-5 sm:text-[0.8125rem]">
                    <span class="flex items-center gap-2 font-medium text-white">
                        <x-glyph name="home" class="size-4 shrink-0 text-orange" />
                        <span>Residential heating &amp; cooling service connections</span>
                    </span>
                    <span class="text-on-dark-muted">For U.S. homeowners</span>
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- SECTION 8: SERVICE INFORMATION CARDS --}}
    <section class="bg-page py-12 sm:py-16" aria-labelledby="landing-services-heading">
        <div class="mx-auto w-full max-w-[720px] px-4 sm:px-6">
            <div class="text-center sm:text-left">
                <p class="eyebrow">Residential HVAC Assistance</p>
                <h2 id="landing-services-heading" class="mt-2 font-display text-[clamp(2rem,1.5rem+2vw,3rem)] leading-[0.96] font-normal tracking-[0.01em] text-ink uppercase">
                    {{ data_get($featureSection, 'data.heading') ?: 'Services & Home Comfort Needs' }}
                </h2>
                <p class="mt-2.5 text-[0.975rem] leading-relaxed text-body sm:text-[1.0625rem]">
                    {{ data_get($featureSection, 'data.intro') ?: 'Call to get connected with participating independent service providers for common residential heating and cooling requests.' }}
                </p>
            </div>

            <div class="mt-7 grid grid-cols-1 gap-4 sm:mt-8 sm:grid-cols-2">
                @foreach ($serviceCards as $card)
                    <article class="card flex items-start gap-3.5 p-5 transition-colors hover:border-teal-900/20">
                        <span class="icon-chip size-11 shrink-0 rounded-xl bg-orange-50 text-orange-ink">
                            <x-glyph :name="$card['icon']" class="size-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-[1.0625rem] leading-snug font-bold text-ink">{{ $card['title'] }}</h3>
                            <p class="mt-1.5 text-[0.9375rem] leading-relaxed text-body">{{ $card['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @if (count($extraSections))
        <x-sections :sections="$extraSections" />
    @endif

    {{-- SECTION 9: SECONDARY CALL-TO-ACTION --}}
    <section class="bg-page pb-12 sm:pb-16" aria-labelledby="secondary-cta-heading">
        <div class="mx-auto w-full max-w-[720px] px-4 sm:px-6">
            <div class="on-dark relative isolate overflow-hidden rounded-[20px] border border-teal-700/50 px-5 py-8 text-center shadow-[var(--shadow-lift)] sm:px-10 sm:py-11"
                 style="background: linear-gradient(135deg, #0E2938 0%, #153445 100%);">
                <div aria-hidden="true" class="mx-auto mb-4 h-1 w-14 rounded-full" style="background: linear-gradient(135deg, #FF5722 0%, #E64A19 100%);"></div>

                <h2 id="secondary-cta-heading" class="font-display text-[clamp(2rem,1.5rem+2.2vw,3.125rem)] leading-[0.96] font-normal tracking-[0.015em] text-white uppercase">
                    {{ $ctaHeading }}
                </h2>
                <p class="mx-auto mt-3 max-w-[520px] text-[0.975rem] leading-relaxed text-on-dark sm:text-[1.0625rem]">
                    {{ $ctaText }}
                </p>

                <div class="mt-6 sm:mt-7">
                    <a href="{{ $telHref }}"
                       class="phone-link group inline-flex w-full max-w-[440px] items-center justify-center gap-3.5 rounded-[14px] px-5 py-3.5 text-teal-950 transition-all duration-200 hover:-translate-y-0.5 hover:brightness-[1.03] active:translate-y-0 active:scale-[0.995] sm:w-auto sm:min-w-[340px] sm:px-7 sm:py-4"
                       style="background: linear-gradient(135deg, #FF5722 0%, #E64A19 100%); box-shadow: 0 1px 0 rgba(255, 255, 255, 0.28) inset, 0 12px 26px -10px rgba(230, 74, 25, 0.65);"
                       data-track="call"
                       data-cta-location="secondary"
                       aria-label="Call for HVAC assistance at {{ $formattedPhone }}">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-teal-900 text-white transition-transform duration-200 group-hover:scale-105">
                            <x-glyph name="phone" class="size-5 text-orange" />
                        </span>
                        <span class="text-left leading-tight">
                            <span class="block text-[0.75rem] font-extrabold tracking-[0.08em] text-teal-950 uppercase">
                                Call for HVAC Assistance
                            </span>
                            <span class="phone-display mt-0.5 block text-[1.45rem] font-extrabold tracking-tight text-teal-950 sm:text-[1.75rem]">
                                {{ $formattedPhone }}
                            </span>
                        </span>
                    </a>
                </div>

                <p class="mt-4 text-xs font-medium text-on-dark-muted sm:text-[0.8125rem]">
                    Independent referral service for U.S. homeowners &middot; No obligation to hire
                </p>
            </div>
        </div>
    </section>

    <x-slot:footer>
        {{-- SECTION 10: LEGAL DISCLAIMER & MINIMAL FOOTER --}}
        <footer id="landing-disclaimer" class="border-t border-white/10 bg-teal-950 text-on-dark" aria-labelledby="footer-disclaimer">
            <div class="mx-auto w-full max-w-[720px] px-4 py-10 sm:px-6 sm:py-12">
                <x-disclaimer variant="footer" />

                <div class="mt-6 flex flex-col gap-4 border-t border-white/10 pt-6 text-xs text-on-dark-muted sm:flex-row sm:items-center sm:justify-between sm:text-[0.8125rem]">
                    <nav aria-label="Legal and policy links" class="flex flex-wrap items-center gap-x-5 gap-y-2">
                        <a href="{{ route('disclaimer') }}" class="font-medium text-white underline decoration-orange/60 underline-offset-4 hover:decoration-orange">
                            Full Disclaimer
                        </a>
                        <a href="{{ \App\Models\Page::urlFor('privacy', '/privacy-policy') }}" class="hover:text-white">
                            Privacy Policy
                        </a>
                        <a href="{{ \App\Models\Page::urlFor('terms', '/terms') }}" class="hover:text-white">
                            Terms &amp; Conditions
                        </a>
                        <a href="{{ \App\Models\Page::urlFor('disclosures', '/service-disclosures') }}" class="hover:text-white">
                            Service Disclosures
                        </a>
                    </nav>
                    <p>&copy; {{ now()->year }} {{ $site->name() }}. All rights reserved.</p>
                </div>
            </div>
        </footer>

        {{-- SECTION 7 (PART 7): MOBILE STICKY CALL BUTTON --}}
        <div class="fixed inset-x-0 bottom-0 z-50 border-t border-white/15 bg-teal-950/95 px-3.5 pt-2.5 pb-[max(10px,env(safe-area-inset-bottom))] backdrop-blur-md md:hidden"
             role="region"
             aria-label="Quick call bar">
            <a href="{{ $telHref }}"
               class="phone-link flex min-h-[54px] w-full items-center justify-center gap-3 rounded-[12px] px-4 py-2.5 text-teal-950 active:scale-[0.99]"
               style="background: linear-gradient(135deg, #FF5722 0%, #E64A19 100%); box-shadow: 0 1px 0 rgba(255, 255, 255, 0.25) inset, 0 8px 20px -6px rgba(230, 74, 25, 0.65);"
               data-track="call"
               data-cta-location="sticky"
               aria-label="Call for HVAC help at {{ $formattedPhone }}">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-teal-900 text-white">
                    <x-glyph name="phone" class="size-5 text-orange" />
                </span>
                <span class="flex flex-col text-left leading-tight">
                    <span class="text-[0.7rem] font-extrabold tracking-[0.08em] text-teal-950 uppercase">
                        Call for HVAC Help
                    </span>
                    <span class="phone-display text-[1.125rem] font-extrabold tracking-tight text-teal-950">
                        {{ $formattedPhone }}
                    </span>
                </span>
            </a>
        </div>
    </x-slot:footer>
</x-layouts.app>
