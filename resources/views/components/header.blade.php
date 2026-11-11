@php
    $groupLabels = \App\Models\Service::GROUPS;
    $active = fn (string $pattern) => request()->is($pattern) ? 'text-teal-900 after:scale-x-100' : 'text-[#374151] after:scale-x-0';
    $navLink = 'relative inline-flex h-11 items-center gap-1 rounded-md px-3 text-[0.9375rem] font-medium transition-colors hover:text-teal-900 after:absolute after:inset-x-3 after:bottom-1 after:h-0.5 after:origin-left after:rounded-full after:bg-orange after:transition-transform after:duration-300 hover:after:scale-x-100';
@endphp
<header x-data="siteHeader" class="sticky top-0 z-50 border-b bg-white/95 backdrop-blur-md transition-shadow duration-300 supports-[backdrop-filter]:bg-white/90"
        :class="scrolled ? 'border-line shadow-[var(--shadow-header)]' : 'border-transparent'">
    <div class="container-site flex h-[var(--header-h)] items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="shrink-0 rounded-md" aria-label="{{ $site->name() }} — home">
            <x-logo class="h-11 w-auto sm:h-12 md:h-[52px]" />
        </a>

        <nav aria-label="Main" class="hidden items-center lg:flex">
            <ul class="flex items-center gap-0.5">
                <li><a href="{{ route('home') }}" class="{{ $navLink }} {{ $active('/') }}" @if(request()->is('/')) aria-current="page" @endif>Home</a></li>
                <li><a href="{{ $nav['about'] }}" class="{{ $navLink }} {{ $active('about') }}">About</a></li>

                {{-- Services mega menu --}}
                <li x-data="dropdown" class="relative" @mouseenter="show()" @mouseleave="hide()" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" class="{{ $navLink }} {{ $active('services*') }}" @click="toggle()" :aria-expanded="open.toString()" aria-controls="menu-services" aria-haspopup="true">
                        Services <x-glyph name="chevron-down" class="size-4 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div id="menu-services" x-cloak x-show="open"
                         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute top-full left-1/2 w-[min(860px,calc(100vw-48px))] -translate-x-1/2 pt-3">
                        <div class="card grid grid-cols-[1fr_1fr_1fr_240px] gap-6 overflow-hidden p-6 shadow-[var(--shadow-lift)]">
                            @foreach ($nav['services'] as $group => $items)
                                @continue($group === 'emergency')
                                <div>
                                    <p class="mb-3 text-xs font-semibold tracking-[0.08em] text-muted uppercase">{{ $groupLabels[$group] ?? $group }}</p>
                                    <ul class="space-y-0.5">
                                        @foreach ($items as $item)
                                            <li><a href="{{ $item['url'] }}" class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-[0.9375rem] font-medium text-ink hover:bg-page">
                                                <x-glyph :name="$item['icon']" class="size-[18px] text-teal-700 group-hover:text-orange-ink" />{{ $item['label'] }}
                                            </a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                            <div class="surface-teal on-dark -my-6 -mr-6 flex flex-col justify-between p-6">
                                <div>
                                    <p class="display text-3xl text-white">Need help now?</p>
                                    <p class="mt-2 text-sm leading-relaxed">Call to get connected with an available local provider.</p>
                                    @foreach ($nav['services']['emergency'] ?? [] as $item)
                                        <a href="{{ $item['url'] }}" class="link-arrow mt-3 text-sm">{{ $item['label'] }} <x-glyph name="arrow-right" /></a>
                                    @endforeach
                                </div>
                                <a href="{{ route('services.index') }}" class="link-arrow mt-6 text-sm">All services <x-glyph name="arrow-right" /></a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- Locations dropdown --}}
                <li x-data="dropdown" class="relative" @mouseenter="show()" @mouseleave="hide()" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" class="{{ $navLink }} {{ $active('locations*') }}" @click="toggle()" :aria-expanded="open.toString()" aria-controls="menu-locations" aria-haspopup="true">
                        Locations <x-glyph name="chevron-down" class="size-4 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div id="menu-locations" x-cloak x-show="open"
                         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute top-full left-1/2 w-[560px] -translate-x-1/2 pt-3">
                        <div class="card grid grid-cols-2 gap-6 p-6 shadow-[var(--shadow-lift)]">
                            <div>
                                <p class="mb-3 text-xs font-semibold tracking-[0.08em] text-muted uppercase">States</p>
                                <ul class="space-y-0.5">
                                    @forelse (collect($nav['states'])->take(8) as $state)
                                        <li><a href="{{ $state['url'] }}" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-[0.9375rem] font-medium text-ink hover:bg-page"><x-glyph name="map-pin" class="size-4 text-teal-700" />{{ $state['name'] }}</a></li>
                                    @empty
                                        <li class="px-2 text-sm text-muted">Service areas are being added.</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div>
                                <p class="mb-3 text-xs font-semibold tracking-[0.08em] text-muted uppercase">Popular cities</p>
                                <ul class="space-y-0.5">
                                    @foreach ($nav['cities'] as $city)
                                        <li><a href="{{ $city['url'] }}" class="block rounded-lg px-2 py-1.5 text-[0.9375rem] font-medium text-ink hover:bg-page">{{ $city['name'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <a href="{{ route('locations.index') }}" class="link-arrow col-span-2 border-t border-line pt-4 text-sm">Browse all locations <x-glyph name="arrow-right" /></a>
                        </div>
                    </div>
                </li>

                <li><a href="{{ route('blog.index') }}" class="{{ $navLink }} {{ $active('blog*') }}">Blog</a></li>
                <li><a href="{{ route('contact') }}" class="{{ $navLink }} {{ $active('contact*') }}">Contact</a></li>
            </ul>
        </nav>

        <div class="flex items-center gap-2">
            <x-phone class="btn btn-primary btn-sm hidden sm:inline-flex md:min-h-12 md:px-5" :label="$site->get('header_cta_label')" />
            <button type="button" x-ref="menuButton" class="inline-flex size-11 items-center justify-center rounded-lg border border-line text-ink lg:hidden" @click="mobileOpen = true" aria-controls="mobile-nav" :aria-expanded="mobileOpen.toString()">
                <x-glyph name="menu" class="size-6" /><span class="sr-only">Open menu</span>
            </button>
        </div>
    </div>

    <x-mobile-nav />
</header>
