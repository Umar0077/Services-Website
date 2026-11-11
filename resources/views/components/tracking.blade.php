@props(['position' => 'head'])
@php
    // IDs are validated against strict patterns, so an admin typo can never inject markup or scripts.
    $enabled = (bool) $site->get('analytics_enabled');
    $gtm = preg_match('/^GTM-[A-Z0-9]{4,12}$/', (string) $site->get('gtm_id')) ? $site->get('gtm_id') : null;
    $ga4 = preg_match('/^G-[A-Z0-9]{6,14}$/', (string) $site->get('ga4_id')) ? $site->get('ga4_id') : null;
    $ads = preg_match('/^AW-\d{6,14}$/', (string) $site->get('google_ads_id')) ? $site->get('google_ads_id') : null;
    $mcProgram = preg_match('/^\d{1,10}$/', (string) $site->get('marketcall_program_id')) ? $site->get('marketcall_program_id') : '0';
    $marketcall = (bool) $site->get('marketcall_enabled');
@endphp
@if ($enabled)
    @if ($position === 'head')
        @if ($gtm)
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json($gtm));</script>
        @endif
        @if ($ga4 || $ads)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 ?? $ads }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());@if($ga4)gtag('config',@json($ga4));@endif @if($ads)gtag('config',@json($ads));@endif</script>
        @endif
    @elseif ($position === 'body-start' && $gtm)
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
    @elseif ($position === 'body-end' && $marketcall)
{{-- MarketCall call tracking, preserved from the legacy site. Phone elements keep the .phone-link / .phone-display hooks it expects. --}}
<script src="https://www.marketcall.com/js/landing.js" async defer></script>
<script src="https://www.marketcall.com/js/mclead.js?program_id={{ $mcProgram }}" async defer></script>
<script src="https://www.marketcall.com/js/mclegal.js?program_id={{ $mcProgram }}" async defer></script>
    @endif
@endif
