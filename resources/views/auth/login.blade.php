<x-guest-layout>
    <h1 class="text-2xl font-semibold text-gray-900">Sign in</h1>
    <p class="mt-1 text-sm text-gray-500">Use your CargoTrace account to continue.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-brand-700 shadow-sm focus:ring-brand-500" name="remember">
            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
        </label>

        <x-primary-button class="w-full justify-center">Sign in</x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-sm text-gray-600">
            No account yet?
            <a href="{{ route('register') }}" class="font-medium text-brand-700 hover:underline">Create one</a>
        </p>
    @endif

    <p class="mt-3 text-sm"><a href="/" class="text-gray-500 hover:underline">&larr; Back to home</a></p>
</x-guest-layout>