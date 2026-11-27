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
    </head>
    <body class="font-sans text-gray-800 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <div class="hidden lg:flex flex-col justify-between bg-brand-900 p-12 text-white"
                 style="background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 48px 48px;">
                <a href="/" class="flex items-center gap-3">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8l-9-5-9 5 9 5 9-5z" />
                        <path d="M3 8v8l9 5 9-5V8" />
                        <path d="M12 13v8" />
                    </svg>
                    <span class="text-xl font-semibold">CargoTrace</span>
                </a>

                <div class="max-w-md">
                    <h2 class="text-3xl font-semibold leading-snug">Global shipment visibility and tracking</h2>
                    <ul class="mt-6 space-y-3 text-brand-100">
                        <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 bg-white"></span>Follow each shipment from origin to destination.</li>
                        <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 bg-white"></span>Monitor delays and exceptions in one place.</li>
                        <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 shrink-0 bg-white"></span>Access is limited by role, customers see only their own shipments.</li>
                    </ul>
                </div>

                <div class="text-sm text-brand-300">Web Programming 2 final project</div>
            </div>

            <div class="flex min-h-screen items-center justify-center bg-white px-6 py-12">
                <div class="w-full max-w-sm">
                    <a href="/" class="mb-8 flex items-center gap-2 text-brand-900 lg:hidden">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8l-9-5-9 5 9 5 9-5z" />
                            <path d="M3 8v8l9 5 9-5V8" />
                            <path d="M12 13v8" />
                        </svg>
                        <span class="text-lg font-semibold">CargoTrace</span>
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>