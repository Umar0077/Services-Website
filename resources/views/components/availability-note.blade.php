{{-- Shown wherever same-day or 24/7 service is mentioned. Text comes from the approved disclaimer. --}}
@props(['dark' => false])
@php($note = \App\Support\Disclaimer::availabilityNote())
@if ($note)
    <p {{ $attributes->merge(['class' => 'flex gap-2.5 text-[0.875rem] leading-relaxed '.($dark ? 'text-on-dark' : 'text-body')]) }}>
        <x-glyph name="info" class="mt-0.5 size-4 shrink-0 {{ $dark ? 'text-orange' : 'text-teal-700' }}" />
        <span>{{ $note }} <a href="{{ route('disclaimer') }}" class="{{ $dark ? 'text-white' : 'text-teal-900' }} font-medium underline underline-offset-2">Read the disclaimer</a></span>
    </p>
@endif
