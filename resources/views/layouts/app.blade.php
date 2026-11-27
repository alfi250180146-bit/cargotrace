<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CargoTrace') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-800">
        <div x-data="{ sidebar: false }" class="min-h-screen">
            @include('layouts.navigation')

            <div class="lg:pl-64 flex flex-col min-h-screen">
                @include('layouts.topbar')

                @isset($header)
                    <header class="bg-white border-b border-gray-200">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>

                <footer class="border-t border-gray-200 bg-white px-4 sm:px-6 lg:px-8 py-4 text-xs text-gray-500">
                    CargoTrace &middot; Web Programming 2 final project
                </footer>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>