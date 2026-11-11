<x-layouts.app :seo="$seo">
    <section class="section">
        <div class="container-site">
            <div class="card mx-auto max-w-2xl p-8 text-center md:p-12" role="status">
                <span class="mx-auto inline-flex size-16 items-center justify-center rounded-full bg-mint text-teal-700"><x-glyph name="check-circle" class="size-8" /></span>
                <h1 class="h-page mt-6">Request received</h1>
                <p class="lead mx-auto mt-4 max-w-lg">Thank you. Your request has been saved, and we will work to connect you with an available independent provider in your area.</p>
                <p class="mt-4 text-[0.9375rem]">For urgent problems, calling is the fastest way to get connected.</p>
                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <x-phone class="btn btn-primary btn-lg" label="Call" />
                    <a href="{{ route('blog.index') }}" class="btn btn-outline btn-lg">Browse HVAC guides</a>
                </div>
                <x-availability-note class="mx-auto mt-8 max-w-lg text-left" />
            </div>
        </div>
    </section>
</x-layouts.app>
