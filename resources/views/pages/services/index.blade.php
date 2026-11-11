<x-layouts.app :seo="$seo">
    @php($hero = $services->firstWhere('slug', 'ac-installation')?->heroMedia ?? $services->first()?->heroMedia)
    <x-page-hero title="Home HVAC services" eyebrow="Services" :media="$hero" :crumbs="$seo->breadcrumbs"
        intro="Heating, cooling, and indoor air quality services for your home. Learn what each involves, then get connected with an available local provider.">
        <x-slot:actions>
            <x-phone class="btn btn-primary btn-lg" label="Call" />
            <a href="{{ route('contact') }}" class="btn btn-ghost-light btn-lg">Request help online</a>
        </x-slot:actions>
    </x-page-hero>

    @forelse ($groups as $group => $items)
        <section class="section {{ $loop->even ? 'bg-white' : '' }}" aria-labelledby="group-{{ $group }}">
            <div class="container-site">
                <x-section-heading :id="'group-'.$group" :eyebrow="str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)" :title="\App\Models\Service::GROUPS[$group] ?? ucfirst($group)" class="reveal" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $service)
                        <div class="reveal"><x-service-card :service="$service" /></div>
                    @endforeach
                </div>
                @if ($group === 'emergency')
                    <x-availability-note class="mt-8 max-w-3xl" />
                @endif
            </div>
        </section>
    @empty
        <section class="section"><div class="container-site"><x-empty-state title="Services are being updated" text="Please call us to describe what you need." /></div></section>
    @endforelse

    <x-cta-band />
</x-layouts.app>
