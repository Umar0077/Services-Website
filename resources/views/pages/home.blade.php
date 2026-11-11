<x-layouts.app :seo="$seo">
    @php
        $bySlug = $services->keyBy('slug');
        $featured = $services->where('is_featured', true)->take(6);
        $others = $services->whereNotIn('id', $featured->pluck('id'));
        $quick = collect([
            ['slug' => 'ac-repair', 'label' => 'AC not cooling', 'icon' => 'snowflake'],
            ['slug' => 'heating-repair', 'label' => 'No heat', 'icon' => 'flame'],
            ['slug' => 'ac-replacement', 'label' => 'Replace a system', 'icon' => 'refresh'],
            ['slug' => 'hvac-maintenance', 'label' => 'Seasonal tune-up', 'icon' => 'wrench'],
        ])->filter(fn ($q) => $bySlug->has($q['slug']));
        $whyIcons = ['compass', 'home', 'tag', 'sliders'];
        [$heroLine1, $heroLine2] = array_pad(preg_split('/(?<=\.)\s+/', trim((string) $site->get('hero_heading')), 2), 2, null);
    @endphp

    {{-- HERO --}}
    <section class="surface-teal on-dark relative isolate overflow-hidden">
        <div class="absolute inset-0 -z-20">
            <x-picture :media="$heroMedia" sizes="100vw" eager class="size-full object-cover object-[70%_center]" alt="" />
        </div>
        <div class="absolute inset-0 -z-10" style="background: var(--gradient-hero)" aria-hidden="true"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-teal-900/80 via-transparent to-transparent md:hidden" aria-hidden="true"></div>

        <div class="container-site flex min-h-[600px] flex-col justify-center pt-16 pb-32 md:min-h-[680px] md:pt-20 md:pb-40">
            <div class="max-w-[680px]">
                <p class="eyebrow mb-6">Residential heating &amp; cooling · Nationwide</p>
                <h1 class="h-hero text-white">
                    {{ $heroLine1 }}
                    @if ($heroLine2)<span class="block text-orange">{{ $heroLine2 }}</span>@endif
                </h1>
                <p class="lead mt-6 max-w-[580px] text-on-dark">{{ $site->get('hero_text') }}</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <x-phone class="btn btn-primary btn-lg" label="Call" />
                    <a href="{{ route('services.index') }}" class="btn btn-ghost-light btn-lg">Explore services <x-glyph name="arrow-right" /></a>
                </div>
                <ul class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-[0.9375rem] font-medium text-white">
                    <li class="flex items-center gap-2"><x-glyph name="check-circle" class="size-5 text-orange" />Free to use</li>
                    <li class="flex items-center gap-2"><x-glyph name="check-circle" class="size-5 text-orange" />Independent local providers</li>
                    <li class="flex items-center gap-2"><x-glyph name="check-circle" class="size-5 text-orange" />Repairs, installs &amp; tune-ups</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- QUICK START (overlaps the hero) --}}
    @if ($quick->isNotEmpty())
        <section aria-labelledby="quick-start" class="relative z-10 -mt-20 md:-mt-24">
            <div class="container-site">
                <div class="card grid gap-6 p-5 shadow-[var(--shadow-lift)] md:p-7 lg:grid-cols-[220px_1fr] lg:items-center lg:gap-8">
                    <div>
                        <h2 id="quick-start" class="display text-[2rem] text-ink">What do you need?</h2>
                        <p class="mt-1 text-[0.9375rem]">Start with the problem you have.</p>
                    </div>
                    <ul class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        @foreach ($quick as $q)
                            <li>
                                <a href="{{ $bySlug[$q['slug']]->publicUrl() }}" class="group flex h-full min-h-[72px] flex-col items-start gap-2.5 rounded-xl sm:flex-row sm:items-center sm:gap-3 border border-line bg-page px-4 py-3 font-semibold text-ink transition-all duration-200 hover:-translate-y-0.5 hover:border-orange/50 hover:bg-white hover:shadow-[var(--shadow-card)]">
                                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-orange-ink shadow-sm group-hover:bg-orange group-hover:text-teal-900"><x-glyph :name="$q['icon']" /></span>
                                    <span class="text-[0.9375rem] leading-snug">{{ $q['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    {{-- SERVICES OVERVIEW --}}
    <section class="section" aria-labelledby="services-heading">
        <div class="container-site">
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <x-section-heading id="services-heading" eyebrow="Our services" title="Heating & cooling help" highlight="for your home"
                    intro="From a sudden breakdown to a planned upgrade, explore the residential HVAC services you can get connected with." class="reveal" />
                <a href="{{ route('services.index') }}" class="link-arrow shrink-0">View all services <x-glyph name="arrow-right" /></a>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $service)
                    <div class="reveal"><x-service-card :service="$service" /></div>
                @endforeach
            </div>

            @if ($others->isNotEmpty())
                <div class="mt-10 flex flex-col gap-4 rounded-[var(--radius-card)] border border-dashed border-[#D1D5DB] p-5 md:flex-row md:items-center md:p-6">
                    <p class="shrink-0 font-semibold text-ink">More home services:</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($others as $service)
                            <li><a href="{{ $service->publicUrl() }}" class="inline-flex min-h-10 items-center gap-2 rounded-full border border-line bg-white px-4 text-sm font-medium text-ink transition-colors hover:border-orange/60 hover:text-teal-900"><x-glyph :name="$service->icon" class="size-4 text-teal-700" />{{ $service->label() }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- WHY CHOOSE US --}}
    <section class="section bg-mint" aria-labelledby="why-heading">
        <div class="container-site grid items-center gap-12 lg:grid-cols-[1fr_1.1fr] lg:gap-20">
            <div class="reveal relative">
                <x-picture :media="$whyMedia" sizes="(min-width: 1024px) 560px, 100vw" class="aspect-[4/3.4] w-full rounded-[20px] object-cover shadow-[var(--shadow-lift)]" />
                <div class="absolute -bottom-6 left-4 right-4 rounded-2xl bg-white p-5 shadow-[var(--shadow-lift)] sm:left-auto sm:-right-4 sm:max-w-[300px] md:-right-8">
                    <p class="display text-[1.75rem] text-ink">You decide.</p>
                    <p class="mt-1 text-sm leading-relaxed">Providers assess your system and explain the options. Whether to hire them is always your call.</p>
                </div>
            </div>
            <div class="pt-6 lg:pt-0">
                <x-section-heading id="why-heading" eyebrow="Why homeowners use us" title="A simpler way to" highlight="find HVAC help"
                    intro="HVAC Services is an independent referral service. We focus on one job: making it easier to reach an available local provider." class="reveal" />
                <ul class="mt-10 grid gap-x-8 gap-y-8 sm:grid-cols-2">
                    @foreach ($site->get('why_items', []) as $i => $item)
                        <li class="reveal flex gap-4">
                            <span class="icon-chip bg-white"><x-glyph :name="$whyIcons[$i % 4]" /></span>
                            <div>
                                <h3 class="text-[1.0625rem] font-bold text-ink">{{ $item['title'] }}</h3>
                                <p class="mt-1.5 text-[0.9375rem] leading-relaxed">{{ $item['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ \App\Models\Page::urlFor('about', '/about') }}" class="link-arrow mt-10">How our referral service works <x-glyph name="arrow-right" /></a>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="section surface-teal on-dark relative isolate overflow-hidden" aria-labelledby="how-heading">
        <div aria-hidden="true" class="absolute top-1/2 -left-40 -z-10 size-[520px] -translate-y-1/2 rounded-full bg-orange/[0.08] blur-3xl"></div>
        <div class="container-site">
            <x-section-heading id="how-heading" eyebrow="How it works" title="Three steps to" highlight="get connected" align="center" class="reveal [&_.h-section]:text-white [&_.lead]:text-on-dark" />
            <div class="relative mt-14">
            <div aria-hidden="true" class="absolute top-[68px] right-[16%] left-[16%] hidden h-px bg-gradient-to-r from-transparent via-white/25 to-transparent md:block"></div>
            <ol class="relative grid gap-6 md:grid-cols-3 md:gap-8">
                @foreach ($site->get('how_steps', []) as $step)
                    <li class="reveal relative rounded-[var(--radius-card)] border border-white/10 bg-white/[0.04] p-7 text-center">
                        <span class="relative mx-auto flex size-20 items-center justify-center rounded-full border border-orange/40 bg-teal-900">
                            <span class="display text-[2.75rem] leading-none text-orange">{{ $loop->iteration }}</span>
                        </span>
                        <h3 class="mt-6 text-xl font-bold text-white">{{ $step['title'] }}</h3>
                        <p class="mx-auto mt-2.5 max-w-[300px] text-[0.9375rem] leading-relaxed">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
            </div>
            <div class="mt-12 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <x-phone class="btn btn-primary btn-lg" label="Call" />
                <a href="{{ route('contact') }}" class="btn btn-ghost-light btn-lg">Request help online</a>
            </div>
        </div>
    </section>

    {{-- FEATURED: PLANNING AN UPGRADE --}}
    @php($upgrade = collect(['heat-pump-services', 'mini-split-systems'])->map(fn ($s) => $bySlug[$s] ?? null)->filter())
    @if ($upgrade->isNotEmpty())
        <section class="section" aria-labelledby="upgrade-heading">
            <div class="container-site">
                <x-section-heading id="upgrade-heading" eyebrow="Planning an upgrade?" title="Efficient comfort," highlight="room by room"
                    intro="Heat pumps and ductless systems have changed what is possible in American homes. Learn how they work before you talk to a provider." class="reveal" />
                <div class="mt-12 grid gap-6 lg:grid-cols-2">
                    @foreach ($upgrade as $service)
                        <article class="group reveal relative isolate flex min-h-[420px] flex-col justify-end overflow-hidden rounded-[20px] p-7 md:min-h-[460px] md:p-9">
                            <x-picture :media="$service->heroMedia" sizes="(min-width: 1024px) 640px, 100vw" class="absolute inset-0 -z-20 size-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" />
                            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-teal-900 via-teal-900/70 to-teal-900/0" aria-hidden="true"></div>
                            <span class="icon-chip mb-5 bg-white/12 text-orange backdrop-blur-sm"><x-glyph :name="$service->icon" /></span>
                            <h3 class="h-card text-white">{{ $service->title }}</h3>
                            <p class="mt-3 max-w-[460px] text-on-dark">{{ $service->short_description }}</p>
                            <a href="{{ $service->publicUrl() }}" class="link-arrow mt-6 text-white after:absolute after:inset-0">Explore {{ strtolower($service->label()) }} <x-glyph name="arrow-right" class="text-orange" /></a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- SERVICE AREAS --}}
    <section class="section bg-white" aria-labelledby="areas-heading">
        <div class="container-site grid gap-12 lg:grid-cols-[1.2fr_1fr] lg:gap-16">
            <div x-data="stateFilter">
                <x-section-heading id="areas-heading" eyebrow="Service areas" title="Find help" highlight="near you"
                    intro="Browse by state to learn about local heating and cooling needs, then call or send a request." class="reveal" />
                @if ($states->isNotEmpty())
                    <form action="{{ route('locations.index') }}" method="GET" class="mt-8 max-w-md" role="search" @submit="if (q.trim() === '') $event.preventDefault()">
                        <label for="state-search" class="sr-only">Search states</label>
                        <div class="relative">
                            <x-glyph name="search" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted" />
                            <input id="state-search" name="q" type="search" x-model="q" placeholder="Search for your state" class="input pl-12" autocomplete="off">
                        </div>
                    </form>
                    <ul class="mt-6 flex flex-wrap gap-2.5">
                        @foreach ($states as $state)
                            <li x-show="matches(@js($state->name))">
                                <a href="{{ $state->publicUrl() }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-line bg-page px-4 text-[0.9375rem] font-medium text-ink transition-colors hover:border-teal-900/30 hover:bg-white">
                                    <x-glyph name="map-pin" class="size-4 text-orange-ink" />{{ $state->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-8">Location pages are being added. You can still call or send a request from anywhere in the United States.</p>
                @endif
                <a href="{{ route('locations.index') }}" class="link-arrow mt-8">See all locations <x-glyph name="arrow-right" /></a>
            </div>

            @if ($featuredCities->isNotEmpty())
                <div class="reveal rounded-[20px] bg-page p-6 md:p-8">
                    <h3 class="text-sm font-semibold tracking-[0.08em] text-muted uppercase">Popular cities</h3>
                    <ul class="mt-4 divide-y divide-line">
                        @foreach ($featuredCities as $city)
                            <li>
                                <a href="{{ $city->publicUrl() }}" class="group flex min-h-14 items-center justify-between gap-4 py-2 font-semibold text-ink">
                                    <span>{{ $city->name }}<span class="ml-2 font-normal text-muted">{{ $city->state->name }}</span></span>
                                    <x-glyph name="arrow-right" class="size-4 text-orange-ink transition-transform group-hover:translate-x-1" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- RESOURCES --}}
    @if ($posts->isNotEmpty())
        <section class="section" aria-labelledby="blog-heading">
            <div class="container-site">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <x-section-heading id="blog-heading" eyebrow="HVAC resource center" title="Guides for" highlight="homeowners"
                        intro="Practical advice to help you understand your system and prepare for a conversation with a provider." class="reveal" />
                    <a href="{{ route('blog.index') }}" class="link-arrow shrink-0">All articles <x-glyph name="arrow-right" /></a>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <div class="reveal {{ $loop->last ? 'sm:max-lg:hidden' : '' }}"><x-blog-card :post="$post" /></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if (count($faqs))
        <section class="section bg-white" aria-labelledby="faq-heading">
            <div class="container-site grid gap-10 lg:grid-cols-[0.9fr_1.4fr] lg:gap-16">
                <div class="lg:sticky lg:top-[calc(var(--header-h)+32px)] lg:self-start">
                    <x-section-heading id="faq-heading" eyebrow="Questions" title="Frequently asked" highlight="questions" class="reveal" />
                    <p class="mt-5 max-w-sm">Still unsure what you need? Call and describe what is happening with your system.</p>
                    <x-phone class="btn btn-dark mt-6" label="Call" />
                </div>
                <x-faq :items="$faqs" id="home-faq" class="reveal" />
            </div>
        </section>
    @endif

    <x-cta-band availability />
</x-layouts.app>
