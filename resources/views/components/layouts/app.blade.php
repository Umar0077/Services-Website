@props(['seo', 'minimal' => false, 'bodyClass' => null])
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.classList.replace('no-js', 'js');</script>
    <x-tracking position="head" />
    <x-seo :seo="$seo" />
    <meta name="theme-color" content="#0E2938">
    <link rel="icon" href="{{ $site->asset('favicon') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @php
        // Preload the two self-hosted fonts emitted by Vite (avoids a late swap on the hero heading).
        $manifestPath = public_path('build/manifest.json');
        $fonts = is_file($manifestPath)
            ? collect(json_decode(file_get_contents($manifestPath), true)['resources/css/app.css']['assets'] ?? [])->filter(fn ($a) => str_ends_with($a, '.woff2'))
            : collect();
    @endphp
    @foreach ($fonts as $font)
        <link rel="preload" href="{{ asset('build/'.$font) }}" as="font" type="font/woff2" crossorigin>
    @endforeach
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ $bodyClass ?? 'flex min-h-dvh flex-col pb-[76px] md:pb-0' }}">
    <x-tracking position="body-start" />
    <a href="#main" class="sr-only z-[100] rounded-lg bg-teal-900 px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to main content</a>

    @if ($minimal)
        {{ $header ?? '' }}
    @else
        <x-header />
    @endif

    <main id="main" class="flex-1" tabindex="-1">
        {{ $slot }}
    </main>

    @if ($minimal)
        {{ $footer ?? '' }}
    @else
        <x-footer />
        <x-mobile-call-bar />
    @endif

    <x-toasts />
    <x-tracking position="body-end" />
</body>
</html>
