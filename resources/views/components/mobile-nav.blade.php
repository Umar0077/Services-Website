{{-- Mobile drawer. Teleported to <body> so header backdrop-filter never clips fixed positioning. Focus is trapped while open. --}}
@php($groupLabels = \App\Models\Service::GROUPS)
<template x-teleport="body">
<div x-cloak x-show="mobileOpen" class="fixed inset-0 z-[60] lg:hidden" role="dialog" aria-modal="true" aria-label="Menu" id="mobile-nav"
     @keydown.escape.window="mobileOpen = false">
    <div x-show="mobileOpen" x-transition.opacity.duration.250ms class="absolute inset-0 bg-teal-950/60" @click="mobileOpen = false" aria-hidden="true"></div>

    <div x-show="mobileOpen" x-trap.inert.noscroll="mobileOpen"
         x-transition:enter="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="absolute inset-y-0 right-0 flex w-full max-w-[400px] flex-col bg-white shadow-2xl">
        <div class="flex h-[var(--header-h)] shrink-0 items-center justify-between border-b border-line px-5">
            <a href="{{ route('home') }}" @click="mobileOpen = false" aria-label="{{ $site->name() }} — home">
                <x-logo class="h-10 w-auto" />
            </a>
            <button type="button" class="inline-flex size-11 items-center justify-center rounded-lg border border-line" @click="mobileOpen = false">
                <x-glyph name="x" class="size-6" /><span class="sr-only">Close menu</span>
            </button>
        </div>

        <nav aria-label="Mobile" class="flex-1 overflow-y-auto overscroll-contain px-3 py-4">
            <ul class="space-y-1 text-[1.0625rem] font-medium text-ink">
                <li><a href="{{ route('home') }}" @click="mobileOpen = false" class="flex min-h-12 items-center rounded-lg px-3 hover:bg-page">Home</a></li>
                <li><a href="{{ $nav['about'] }}" @click="mobileOpen = false" class="flex min-h-12 items-center rounded-lg px-3 hover:bg-page">About</a></li>
                <li x-data="{ open: {{ request()->is('services*') ? 'true' : 'false' }} }">
                    <button type="button" class="flex min-h-12 w-full items-center justify-between rounded-lg px-3 hover:bg-page" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-menu-services">
                        <span>Services</span>
                        <x-glyph name="chevron-down" class="size-5 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div id="mobile-menu-services" x-show="open" x-collapse x-cloak>
                        @foreach ($nav['services'] as $group => $items)
                            <p class="mt-3 mb-1 px-4 text-xs font-semibold tracking-[0.08em] text-muted uppercase">{{ $groupLabels[$group] ?? $group }}</p>
                            <ul>
                                @foreach ($items as $item)
                                    <li>
                                        <a href="{{ $item['url'] }}" @click="mobileOpen = false" class="flex min-h-11 items-center gap-3 rounded-lg px-4 text-base text-[#374151] hover:bg-page">
                                            <x-glyph :name="$item['icon']" class="size-[18px] shrink-0 text-teal-700" />
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                        <a href="{{ route('services.index') }}" @click="mobileOpen = false" class="mt-2 flex min-h-11 items-center px-4 text-base font-semibold text-orange-ink">All services</a>
                    </div>
                </li>
                <li x-data="{ open: {{ request()->is('locations*') ? 'true' : 'false' }} }">
                    <button type="button" class="flex min-h-12 w-full items-center justify-between rounded-lg px-3 hover:bg-page" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-menu-locations">
                        <span>Locations</span>
                        <x-glyph name="chevron-down" class="size-5 transition-transform duration-200" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div id="mobile-menu-locations" x-show="open" x-collapse x-cloak>
                        <ul>
                            @foreach (collect($nav['states'])->take(8) as $state)
                                <li>
                                    <a href="{{ $state['url'] }}" @click="mobileOpen = false" class="flex min-h-11 items-center gap-3 rounded-lg px-4 text-base text-[#374151] hover:bg-page">
                                        <x-glyph name="map-pin" class="size-[18px] shrink-0 text-teal-700" />
                                        <span>{{ $state['name'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('locations.index') }}" @click="mobileOpen = false" class="mt-1 flex min-h-11 items-center px-4 text-base font-semibold text-orange-ink">All locations</a>
                    </div>
                </li>
                <li><a href="{{ route('blog.index') }}" @click="mobileOpen = false" class="flex min-h-12 items-center rounded-lg px-3 hover:bg-page">Blog</a></li>
                <li><a href="{{ route('contact') }}" @click="mobileOpen = false" class="flex min-h-12 items-center rounded-lg px-3 hover:bg-page">Contact</a></li>
            </ul>
        </nav>

        <div class="shrink-0 border-t border-line p-5">
            <x-phone class="btn btn-primary btn-lg w-full" label="Call" />
            <a href="{{ route('contact') }}" @click="mobileOpen = false" class="btn btn-outline mt-3 w-full">Send a request online</a>
        </div>
    </div>
</div>
</template>
