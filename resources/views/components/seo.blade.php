@props(['seo'])
<title>{{ $seo->fullTitle() }}</title>
<meta name="description" content="{{ $seo->description }}">
<meta name="robots" content="{{ $seo->robots }}{{ str_starts_with($seo->robots, 'index') ? ',max-image-preview:large' : '' }}">
<link rel="canonical" href="{{ $seo->canonical }}">

<meta property="og:site_name" content="{{ $site->name() }}">
<meta property="og:locale" content="en_US">
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:title" content="{{ $seo->ogTitle ?: $seo->fullTitle() }}">
<meta property="og:description" content="{{ $seo->ogDescription ?: $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:image" content="{{ $seo->imageUrl() }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->ogTitle ?: $seo->fullTitle() }}">
<meta name="twitter:description" content="{{ $seo->ogDescription ?: $seo->description }}">
<meta name="twitter:image" content="{{ $seo->imageUrl() }}">

@php
    // Every page carries the Organization node that Service/Article schema reference by @id.
    $graphs = collect($seo->schema)->contains(fn ($g) => ($g['@type'] ?? null) === 'Organization')
        ? $seo->schema
        : [\App\Support\Seo\Schema::organization(), ...$seo->schema];
@endphp
@foreach ($graphs as $graph)
<script type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
