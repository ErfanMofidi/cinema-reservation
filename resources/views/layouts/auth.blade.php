<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == "fa" ? "rtl" : "ltr" }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#07070a">
    <title>@yield('title', 'Account') — {{ config('cinema.name') }}</title>
    <meta name="description" content="@yield('meta_description', config('cinema.tagline'))">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative hidden overflow-hidden border-r border-line lg:flex lg:flex-col lg:justify-between">
            <img src="{{ asset('images/backdrops/orbital.svg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-70" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-t from-void via-void/40 to-void/20"></div>
            <div class="relative z-10 p-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/40 bg-void/50 text-gold">
                        <x-icon name="film" class="h-4 w-4" />
                    </span>
                    <span class="font-display text-2xl text-ink">{{ config('cinema.name') }}</span>
                </a>
            </div>
            <div class="relative z-10 max-w-lg p-10 pb-14">
                <p class="eyebrow">{{ __('home.welcome.welcome') }}</p>
                <p class="mt-4 font-display text-4xl leading-[1.1] text-ink xl:text-5xl">{{ __('home.welcome.welcome') }}<br>{{ __('auth.heading.world_opens') }}</p>
                <p class="mt-5 text-sm leading-6 text-ink/60">{{ config('cinema.tagline') }} {{ __('home.plan_visit.subtitle') }}.</p>
                <p class="mt-10 inline-flex items-center gap-2 text-xs text-ink/40">
                    <x-icon name="shield" class="h-3.5 w-3.5" />
                    {{ __('messages.footer.demo_checkout') }}
                </p>
            </div>
        </section>

        <section class="flex items-center justify-center px-4 py-12 sm:px-8">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-10 inline-flex items-center gap-3 lg:hidden">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-gold/40 text-gold">
                        <x-icon name="film" class="h-4 w-4" />
                    </span>
                    <span class="font-display text-2xl text-ink">{{ config('cinema.name') }}</span>
                </a>

                @if (session('status') || session('success') || session('error') || $errors->any())
                    <div class="mb-6 space-y-3" aria-live="polite">
                        @if (session('status'))
                            <x-alert type="info">{{ session('status') }}</x-alert>
                        @endif
                        @if (session('success'))
                            <x-alert type="success">{{ session('success') }}</x-alert>
                        @endif
                        @if (session('error'))
                            <x-alert type="error">{{ session('error') }}</x-alert>
                        @endif
                        @if ($errors->any())
                            <x-alert type="error">
                                <p class="font-medium">{{ __('messages.component.review_fields') }}</p>
                                <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs text-danger/90">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-alert>
                        @endif
                    </div>
                @endif

                @yield('content')

                <p class="mt-10 text-center text-xs text-muted/60">
                    <a href="{{ route('home') }}" class="transition hover:text-gold">{{ __('messages.nav.back_to_cinema') }}</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
