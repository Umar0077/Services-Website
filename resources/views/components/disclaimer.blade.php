{{-- The client-approved disclaimer, always read from App\Support\Disclaimer so every page shows identical text. --}}
@props(['variant' => 'footer'])
@php($paragraphs = \App\Support\Disclaimer::paragraphs())
@if ($variant === 'footer')
    <section aria-labelledby="footer-disclaimer" class="rounded-[var(--radius-card)] border border-white/12 bg-white/[0.04] p-5 md:p-7">
        <h2 id="footer-disclaimer" class="mb-3 text-sm font-semibold tracking-[0.08em] text-white uppercase">Important information</h2>
        <div class="space-y-3 text-[0.9375rem] leading-relaxed text-on-dark">
            @foreach ($paragraphs as $p)
                @if ($p['label'])
                    <p class="font-semibold text-white">{{ $p['text'] }}</p>
                @else
                    <p>{{ $p['text'] }}</p>
                @endif
            @endforeach
        </div>
    </section>
@else
    <div class="space-y-5 text-[1.0625rem] leading-[1.8] text-[#374151]">
        @foreach ($paragraphs as $p)
            @if ($p['label'])
                <p class="text-lg font-bold text-ink">{{ $p['text'] }}</p>
            @else
                <p>{{ $p['text'] }}</p>
            @endif
        @endforeach
    </div>
@endif
