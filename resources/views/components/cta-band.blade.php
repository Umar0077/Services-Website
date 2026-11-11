{{-- Final call-to-action band used at the bottom of most pages. --}}
@props(['heading' => null, 'text' => null, 'availability' => false])
<section class="section-tight">
    <div class="container-site">
        <div class="surface-teal on-dark relative isolate overflow-hidden rounded-[24px] px-6 py-12 md:px-14 md:py-16">
            <div aria-hidden="true" class="absolute -top-28 -right-20 -z-10 size-[420px] rounded-full bg-orange/15 blur-3xl"></div>
            <div aria-hidden="true" class="absolute bottom-0 left-0 -z-10 h-1 w-40" style="background: var(--gradient-orange)"></div>
            <div class="grid items-center gap-10 lg:grid-cols-[1.4fr_1fr]">
                <div>
                    <h2 class="h-section text-white">{{ $heading ?? $site->get('final_cta_heading') }}</h2>
                    <p class="lead mt-4 max-w-[560px]">{{ $text ?? $site->get('final_cta_text') }}</p>
                </div>
                <div class="flex flex-col gap-3 lg:items-end">
                    <x-phone class="btn btn-primary btn-lg w-full sm:w-auto" label="Call" />
                    <a href="{{ route('contact') }}" class="btn btn-ghost-light btn-lg w-full sm:w-auto">Send a request online <x-glyph name="arrow-right" /></a>
                </div>
            </div>
            @if ($availability)
                <x-availability-note dark class="mt-8 max-w-[760px] border-t border-white/10 pt-6" />
            @endif
        </div>
    </div>
</section>
