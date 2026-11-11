{{-- Responsive image from the media library: WebP srcset + original fallback, explicit dimensions (no layout shift). --}}
@props(['media' => null, 'sizes' => '100vw', 'eager' => false, 'alt' => null, 'class' => '', 'fallback' => null])
@if ($media)
    @php($srcset = $media->srcset())
    <picture>
        @if ($srcset)
            <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}">
        @endif
        <img src="{{ $media->url() }}" alt="{{ $alt ?? $media->alt ?? '' }}" width="{{ $media->width }}" height="{{ $media->height }}"
             @if ($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async" {{ $attributes->merge(['class' => $class]) }}>
    </picture>
@elseif ($fallback)
    <img src="{{ $fallback }}" alt="{{ $alt ?? '' }}" @if (! $eager) loading="lazy" @endif decoding="async" {{ $attributes->merge(['class' => $class]) }}>
@else
    <div {{ $attributes->merge(['class' => $class.' bg-[linear-gradient(135deg,#153445,#1E4155)]']) }} aria-hidden="true"></div>
@endif
