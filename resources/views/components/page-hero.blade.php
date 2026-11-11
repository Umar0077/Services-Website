{{-- Interior page hero: dark teal, photo on the right behind the brand overlay, breadcrumbs, H1, intro, actions. --}}
@props(['title', 'eyebrow' => null, 'intro' => null, 'media' => null, 'crumbs' => [], 'compact' => false])
<section class="surface-teal on-dark relative isolate overflow-hidden">
    @if ($media)
        <div class="absolute inset-y-0 right-0 -z-10 w-full md:w-[62%]">
            <x-picture :media="$media" sizes="(min-width: 768px) 62vw, 100vw" eager class="size-full object-cover" alt="" />
        </div>
        <div class="absolute inset-0 -z-10" style="background: var(--gradient-hero)" aria-hidden="true"></div>
        <div class="absolute inset-0 -z-10 bg-teal-900/55 md:hidden" aria-hidden="true"></div>
    @else
        <div aria-hidden="true" class="absolute -top-24 right-[-10%] -z-10 size-[480px] rounded-full bg-orange/10 blur-3xl"></div>
    @endif

    <div class="container-site {{ $compact ? 'py-12 md:py-16' : 'py-14 md:py-24' }}">
        <x-breadcrumbs :items="$crumbs" dark class="mb-8" />
        <div class="max-w-[720px]">
            @if ($eyebrow)<p class="eyebrow mb-4">{{ $eyebrow }}</p>@endif
            <h1 class="h-page text-white">{{ $title }}</h1>
            @if ($intro)
                <p class="lead mt-5 max-w-[620px] text-on-dark">{{ $intro }}</p>
            @endif
            @if (isset($actions) && $actions->isNotEmpty())
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">{{ $actions }}</div>
            @endif
            {{ $slot }}
        </div>
    </div>
    <div class="h-1 w-full" style="background: var(--gradient-orange)" aria-hidden="true"></div>
</section>
