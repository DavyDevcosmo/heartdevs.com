@props ([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
])

@php
    $pageTitle = ($title ? $title . ' - ' : '') . config('app.name');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <title>{{ $pageTitle }}</title>

    {{-- The page is meant to be pasted into LinkedIn and Discord: without these,
         the unfurl falls back to the site name and a blank card. --}}
    @if ($description)
        <meta name="description" content="{{ $description }}" />
    @endif

    <link rel="canonical" href="{{ url()->current() }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}" />
    <meta property="og:type" content="{{ $type }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="{{ $pageTitle }}" />
    <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}" />

    @if ($description)
        <meta property="og:description" content="{{ $description }}" />
    @endif

    @if ($image)
        <meta property="og:image" content="{{ $image }}" />
        <meta property="og:image:alt" content="{{ $title }}" />
    @endif

    @vite (['app-modules/he4rt/resources/css/theme.css'])
    @fluxAppearance
</head>
<body class="min-h-screen antialiased">
    <x-portal::navbar />

    {{ $slot }}
    @fluxScripts
</body>
</html>
