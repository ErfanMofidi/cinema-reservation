<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#07070a">
    <title>@yield('title', config('cinema.name')) — {{ config('cinema.name') }}</title>
    <meta name="description" content="@yield('meta_description', config('cinema.tagline'))">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen">
    <a href="#main-content" class="sr-only z-50 rounded-full bg-gold px-4 py-2 text-sm font-semibold text-night focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
        Skip to content
    </a>

    <header class="sticky top-0 z-40 border-b border-line/70 bg-void/85 backdrop-blur-xl">
        <div class="container-cinema">
            <div class="flex h-16 items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 rounded-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold" aria-label="{{ config('cinema.name') }} home">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-gold/40 bg-gold/5 text-gold">
                        <x-icon name="film" class="h-4 w-4" />
                    </span>
                    <span class="leading-none">
                        <span class="block font-display text-[1.45rem] leading-none tracking-wide text-ink">{{ config('cinema.name') }}</span>
                        <span class="mt-1 block text-[8px] font-semibold uppercase tracking-[0.32em] text-muted">Ticket reservations</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 md:flex" aria-label="Main navigation">
                    <a href="{{ route('movies.index') }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('movies.index') && ! request()->filled('showing')])>All films</a>
                    <a href="{{ route('movies.index', ['showing' => 'now']) }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('movies.index') && request('showing') === 'now'])>Now showing</a>
                    <a href="{{ route('movies.index', ['showing' => 'coming']) }}" @class(['nav-link', 'nav-link-active' => request()->routeIs('movies.index') && request('showing') === 'coming'])>Coming soon</a>
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    @auth
                        <a href="{{ route('reservations.index') }}" class="nav-link">My tickets</a>

                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-link inline-flex items-center gap-1.5">
                                <x-icon name="dashboard" class="h-4 w-4" />
                                Admin
                            </a>
                        @endif

                        <details class="group relative">
                            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-line bg-surface px-2.5 py-1.5 text-sm text-ink transition hover:border-gold/40">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gold/15 text-[10px] font-semibold uppercase text-gold">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="max-w-24 truncate">{{ auth()->user()->name }}</span>
                                <x-icon name="chevron-down" class="h-3.5 w-3.5 text-muted transition group-open:rotate-180" />
                            </summary>
                            <div class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-line bg-surface shadow-2xl shadow-black/50">
                                <div class="border-b border-line-soft px-4 py-3">
                                    <p class="truncate text-sm font-medium text-ink">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="p-1.5">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-muted transition hover:bg-surface-2 hover:text-ink">
                                        <x-icon name="user" class="h-4 w-4" />
                                        Profile
                                    </a>
                                    <a href="{{ route('reservations.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-muted transition hover:bg-surface-2 hover:text-ink">
                                        <x-icon name="ticket" class="h-4 w-4" />
                                        My reservations
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-muted transition hover:bg-surface-2 hover:text-ink">
                                            <x-icon name="logout" class="h-4 w-4" />
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Sign in</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Join the list</a>
                    @endauth
                </div>

                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-line text-ink md:hidden"
                    aria-label="Open navigation menu"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                    data-mobile-menu-toggle
                >
                    <x-icon name="menu" class="h-5 w-5" data-menu-open-icon />
                    <x-icon name="close" class="hidden h-5 w-5" data-menu-close-icon />
                </button>
            </div>

            <div id="mobile-menu" class="hidden border-t border-line-soft py-4 md:hidden" data-mobile-menu>
                <nav class="flex flex-col gap-1" aria-label="Mobile navigation">
                    <a href="{{ route('movies.index') }}" class="rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface">All films</a>
                    <a href="{{ route('movies.index', ['showing' => 'now']) }}" class="rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface">Now showing</a>
                    <a href="{{ route('movies.index', ['showing' => 'coming']) }}" class="rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface">Coming soon</a>
                </nav>

                <div class="mt-4 border-t border-line-soft pt-4">
                    @auth
                        <div class="mb-3 px-3">
                            <p class="text-sm font-medium text-ink">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-muted">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('reservations.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface">
                            <x-icon name="ticket" class="h-4 w-4" /> My tickets
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface">
                            <x-icon name="user" class="h-4 w-4" /> Profile
                        </a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface">
                                <x-icon name="dashboard" class="h-4 w-4" /> Admin dashboard
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm text-muted hover:bg-surface hover:text-ink">
                                <x-icon name="logout" class="h-4 w-4" /> Sign out
                            </button>
                        </form>
                    @else
                        <div class="flex gap-2 px-1">
                            <a href="{{ route('login') }}" class="btn btn-secondary flex-1">Sign in</a>
                            <a href="{{ route('register') }}" class="btn btn-primary flex-1">Join</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main id="main-content">
        @if (session('status') || session('success') || session('error') || $errors->any())
            <div class="container-cinema pt-5" aria-live="polite">
                <div class="space-y-3">
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
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-20 border-t border-line-soft bg-night/60">
        <div class="container-cinema py-12">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr]">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/40 text-gold">
                            <x-icon name="film" class="h-4 w-4" />
                        </span>
                        <span class="font-display text-2xl text-ink">{{ config('cinema.name') }}</span>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-muted">{{ config('cinema.tagline') }}</p>
                    <p class="mt-4 inline-flex items-center gap-2 text-xs text-muted/70">
                        <x-icon name="location" class="h-3.5 w-3.5" />
                        A single-screen neighbourhood cinema experience.
                    </p>
                </div>

                <div>
                    <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">Discover</h2>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('movies.index', ['showing' => 'now']) }}" class="text-muted transition hover:text-gold">Now showing</a></li>
                        <li><a href="{{ route('movies.index', ['showing' => 'coming']) }}" class="text-muted transition hover:text-gold">Coming soon</a></li>
                        <li><a href="{{ route('movies.index') }}" class="text-muted transition hover:text-gold">All films</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">Your visit</h2>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @auth
                            <li><a href="{{ route('reservations.index') }}" class="text-muted transition hover:text-gold">My reservations</a></li>
                            <li><a href="{{ route('profile.edit') }}" class="text-muted transition hover:text-gold">Profile</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="text-muted transition hover:text-gold">Sign in</a></li>
                            <li><a href="{{ route('register') }}" class="text-muted transition hover:text-gold">Create an account</a></li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-line-soft pt-6 text-xs text-muted/70 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} {{ config('cinema.name') }}. A portfolio project.</p>
                <p class="inline-flex items-center gap-2">
                    <x-icon name="shield" class="h-3.5 w-3.5" />
                    Demo checkout — no payment is collected.
                </p>
            </div>
        </div>
    </footer>

    <dialog
        id="confirm-dialog"
        class="m-auto w-[min(92vw,27rem)] rounded-2xl border border-line bg-surface p-0 text-ink shadow-2xl shadow-black/60 backdrop:bg-void/80 backdrop:backdrop-blur-sm"
        aria-labelledby="confirm-dialog-title"
    >
        <form method="dialog" class="p-6">
            <div class="flex items-start gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-danger/30 bg-danger/10 text-danger">
                    <x-icon name="alert" class="h-5 w-5" />
                </span>
                <div>
                    <h2 id="confirm-dialog-title" class="font-display text-2xl text-ink" data-confirm-title>Please confirm</h2>
                    <p class="mt-2 text-sm leading-6 text-muted" data-confirm-message>This action cannot be undone.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button value="cancel" class="btn btn-secondary btn-sm">Keep it</button>
                <button value="confirm" class="btn btn-danger btn-sm" data-confirm-accept>Confirm</button>
            </div>
        </form>
    </dialog>

    @stack('scripts')
</body>
</html>
