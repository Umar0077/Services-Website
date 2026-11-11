@props(['items' => [], 'dark' => false])
@if (count($items) > 1)
    <nav aria-label="Breadcrumb" {{ $attributes }}>
        <ol class="breadcrumb {{ $dark ? 'text-on-dark-muted' : 'text-muted' }}">
            @foreach ($items as $crumb)
                <li class="flex items-center gap-2">
                    @if (! $loop->first)<x-glyph name="chevron-right" class="size-3.5 opacity-60" />@endif
                    @if ($loop->last)
                        <span aria-current="page" class="{{ $dark ? 'text-white' : 'text-ink' }} line-clamp-1 font-medium">{{ $crumb['name'] }}</span>
                    @else
                        <a href="{{ $crumb['url'] }}" class="{{ $dark ? 'hover:text-white' : 'hover:text-ink' }}">{{ $crumb['name'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
