<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- SEO & Meta -->
        <meta name="description" content="Personal portfolio and software engineering showcase for ABM Digital Lab.">
        <title inertia>{{ config('app.name', 'ABM Digital Lab - Portfolio') }}</title>

        <!-- PWA Manifest & Meta -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#4f46e5">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Bhavesh Portfolio">
        <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased text-slate-900 bg-white selection:bg-indigo-500 selection:text-white">
        @inertia

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('PWA Service Worker registered with scope: ', registration.scope);
                        })
                        .catch(function(error) {
                            console.warn('PWA Service Worker registration failed: ', error);
                        });
                });
            }
        </script>
    </body>
</html>
