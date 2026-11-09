<x-guest-layout>
    {{-- ================= QUICK LOGIN DEMO (PANEL PENGUJI) ================= --}}
    @if (config('demo.enabled'))
        <div class="mb-6 rounded-lg border border-dashed border-indigo-300 bg-indigo-50 p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-bold text-indigo-900">
                    <svg class="inline h-4 w-4 -mt-0.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Quick Login Demo
                </p>
                <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                    Panel Penguji
                </span>
            </div>

            <p class="mt-1 text-xs leading-relaxed text-indigo-700">
                Login instan tanpa mengetik kredensial. Setiap tombol mengirim
                <span class="font-semibold">POST /demo-login/{panel}</span> dengan whitelist akun pada
                <code class="rounded bg-white px-1">config/demo.php</code>.
            </p>

            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <form method="POST" action="{{ route('demo.login', 'admin') }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Login sebagai Admin
                    </button>
                </form>

                <form method="POST" action="{{ route('demo.login', 'staf') }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-md bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                        Login sebagai Staf
                    </button>
                </form>
            </div>

            <p class="mt-2 text-[11px] text-indigo-700">
                <span class="font-semibold">admin@its.ac.id</span> &middot;
                <span class="font-semibold">staf@its.ac.id</span> &middot; password: <code class="rounded bg-white px-1">password</code>
            </p>
        </div>
    @endif

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
