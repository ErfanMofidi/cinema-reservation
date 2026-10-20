@extends('layouts.auth')

@section('title', 'Create an account')
@section('meta_description', 'Create an account to reserve seats and keep your cinema tickets together.')

@section('content')
    <p class="eyebrow">Join the list</p>
    <h1 class="mt-3 font-display text-4xl leading-none text-ink sm:text-5xl">Create your account</h1>
    <p class="mt-4 text-sm leading-6 text-muted">
        Save your reservations, revisit past screenings, and cancel online when plans change.
        Already a member?
        <a href="{{ route('login') }}" class="font-medium text-gold underline-offset-4 hover:underline">Sign in</a>.
    </p>

    <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5" novalidate>
        @csrf

        <x-field name="name" label="Full name" :required="true">
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="field-input"
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
            >
        </x-field>

        <x-field name="email" label="Email address" :required="true">
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                inputmode="email"
                class="field-input"
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
        </x-field>

        <x-field name="password" label="Password" :required="true" hint="At least 8 characters, including a letter and a number.">
            <div class="relative">
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
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

        <x-field name="password_confirmation" label="Confirm password" :required="true">
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="field-input"
                @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror
            >
        </x-field>

        <p class="text-xs leading-5 text-muted/80">
            By creating an account you agree that this is a portfolio demo and that no real payment is collected.
        </p>

        <button type="submit" class="btn btn-primary w-full">
            Create account
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>
@endsection
