@props(['title', 'text' => null, 'icon' => 'search'])
<div class="card mx-auto flex max-w-xl flex-col items-center px-6 py-14 text-center">
    <span class="icon-chip"><x-glyph :name="$icon" /></span>
    <h2 class="mt-5 text-xl font-bold text-ink">{{ $title }}</h2>
    @if ($text)<p class="mt-2 max-w-md">{{ $text }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-6">{{ $slot }}</div>@endif
</div>
