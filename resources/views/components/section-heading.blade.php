{{-- Section heading. `highlight` renders a phrase in brand orange (large display text, so 3:1 contrast applies). --}}
@props(['eyebrow' => null, 'title', 'highlight' => null, 'intro' => null, 'align' => 'left', 'id' => null, 'level' => 'h2'])
<div {{ $attributes->merge(['class' => ($align === 'center' ? 'mx-auto max-w-[760px] text-center' : 'max-w-[760px]')]) }}>
    @if ($eyebrow)<p class="eyebrow mb-4 {{ $align === 'center' ? 'justify-center' : '' }}">{{ $eyebrow }}</p>@endif
    <{{ $level }} @if($id) id="{{ $id }}" @endif class="h-section">{{ $title }}@if ($highlight) <span class="text-orange">{{ $highlight }}</span>@endif</{{ $level }}>
    @if ($intro)<p class="lead mt-4 {{ $align === 'center' ? 'mx-auto' : '' }} max-w-[640px]">{{ $intro }}</p>@endif
</div>
