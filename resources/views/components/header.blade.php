@php
    $groupLabels = \App\Models\Service::GROUPS;
    $menuGroups = ['cooling', 'heating', 'air'];
    $active = fn (string $pattern) => request()->is($pattern) ? 'text-teal-900 after:scale-x-100' : 'text-[#374151] after:scale-x-0';
    $navLink = 'relative inline-flex h-11 items-center gap-1 rounded-md px-3 text-[0.9375rem] font-medium transition-colors hover:text-teal-900 after:absolute after:inset-x-3 after:bottom-1 after:h-0.5 after:origin-left after:rounded-full after:bg-orange after:transition-transform after:duration-300 hover:after:scale-x-100';
@endphp
<header x-data="siteHeader" class="sticky top-0 z-50 border-b bg-white/95 backdrop-blur-md transition-shadow duration-300 supports-[backdrop-filter]:bg-white/90"
        :class="scrolled ? 'border-line shadow-[var(--shadow-header)]' : 'border-transparent'">
    <div class="container-site relative flex h-[var(--header-h)] items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="shrink-0 rounded-md" aria-label="{{ $site->name() }} — home">
            <x-logo class="h-11 w-auto sm:h-12 md:h-[52px]" />
        </a>

        <nav aria-label="Main" class="hidden items-center lg:flex">
            <ul class="flex items-center gap-0.5">
                <li><a href="{{ route('home') }}" class="{{ $navLink }} {{ $active('/') }}" @if(request()->is('/')) aria-current="page" @endif>Home</a></li>
                <li><a href="{{ $nav['about'] }}" class="{{ $navLink }} {{ $active('about') }}" @if(request()->is('about')) aria-current="page" @endif>About</a></li>

                {{-- Services mega menu --}}
                <li x-data="dropdown" class=" static xl:relative" @mouseenter="show()" @mouseleave="hide()" @keydown.escape.window="open = false" @click.outside="open = false">
                    <div class="inline-flex items-center">
                        <a href="{{ route('services.index') }}" class="{{ $navLink }} pr-1 {{ $active('services*') }}" @if(request()->is('services')) aria-current="page" @endif>
                            Services
                        </a>
                        <button type="button"
                                class="inline-flex h-11 w-7 items-center justify-center rounded-md text-[#374151] transition-colors hover:text-teal-900"
                                @click.stop="toggle()"
                                :aria-expanded="open.toString()"
                                aria-controls="menu-services"
                                aria-haspopup="true"
                                aria-label="Toggle services menu">
                            <x-glyph name="chevron-down" class="size-4 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
                        </button>
                    </div>
                    <div id="menu-services" x-cloak x-show="open"
                         x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute top-full right-5 z-50 w-[min(900px,calc(100vw-40px))] pt-2 xl:right-auto xl:left-1/2 xl:-translate-x-1/2">
                        <div class="card grid grid-cols-[1fr_1fr_1fr_230px] gap-6 overflow-hidden p-6 shadow-[var(--shadow-lift)]">
                            @foreach ($menuGroups as $group)
                                @php($items = $nav['services'][$group] ?? [])
                                <div>
                                    <p class="mb-3 text-xs font-semibold tracking-[0.08em] text-muted uppercase">{{ $groupLabels[$group] ?? $group }}</p>
                                    <ul class="space-y-0.5">
                                        @foreach ($items as $item)
                                            <li>
                                                <a href="{{ $item['url'] }}" @click="open = false" class="group flex items-center gap-2.5 rounded-lg px-2 py-2 text-[0.9375rem] font-medium text-ink hover:bg-page">
                                                    <x-glyph :name="$item['icon']" class="size-[18px] shrink-0 text-teal-700 group-hover:text-orange-ink" />
                                                    <span>{{ $item['label'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                            <div class="surface-teal on-dark -my-6 -mr-6 flex flex-col justify-between p-6">
                                <div>
                                    <p class="display text-3xl text-white">Need help now?</p>
                                    <p class="mt-2 text-sm leading-relaxed">Call to get connected with an available local provider.</p>
                                    @foreach ($nav['services']['emergency'] ?? [] as $item)
                                        <a href="{{ $item['url'] }}" @click="open = false" class="link-arrow mt-3 text-sm">{{ $item['label'] }} <x-glyph name="arrow-right" /></a>
                                    @endforeach
                                </div>
                                <a href="{{ route('services.index') }}" @click="open = false" class="link-arrow mt-6 text-sm">All services <x-glyph name="arrow-right" /></a>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- Locations dropdown --}}
                <li x-data="dropdown" class="relative" @mouseenter="show()" @mouseleave="hide()" @keydown.escape.window="open = false" @click.outside="open = false">
                    <div class="inline-flex items-center">
                        <a href="{{ route('locations.index') }}" class="{{ $navLink }} pr-1 {{ $active('locations*') }}" @if(request()->is('locations')) aria-current="page" @endif>
                            Locations
                        </a>
                        <button type="button"
                                class="inline-flex h-11 w-7 items-center justify-center rounded-md text-[#374151] transition-colors hover:text-teal-900"
                                @click.stop="toggle()"
                                :aria-expanded="open.toString()"
                                aria-controls="menu-locations"
                                aria-haspopup="true"
                                aria-label="Toggle locations menu">
                            <x-glyph name="chevron-down" class="size-4 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
                        </button>
                    </div>
                    <div id="menu-locations" x-cloak x-show="open"
                         x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute top-full right-0 z-50 w-[min(560px,calc(100vw-40px))] pt-2 xl:right-auto xl:left-1/2 xl:-translate-x-1/2">
                        <div class="card grid grid-cols-2 gap-6 p-6 shadow-[var(--shadow-lift)]">
                            <div>
                                <p class="mb-3 text-xs font-semibold tracking-[0.08em] text-muted uppercase">States</p>
                                <ul class="space-y-0.5">
                                    @forelse (collect($nav['states'])->take(8) as $state)
                                        <li><a href="{{ $state['url'] }}" @click="open = false" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-[0.9375rem] font-medium text-ink hover:bg-page"><x-glyph name="map-pin" class="size-4 text-teal-700" />{{ $state['name'] }}</a></li>
                                    @empty
                                        <li class="px-2 text-sm text-muted">Service areas are being added.</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div>
                                <p class="mb-3 text-xs font-semibold tracking-[0.08em] text-muted uppercase">Popular cities</p>
                                <ul class="space-y-0.5">
                                    @foreach ($nav['cities'] as $city)
                                        <li><a href="{{ $city['url'] }}" @click="open = false" class="block rounded-lg px-2 py-1.5 text-[0.9375rem] font-medium text-ink hover:bg-page">{{ $city['name'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <a href="{{ route('locations.index') }}" @click="open = false" class="link-arrow col-span-2 border-t border-line pt-4 text-sm">Browse all locations <x-glyph name="arrow-right" /></a>
                        </div>
                    </div>
                </li>

                <li><a href="{{ route('blog.index') }}" class="{{ $navLink }} {{ $active('blog*') }}" @if(request()->is('blog')) aria-current="page" @endif>Blog</a></li>
                <li><a href="{{ route('contact') }}" class="{{ $navLink }} {{ $active('contact*') }}" @if(request()->is('contact')) aria-current="page" @endif>Contact</a></li>
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
