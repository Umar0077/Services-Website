{{-- Brand logo: WebP with PNG fallback for the bundled files; uploaded replacements are served as uploaded. --}}
@props(['variant' => 'light', 'class' => 'h-11 w-auto', 'eager' => true])
@php
    $key = $variant === 'dark' ? 'logo_dark_background' : 'logo_light_background';
    $src = $site->asset($key);
    $webp = preg_match('#/images/brand/(logo-hvac-services(?:-white)?)\.png$#', (string) $src, $m) ? asset('images/brand/'.$m[1].'.webp') : null;
@endphp
<picture>
    @if ($webp)<source type="image/webp" srcset="{{ $webp }}">@endif
    <img src="{{ $src }}" alt="{{ $site->name() }}" width="638" height="168" class="{{ $class }}" @unless($eager) loading="lazy" @endunless decoding="async">
</picture>
