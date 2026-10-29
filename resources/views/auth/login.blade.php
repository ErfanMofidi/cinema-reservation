@extends('layouts.auth')

@section('title', __('auth.title.login'))
@section('meta_description', __('auth.meta_description.login'))

@section('content')
    <p class="eyebrow">{{ __('auth.eyebrow.login') }}</p>
    <h1 class="mt-3 font-display text-4xl leading-none text-ink sm:text-5xl">{{ __('auth.heading.login') }}</h1>
    <p class="mt-4 text-sm leading-6 text-muted">
        {{ __('auth.intro.login') }}
        {{ __('auth.form.new_here') }}
        <a href="{{ route('register') }}" class="font-medium text-gold underline-offset-4 hover:underline">{{ __('auth.form.create_account') }}</a>.
    </p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5" novalidate>
        @csrf

        <x-field name="email" label="{{ __('auth.labels.email') }}" :required="true">
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                inputmode="email"
                class="field-input"
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
        </x-field>

        <x-field name="password" label="{{ __('auth.labels.password') }}" :required="true">
            <div class="relative">
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="field-input pr-12"
                    data-password-input
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                >
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-muted transition hover:text-gold"
                    data-password-toggle
                    aria-label="Show password"
                    aria-pressed="false"
                >
                    <x-icon name="eye" class="h-4 w-4" data-password-icon-show />
                    <x-icon name="eye-off" class="hidden h-4 w-4" data-password-icon-hide />
                </button>
            </div>
        </x-field>

        <label class="flex items-center gap-2.5 text-sm text-muted">
            <input
                type="checkbox"
                name="remember"
                value="1"
                @checked(old('remember'))
                class="h-4 w-4 rounded border-line bg-night text-gold focus:ring-2 focus:ring-gold/30"
            >
            {{ __('auth.labels.remember') }}
        </label>

        <button type="submit" class="btn btn-primary w-full">
            {{ __('auth.buttons.sign_in') }}
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <aside class="mt-8 rounded-2xl border border-line bg-surface/70 p-5" aria-label="Demo accounts">
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-gold">
            <x-icon name="sparkle" class="h-4 w-4" />
            {{ __('auth.demo.title') }}
        </div>
        <p class="mt-2 text-xs leading-5 text-muted">
            {{ __('auth.demo.intro', ['password' => '<code class="rounded bg-night px-1.5 py-0.5 font-mono text-[11px] text-ink">Cinema123!</code>']) }}
        </p>
        <dl class="mt-4 space-y-2 text-xs">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-muted">{{ __('auth.demo.customer') }}</dt>
                <dd class="font-mono text-ink/80">{{ __('auth.demo.customer_email') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-muted">{{ __('auth.demo.admin') }}</dt>
                <dd class="font-mono text-ink/80">{{ __('auth.demo.admin_email') }}</dd>
            </div>
        </dl>
    </aside>
@endsection
