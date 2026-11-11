<x-layouts.app :seo="$seo">
    @php
        $published = $states->where('is_published', true);
        $byLetter = $states->groupBy(fn ($s) => mb_substr($s->name, 0, 1));
        $query = request('q', '');
    @endphp
    <x-page-hero compact title="Service areas" eyebrow="Locations" :crumbs="$seo->breadcrumbs"
        intro="Find your state or city to learn about local heating and cooling needs. You can call or send a request from anywhere in the United States." />

    <div x-data="stateFilter" x-init="q = @js($query)">
        <section class="section-tight">
            <div class="container-site">
                <div class="grid gap-8 lg:grid-cols-[1fr_420px] lg:items-end">
                    <x-section-heading eyebrow="Browse by state" title="Choose your" highlight="state" />
                    <form action="{{ route('locations.index') }}" method="GET" role="search" @submit.prevent>
                        <label for="loc-search" class="field-label">Search states</label>
                        <div class="relative">
                            <x-glyph name="search" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted" />
                            <input id="loc-search" type="search" name="q" x-model="q" value="{{ $query }}" placeholder="e.g. Texas" class="input pl-12" autocomplete="off">
                        </div>
                    </form>
                </div>

                @if ($published->isNotEmpty())
                    <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($published as $state)
                            <li x-show="matches(@js($state->name))">
                                <a href="{{ $state->publicUrl() }}" class="card card-hover group flex items-center gap-4 p-5">
                                    <span class="display inline-flex size-14 shrink-0 items-center justify-center rounded-xl bg-teal-900 text-2xl text-white">{{ $state->code }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-lg font-bold text-ink">{{ $state->name }}</span>
                                        <span class="text-sm text-muted">{{ $state->cities_count }} {{ Str::plural('city page', $state->cities_count) }}</span>
                                    </span>
                                    <x-glyph name="arrow-right" class="size-5 text-orange-ink transition-transform group-hover:translate-x-1" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        @if ($featuredCities->isNotEmpty())
            <section class="section-tight bg-white" aria-labelledby="popular-cities">
                <div class="container-site">
                    <h2 id="popular-cities" class="text-[1.5rem] font-bold text-ink">Popular cities</h2>
                    <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($featuredCities as $city)
                            <li><a href="{{ $city->publicUrl() }}" class="flex min-h-14 items-center justify-between gap-3 rounded-xl border border-line bg-page px-4 font-semibold text-ink hover:border-teal-900/30 hover:bg-white">
                                <span>{{ $city->name }}, <span class="font-normal text-muted">{{ $city->state->code }}</span></span><x-glyph name="arrow-right" class="size-4 text-orange-ink" />
                            </a></li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        {{-- Full A–Z directory. States without a page yet are listed without a link (no empty pages are published). --}}
        <section class="section-tight" aria-labelledby="az-directory">
            <div class="container-site">
                <h2 id="az-directory" class="text-[1.5rem] font-bold text-ink">All states, A–Z</h2>
                <p class="mt-2 max-w-2xl text-[0.9375rem]">States without a dedicated page can still request help by phone or through the online form.</p>
                <div class="mt-8 grid gap-x-8 gap-y-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($byLetter as $letter => $group)
                        <div>
                            <p class="display text-3xl text-orange">{{ $letter }}</p>
                            <ul class="mt-2 space-y-1.5">
                                @foreach ($group as $state)
                                    <li x-show="matches(@js($state->name))">
                                        @if ($state->is_published)
                                            <a href="{{ $state->publicUrl() }}" class="text-link">{{ $state->name }}</a>
                                        @else
                                            <span class="text-body">{{ $state->name }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <x-cta-band availability />
</x-layouts.app>
