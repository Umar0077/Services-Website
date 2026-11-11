<x-layouts.app :seo="$seo">
    <x-page-hero compact title="Contact us" eyebrow="Get help" :crumbs="$seo->breadcrumbs"
        intro="Call for the quickest connection, or send a request online and an independent provider in your area may follow up." />

    <section class="section">
        <div class="container-site grid gap-10 lg:grid-cols-[380px_1fr] lg:gap-14">
            <div class="space-y-6">
                <div class="surface-teal on-dark rounded-[20px] p-7">
                    <span class="icon-chip"><x-glyph name="phone" /></span>
                    <h2 class="mt-5 text-xl font-bold text-white">Call to get connected</h2>
                    <p class="mt-2 text-[0.9375rem] leading-relaxed">Describe your system and the problem. Our connection service works to route you to an available independent provider.</p>
                    <x-phone class="btn btn-primary btn-lg mt-6 w-full" />
                    @if ($site->get('hours_text'))
                        <p class="mt-4 flex items-center gap-2 text-sm"><x-glyph name="clock" class="size-4 text-orange" />{{ $site->get('hours_text') }}</p>
                    @endif
                    <x-availability-note dark class="mt-5 border-t border-white/10 pt-4" />
                </div>
                @if ($site->get('email'))
                    <div class="card p-6">
                        <span class="icon-chip"><x-glyph name="mail" /></span>
                        <h2 class="mt-4 text-lg font-bold text-ink">Email</h2>
                        <a href="mailto:{{ $site->get('email') }}" class="text-link mt-1 inline-block">{{ $site->get('email') }}</a>
                    </div>
                @endif
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-ink">What happens next</h2>
                    <ol class="mt-4 space-y-4 text-[0.9375rem]">
                        @foreach ($site->get('how_steps', []) as $step)
                            <li class="flex gap-3"><span class="display flex size-8 shrink-0 items-center justify-center rounded-full bg-orange-50 text-xl text-orange-ink">{{ $loop->iteration }}</span><span><strong class="text-ink">{{ $step['title'] }}.</strong> {{ $step['text'] }}</span></li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <x-contact-form :selected-service="$selectedService" />
        </div>
    </section>
</x-layouts.app>
