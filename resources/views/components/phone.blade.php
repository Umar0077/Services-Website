{{-- Click-to-call link. Number comes from App\Support\Phone (approved tracking numbers only). --}}
@props(['label' => null, 'icon' => true, 'class' => 'btn btn-primary'])
@php($number = \App\Support\Phone::current())
<a href="{{ \App\Support\Phone::tel($number) }}" {{ $attributes->merge(['class' => $class.' phone-link']) }} data-track="call">
    @if ($icon)<x-glyph name="phone" />@endif
    <span>@if ($label){{ $label }} @endif<span class="phone-display">{{ \App\Support\Phone::format($number) }}</span></span>
</a>
