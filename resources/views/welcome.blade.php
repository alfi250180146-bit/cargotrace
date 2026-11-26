<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'CargoTrace') }} - Global shipment visibility</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-900 text-white">
        <div class="min-h-screen flex flex-col">
            <header class="max-w-6xl w-full mx-auto px-6 py-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="h-8 w-8 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8l-9-5-9 5 9 5 9-5z" />
                        <path d="M3 8v8l9 5 9-5V8" />
                        <path d="M12 13v8" />
                    </svg>
                    <span class="text-xl font-semibold">CargoTrace</span>
                </div>

                <nav class="flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-md bg-indigo-500 hover:bg-indigo-400 font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-md hover:bg-white/10">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-indigo-500 hover:bg-indigo-400 font-medium">Register</a>
                        @endif
                    @endauth
                </nav>
            </header>

            <main class="flex-1 max-w-6xl w-full mx-auto px-6 flex items-center">
                <div class="py-16 max-w-2xl">
                    <h1 class="text-4xl sm:text-5xl font-semibold leading-tight">
                        Global shipment visibility &amp; tracking
                    </h1>
                    <p class="mt-5 text-lg text-slate-300">
                        Follow every shipment from origin to destination, see the full journey on a map,
                        and catch delays and exceptions before your customers ask.
                    </p>

                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div class="rounded-xl bg-white/5 p-4">
                            <div class="font-semibold">Live timeline</div>
                            <div class="mt-1 text-slate-400">Every status change and checkpoint in order.</div>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4">
                            <div class="font-semibold">Delay detection</div>
                            <div class="mt-1 text-slate-400">Clear rules with severity and the numbers behind them.</div>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4">
                            <div class="font-semibold">Role-based access</div>
                            <div class="mt-1 text-slate-400">Customers only see their own shipments.</div>
                        </div>
                    </div>

                    @guest
                        <div class="mt-8">
                            <a href="{{ route('login') }}" class="inline-block px-6 py-3 rounded-md bg-indigo-500 hover:bg-indigo-400 font-medium">
                                Log in to continue
                            </a>
                        </div>
                    @endguest
                </div>
            </main>

            <footer class="py-6 text-center text-xs text-slate-500">
                CargoTrace &middot; Web Programming 2 final project
            </footer>
        </div>
    </body>
</html>