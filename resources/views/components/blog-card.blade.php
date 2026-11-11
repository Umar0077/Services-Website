@props(['post', 'featured' => false])
@if ($featured)
    <article class="card card-hover group relative grid overflow-hidden md:grid-cols-[1.15fr_1fr]">
        <div class="relative aspect-[16/10] overflow-hidden md:aspect-auto md:min-h-[380px]">
            <x-picture :media="$post->featuredMedia" sizes="(min-width: 768px) 55vw, 100vw" eager class="zoom-img absolute inset-0 size-full object-cover" />
        </div>
        <div class="flex flex-col justify-center p-6 md:p-10">
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="rounded-full bg-orange-50 px-3 py-1 font-semibold text-orange-ink">Featured</span>
                @if ($post->category)<span class="font-medium text-teal-700">{{ $post->category->name }}</span>@endif
            </div>
            <h2 class="mt-4 text-[1.75rem] leading-tight font-bold tracking-[-0.015em] text-ink md:text-[2rem]">
                <a href="{{ $post->publicUrl() }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
            </h2>
            @if ($post->excerpt)<p class="mt-4 leading-relaxed">{{ $post->excerpt }}</p>@endif
            <p class="mt-6 text-sm text-muted">
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
                <span aria-hidden="true"> · </span>{{ $post->reading_minutes }} min read
            </p>
        </div>
    </article>
@else
    <article class="card card-hover group relative flex h-full flex-col overflow-hidden">
        <div class="aspect-[16/10] overflow-hidden">
            <x-picture :media="$post->featuredMedia" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" class="zoom-img size-full object-cover" />
        </div>
        <div class="flex flex-1 flex-col p-5 md:p-6">
            @if ($post->category)<p class="text-[0.8125rem] font-semibold tracking-[0.06em] text-orange-ink uppercase">{{ $post->category->name }}</p>@endif
            <h3 class="mt-2 text-[1.1875rem] leading-snug font-bold text-ink">
                <a href="{{ $post->publicUrl() }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
            </h3>
            @if ($post->excerpt)<p class="mt-2.5 line-clamp-3 flex-1 text-[0.9375rem] leading-relaxed">{{ $post->excerpt }}</p>@endif
            <p class="mt-5 text-sm text-muted">
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
                <span aria-hidden="true"> · </span>{{ $post->reading_minutes }} min read
            </p>
        </div>
    </article>
@endif
