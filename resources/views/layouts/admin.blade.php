@php
    $adminNav = [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ['route' => 'admin.movies.index', 'active' => 'admin.movies.*', 'label' => 'Films', 'icon' => 'film'],
        ['route' => 'admin.genres.index', 'active' => 'admin.genres.*', 'label' => 'Genres', 'icon' => 'sparkle'],
        ['route' => 'admin.auditoriums.index', 'active' => 'admin.auditoriums.*', 'label' => 'Auditoriums', 'icon' => 'building'],
        ['route' => 'admin.screenings.index', 'active' => 'admin.screenings.*', 'label' => 'Screenings', 'icon' => 'calendar'],
        ['route' => 'admin.reservations.index', 'active' => 'admin.reservations.*', 'label' => 'Reservations', 'icon' => 'ticket'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#07070a">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — {{ config('cinema.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen">
    <a href="#admin-main" class="sr-only z-50 rounded-full bg-gold px-4 py-2 text-sm font-semibold text-night focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
        Skip to admin content
    </a>

    <div class="min-h-screen lg:grid lg:grid-cols-[16rem_1fr]">
        <aside class="hidden border-r border-line-soft bg-night/70 lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:justify-between">
            <div class="p-5">
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-gold/40 bg-gold/5 text-gold">
                        <x-icon name="film" class="h-4 w-4" />
                    </span>
                    <span class="leading-none">
                        <span class="block font-display text-xl tracking-wide text-ink">{{ config('cinema.name') }}</span>
                        <span class="mt-1 block text-[8px] font-semibold uppercase tracking-[0.3em] text-muted">Admin console</span>
                    </span>
                </a>

                <nav class="mt-8 space-y-1" aria-label="Admin navigation">
                    @foreach ($adminNav as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class(['admin-nav-link', 'admin-nav-link-active' => request()->routeIs($item['active'])])
                            @if (request()->routeIs($item['active'])) aria-current="page" @endif
                        >
                            <x-icon :name="$item['icon']" class="h-4 w-4" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="border-t border-line-soft p-5">
                <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-lg px-2 py-2 text-xs text-muted transition hover:text-gold">
                    <x-icon name="external" class="h-3.5 w-3.5" />
                    View public site
                </a>
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-line bg-surface px-3 py-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gold/15 text-[10px] font-semibold uppercase text-gold">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-ink">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[10px] text-muted">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs text-muted transition hover:text-gold">
                        <x-icon name="logout" class="h-3.5 w-3.5" />
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 border-b border-line-soft bg-void/90 backdrop-blur-xl">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 lg:hidden">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border border-gold/40 text-gold">
                                <x-icon name="film" class="h-3.5 w-3.5" />
                            </span>
                            <span class="font-display text-lg text-ink">Admin</span>
                        </a>
                        <span class="hidden text-xs text-muted lg:block">Cinema operations</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('home') }}" class="hidden items-center gap-1.5 rounded-full border border-line px-3 py-1.5 text-[11px] text-muted transition hover:border-gold/40 hover:text-gold sm:inline-flex">
                            <x-icon name="external" class="h-3 w-3" />
                            Public site
                        </a>

                        <details class="relative lg:hidden">
                            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-line bg-surface px-3 py-1.5 text-xs text-ink">
                                <x-icon name="menu" class="h-4 w-4" />
                                Menu
                            </summary>
                            <div class="absolute right-0 z-50 mt-2 w-60 rounded-2xl border border-line bg-surface p-2 shadow-2xl shadow-black/60">
                                <nav class="space-y-1" aria-label="Mobile admin navigation">
                                    @foreach ($adminNav as $item)
                                        <a
                                            href="{{ route($item['route']) }}"
                                            class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm {{ request()->routeIs($item['active']) ? 'bg-gold/10 text-gold' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"
                                        >
                                            <x-icon :name="$item['icon']" class="h-4 w-4" />
                                            {{ $item['label'] }}
                                        </a>
                                    @endforeach
                                </nav>
                                <div class="mt-2 border-t border-line-soft pt-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm text-muted hover:bg-surface-2 hover:text-ink">
                                            <x-icon name="logout" class="h-4 w-4" />
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </header>

            <main id="admin-main" class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('status') || session('success') || session('error') || $errors->any())
                    <div class="mb-6 space-y-3" aria-live="polite">
                        @if (session('status'))
                            <x-alert type="info" dismissible>{{ session('status') }}</x-alert>
                        @endif
                        @if (session('success'))
                            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
                        @endif
                        @if (session('error'))
                            <x-alert type="error" dismissible>{{ session('error') }}</x-alert>
                        @endif
                        @if ($errors->any())
                            <x-alert type="error">
                                <p class="font-medium">Please review the highlighted fields.</p>
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
            </main>

            <footer class="border-t border-line-soft px-6 py-5 text-[11px] text-muted/60 lg:px-8">
                {{ config('cinema.name') }} admin · Demo data only · No payments are processed.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
