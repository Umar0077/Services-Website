@props(['service', 'variant' => 'image'])
@if ($variant === 'image')
    <article class="card card-hover group relative flex h-full flex-col overflow-hidden">
        <div class="relative aspect-[16/10] overflow-hidden">
            <x-picture :media="$service->heroMedia" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" class="zoom-img size-full object-cover" />
            <span class="absolute bottom-0 left-5 inline-flex size-12 translate-y-1/2 items-center justify-center rounded-xl bg-white text-orange-ink shadow-[var(--shadow-card)]">
                <x-glyph :name="$service->icon" class="size-6" />
            </span>
        </div>
        <div class="flex flex-1 flex-col px-5 pt-9 pb-6 md:px-6">
            <h3 class="text-xl font-bold tracking-[-0.01em] text-ink">
                <a href="{{ $service->publicUrl() }}" class="after:absolute after:inset-0 focus-visible:outline-none">{{ $service->title }}</a>
            </h3>
            <p class="mt-2.5 flex-1 text-[0.9375rem] leading-relaxed">{{ $service->short_description }}</p>
            <span class="link-arrow mt-5 text-[0.9375rem]">Learn more <x-glyph name="arrow-right" /></span>
        </div>
    </article>
@else
    <a href="{{ $service->publicUrl() }}" class="card card-hover group flex items-start gap-4 p-5">
        <span class="icon-chip"><x-glyph :name="$service->icon" /></span>
        <span class="min-w-0">
            <span class="block text-[1.0625rem] font-semibold text-ink">{{ $service->title }}</span>
            <span class="mt-1 line-clamp-2 block text-sm leading-relaxed">{{ $service->short_description }}</span>
        </span>
    </a>
@endif
