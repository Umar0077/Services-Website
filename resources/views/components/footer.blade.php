@php($allServices = collect($nav['services'])->flatten(1))
<footer class="surface-teal on-dark relative overflow-hidden" aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">Site information</h2>
    <div aria-hidden="true" class="pointer-events-none absolute -top-40 -right-40 size-[520px] rounded-full bg-orange/[0.07] blur-3xl"></div>

    <div class="container-site relative pt-16 pb-10 md:pt-20">
        <div class="grid gap-12 lg:grid-cols-[1.25fr_2fr] lg:gap-16">
            <div>
                <a href="{{ route('home') }}" class="inline-block rounded-md" aria-label="{{ $site->name() }} — home">
                    <x-logo variant="dark" :eager="false" class="h-14 w-auto" />
                </a>
                <p class="mt-5 max-w-sm text-[0.9375rem] leading-relaxed">{{ $site->get('footer_about') }}</p>
                <div class="mt-6 space-y-3">
                    <x-phone class="inline-flex items-center gap-2.5 text-lg font-semibold text-white hover:text-orange" />
                    @if ($site->get('email'))
                        <a href="mailto:{{ $site->get('email') }}" class="flex items-center gap-2.5 text-[0.9375rem] hover:text-white"><x-glyph name="mail" class="size-5 text-orange" />{{ $site->get('email') }}</a>
                    @endif
                </div>
                @php($social = collect($site->get('social_links', []))->filter(fn ($s) => filled($s['url'] ?? null) && str_starts_with($s['url'], 'https://')))
                @if ($social->isNotEmpty())
                    <ul class="mt-6 flex flex-wrap gap-2">
                        @foreach ($social as $link)
                            <li><a href="{{ $link['url'] }}" rel="noopener" target="_blank" class="inline-flex min-h-10 items-center rounded-lg border border-white/15 px-3 text-sm hover:border-white/40 hover:text-white">{{ $link['network'] ?? 'Social' }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-10 md:grid-cols-4">
                <div>
                    <h3 class="text-sm font-semibold tracking-[0.08em] text-white uppercase">Services</h3>
                    <ul class="mt-4 space-y-2.5 text-[0.9375rem]">
                        @foreach ($allServices->take(8) as $item)
                            <li><a href="{{ $item['url'] }}" class="hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                        <li><a href="{{ route('services.index') }}" class="font-medium text-orange hover:text-white">All services</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold tracking-[0.08em] text-white uppercase">States</h3>
                    <ul class="mt-4 space-y-2.5 text-[0.9375rem]">
                        @foreach (collect($nav['states'])->take(7) as $state)
                            <li><a href="{{ $state['url'] }}" class="hover:text-white">{{ $state['name'] }}</a></li>
                        @endforeach
                        <li><a href="{{ route('locations.index') }}" class="font-medium text-orange hover:text-white">All locations</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold tracking-[0.08em] text-white uppercase">Cities</h3>
                    <ul class="mt-4 space-y-2.5 text-[0.9375rem]">
                        @foreach ($nav['cities'] as $city)
                            <li><a href="{{ $city['url'] }}" class="hover:text-white">{{ $city['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold tracking-[0.08em] text-white uppercase">Company</h3>
                    <ul class="mt-4 space-y-2.5 text-[0.9375rem]">
                        <li><a href="{{ $nav['about'] }}" class="hover:text-white">About us</a></li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-white">HVAC resource center</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                    </ul>
                    <h3 class="mt-8 text-sm font-semibold tracking-[0.08em] text-white uppercase">Legal</h3>
                    <ul class="mt-4 space-y-2.5 text-[0.9375rem]">
                        <li><a href="{{ route('disclaimer') }}" class="hover:text-white">Disclaimer</a></li>
                        @foreach ($nav['legal'] as $page)
                            <li><a href="{{ $page['url'] }}" class="hover:text-white">{{ $page['title'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-14">
            <x-disclaimer variant="footer" />
        </div>

        <div class="mt-8 flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-on-dark-muted md:flex-row md:items-center md:justify-between">
            <p>&copy; {{ now()->year }} {{ $site->name() }}. All rights reserved.</p>
            <p>An independent referral service for U.S. homeowners.</p>
        </div>
    </div>
</footer>
