@php
    $seo = \App\Support\Seo\SeoData::make('Page not found');
    $seo->robots = 'noindex,follow';
@endphp
<x-layouts.app :seo="$seo">
    <section class="section">
        <div class="container-site text-center">
            <p class="display text-[7rem] leading-none text-orange md:text-[9rem]">404</p>
            <h1 class="h-page mt-2">We could not find that page</h1>
            <p class="lead mx-auto mt-4 max-w-xl">The page may have moved. Try one of these instead, or call to get connected with an HVAC provider.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn btn-dark btn-lg">Go to the homepage</a>
                <a href="{{ route('services.index') }}" class="btn btn-outline btn-lg">Browse services</a>
                <x-phone class="btn btn-primary btn-lg" label="Call" />
            </div>
        </div>
    </section>
</x-layouts.app>
