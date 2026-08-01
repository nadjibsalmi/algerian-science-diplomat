<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- SEO & sharing meta — audit fix: previously absent --}}
        <meta name="description" content="Plateforme centrale des opportunités scientifiques, académiques et professionnelles internationales pour les étudiants et chercheurs algériens.">
        <meta property="og:title" content="{{ config('app.name', 'Algerian Science Diplomat') }}">
        <meta property="og:description" content="Bourses, stages et programmes de recherche publiés par les ambassades et organisations partenaires en Algérie.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">

        {{-- Favicon --}}
        <link rel="icon" type="image/x-icon" href="/favicon.ico">

        {{-- Font: Instrument Sans — audit fix: font was declared in CSS but never loaded --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wdth,wght@0,75..100,400..700;1,75..100,400..700&display=swap" rel="stylesheet">

        <title inertia>{{ config('app.name', 'Algerian Science Diplomat') }}</title>

        {{-- Ziggy route helpers — audit fix: ZiggyVue was registered in app.ts
             but the @routes directive was missing, so the Ziggy config object
             was never injected into the page and route() calls would throw. --}}
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
