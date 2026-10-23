<!DOCTYPE html>
<html lang="en-GB">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Runs before first paint so a dark-mode visitor never sees a light flash. ThemeToggle.vue writes the same key. --}}
        <script>
            (function(){try{var t=localStorage.getItem('swvh-theme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark')}catch(e){}})()
        </script>

        <link rel="icon" href="/icon.png" type="image/png">
        <link rel="icon" href="/icon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/icon.png">
        <meta name="application-name" content="{{ config('site.name') }}">

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])

        @isset($seo)
            {{-- See App\Support\Seo. Printed here, not from Vue, so it is in the HTML with or without JavaScript. --}}
            <title>{{ $seo->fullTitle() }}</title>
            <meta name="description" content="{{ $seo->description() }}">
            <link rel="canonical" href="{{ $seo->canonical() }}">
            <meta name="robots" content="{{ $seo->robots() }}">
            <meta property="og:site_name" content="{{ config('site.name') }}">
            <meta property="og:locale" content="en_GB">
            <meta property="og:title" content="{{ $seo->title }}">
            <meta property="og:description" content="{{ $seo->description() }}">
            <meta property="og:url" content="{{ $seo->canonical() }}">
            @if ($seo->imageUrl())
                <meta property="og:image" content="{{ $seo->imageUrl() }}">
            @endif
        @else
            <x-inertia::head>
                <title>{{ config('site.name') }}</title>
            </x-inertia::head>
        @endisset
    </head>
    <body class="bg-white text-stone-950 antialiased dark:bg-stone-950 dark:text-white">
        <x-inertia::app />
    </body>
</html>
