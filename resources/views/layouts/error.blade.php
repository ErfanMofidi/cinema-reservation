<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#07070a">
    <title>@yield('title', 'Something went wrong') — {{ config('cinema.name') }}</title>
    <meta name="robots" content="noindex">
    @fonts
    @vite('resources/css/app.css')
</head>
<body class="flex min-h-screen flex-col">
    <header class="border-b border-line-soft">
        <div class="container-cinema flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-gold/40 bg-gold/5 text-gold">
                    <x-icon name="film" class="h-4 w-4" />
                </span>
                <span class="font-display text-xl tracking-wide text-ink">{{ config('cinema.name') }}</span>
            </a>
            <a href="{{ route('movies.index') }}" class="text-xs text-muted transition hover:text-gold">Browse films</a>
        </div>
    </header>

    <main class="container-cinema flex flex-1 items-center justify-center py-16">
        <div class="w-full max-w-xl text-center">
            <p class="font-display text-[7rem] leading-none text-gold/25 sm:text-[9rem]" aria-hidden="true">@yield('code', '?')</p>
            <p class="eyebrow -mt-4">@yield('eyebrow', 'A small interruption')</p>
            <h1 class="mt-4 font-display text-4xl leading-tight text-ink sm:text-5xl">@yield('heading', 'Something went wrong')</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-muted">@yield('description', 'Please go back and try again.')</p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-primary btn-sm">Back to the cinema</a>
                <a href="{{ route('movies.index') }}" class="btn btn-secondary btn-sm">Browse films</a>
            </div>

            @yield('extra')
        </div>
    </main>

    <footer class="border-t border-line-soft py-5">
        <p class="container-cinema text-center text-[11px] text-muted/60">
            {{ config('cinema.name') }} · Demo cinema reservation project
        </p>
    </footer>
</body>
</html>
