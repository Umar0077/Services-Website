<x-layouts.app :seo="$seo">
    <x-page-hero compact title="Disclaimer" eyebrow="Legal" :crumbs="$seo->breadcrumbs"
        intro="HVAC Services is an independent referral service. Please read this before hiring a provider." />

    <section class="bg-white py-14 md:py-20">
        <div class="container-site">
            <div class="mx-auto max-w-[760px]">
                <div class="rounded-[20px] border-l-4 border-orange bg-page p-6 md:p-10">
                    <x-disclaimer variant="page" />
                </div>
                <div class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-[0.9375rem]">
                    <a href="{{ \App\Models\Page::urlFor('disclosures', '/service-disclosures') }}" class="text-link">Service disclosures</a>
                    <a href="{{ \App\Models\Page::urlFor('privacy', '/privacy-policy') }}" class="text-link">Privacy Policy</a>
                    <a href="{{ \App\Models\Page::urlFor('terms', '/terms') }}" class="text-link">Terms and Conditions</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
