<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'CargoTrace') }} - Global shipment visibility and tracking</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-gray-800">
        <header class="bg-white border-b border-stone-200">
            <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2 text-brand-900">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8l-9-5-9 5 9 5 9-5z" />
                        <path d="M3 8v8l9 5 9-5V8" />
                        <path d="M12 13v8" />
                    </svg>
                    <span class="text-lg font-semibold">CargoTrace</span>
                </a>

                <nav class="flex items-center gap-5 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-md bg-brand-600 text-white font-medium hover:bg-brand-700">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-gray-700 hover:text-brand-700">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-brand-600 text-white font-medium hover:bg-brand-700">Register</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <section class="bg-brand-100">
            <div class="max-w-6xl mx-auto px-6 py-20">
                <div class="max-w-2xl">
                    <h1 class="text-4xl font-semibold leading-tight text-brand-900">Global shipment visibility and tracking</h1>
                    <p class="mt-5 text-lg text-brand-800">
                        CargoTrace helps logistics teams and their customers follow every shipment from origin to destination,
                        and see delays and exceptions as soon as they happen.
                    </p>

                    @guest
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('login') }}" class="px-5 py-3 rounded-md bg-brand-600 text-white font-medium hover:bg-brand-700">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-3 rounded-md border border-brand-600 text-brand-800 font-medium hover:bg-brand-50">Create an account</a>
                            @endif
                        </div>
                    @endguest
                </div>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 py-16">
            <h2 class="text-2xl font-semibold text-gray-900">What you can do</h2>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border border-stone-200 p-6 rounded-md">
                    <div class="text-sm font-semibold text-brand-600">01</div>
                    <h3 class="mt-2 font-semibold text-gray-900">Track every step</h3>
                    <p class="mt-2 text-sm text-gray-600">A full timeline of status changes and checkpoints, with the route shown on a map.</p>
                </div>
                <div class="border border-stone-200 p-6 rounded-md">
                    <div class="text-sm font-semibold text-brand-600">02</div>
                    <h3 class="mt-2 font-semibold text-gray-900">Detect delays</h3>
                    <p class="mt-2 text-sm text-gray-600">Clear delay rules with a severity level and the numbers behind every result.</p>
                </div>
                <div class="border border-stone-200 p-6 rounded-md">
                    <div class="text-sm font-semibold text-brand-600">03</div>
                    <h3 class="mt-2 font-semibold text-gray-900">Control access</h3>
                    <p class="mt-2 text-sm text-gray-600">Administrators, operators and customers each see only what their role allows.</p>
                </div>
            </div>
        </section>

        <section class="bg-stone-50 border-y border-stone-200">
            <div class="max-w-6xl mx-auto px-6 py-16">
                <h2 class="text-2xl font-semibold text-gray-900">How it works</h2>

                <ol class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm text-gray-700">
                    <li class="border-l-4 border-brand-400 bg-white p-5">An operator registers a shipment with its carrier, route and expected arrival.</li>
                    <li class="border-l-4 border-brand-400 bg-white p-5">Tracking events are added as the cargo moves, and the timeline keeps the full history.</li>
                    <li class="border-l-4 border-brand-400 bg-white p-5">Customers follow progress, ETA and exceptions for their own shipments.</li>
                </ol>
            </div>
        </section>

        <footer class="py-8 text-center text-xs text-gray-500">
            CargoTrace &middot; Web Programming 2 final project
        </footer>
    </body>
</html>