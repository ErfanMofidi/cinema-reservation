@extends('layouts.app')

@section('title', 'Your profile')

@section('content')
    <div class="container-cinema py-10 sm:py-14">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Account</p>
                <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Your profile</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-muted">
                    Keep your contact details current and update the password used to protect your reservations.
                </p>
            </div>
            <a href="{{ route('reservations.index') }}" class="btn btn-secondary btn-sm w-fit">
                <x-icon name="ticket" class="h-4 w-4" />
                My reservations
            </a>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="card p-6 sm:p-8" aria-labelledby="profile-details-heading">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="profile-details-heading" class="font-display text-2xl text-ink">Profile details</h2>
                        <p class="mt-1 text-xs text-muted">Used on your booking confirmations and account menu.</p>
                    </div>
                    <span class="badge badge-gold">{{ $user->role->label() }}</span>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5" novalidate>
                    @csrf
                    @method('PATCH')

                    <x-field name="name" label="Full name" :required="true">
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            required
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
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="email"
                            class="field-input"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        >
                    </x-field>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm">Save details</button>
                    </div>
                </form>
            </section>

            <section class="card p-6 sm:p-8" aria-labelledby="password-heading">
                <h2 id="password-heading" class="font-display text-2xl text-ink">Password</h2>
                <p class="mt-1 text-xs text-muted">
                    Use at least 8 characters with a letter and a number.
                </p>

                <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-5" novalidate>
                    @csrf
                    @method('PUT')

                    <x-field name="current_password" label="Current password" :required="true">
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="field-input"
                            @error('current_password') aria-invalid="true" aria-describedby="current_password-error" @enderror
                        >
                    </x-field>

                    <x-field name="password" label="New password" :required="true">
                        <div class="relative">
                            <input
                                id="new_password"
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

                    <x-field name="password_confirmation" label="Confirm new password" :required="true">
                        <input
                            id="new_password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="field-input"
                            @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror
                        >
                    </x-field>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-secondary btn-sm">Update password</button>
                    </div>
                </form>
            </section>
        </div>

        <p class="mt-6 text-xs text-muted">
            Signed in as <span class="text-ink">{{ $user->email }}</span>. Need to leave?
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="font-medium text-gold underline-offset-4 hover:underline">Sign out</button>
            </form>
        </p>
    </div>
@endsection
