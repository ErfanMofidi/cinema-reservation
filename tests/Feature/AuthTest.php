<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('renders the login and registration screens for guests', function () {
    $this->get(route('login'))->assertOk()->assertSee('Sign in');
    $this->get(route('register'))->assertOk()->assertSee('Create account');
});

it('registers a customer and signs them in', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'New Customer',
        'email' => 'new.customer@example.test',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticated();

    $user = User::query()->where('email', 'new.customer@example.test')->firstOrFail();

    expect($user->name)->toBe('New Customer')
        ->and($user->isAdmin())->toBeFalse()
        ->and(Hash::check('SecurePass123', $user->password))->toBeTrue();
});

it('never allows role escalation through registration', function () {
    $this->post(route('register.store'), [
        'name' => 'Sneaky User',
        'email' => 'sneaky@example.test',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
        'role' => 'admin',
    ])->assertRedirect(route('home'));

    $user = User::query()->where('email', 'sneaky@example.test')->firstOrFail();

    expect($user->isAdmin())->toBeFalse();
});

it('restores the intended checkout URL after signing in', function () {
    $screening = makeScreening();
    $seatId = seatIdsFor($screening, ['A2'])[0];

    $checkoutUrl = route('checkout.show', [
        'screening' => $screening,
        'seats' => $seatId,
    ]);

    $this->get($checkoutUrl)->assertRedirect(route('login'));

    User::factory()->create([
        'email' => 'intended@example.test',
        'password' => 'Cinema123!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'intended@example.test',
        'password' => 'Cinema123!',
    ])->assertRedirect($checkoutUrl);
});

it('rate limits repeated failed login attempts', function () {
    User::factory()->create([
        'email' => 'limited@example.test',
        'password' => 'Cinema123!',
    ]);

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'limited@example.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $this->from(route('login'))->post(route('login.store'), [
        'email' => 'limited@example.test',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toContain('Too many login attempts');
    $this->assertGuest();
});

it('rejects registration with a duplicate email and weak password', function () {
    User::factory()->create(['email' => 'taken@example.test']);

    $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Duplicate',
        'email' => 'taken@example.test',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertRedirect(route('register'))
        ->assertSessionHasErrors(['email', 'password']);

    $this->assertGuest();
});

it('signs in an existing user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'viewer@example.test',
        'password' => 'Cinema123!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'viewer@example.test',
        'password' => 'Cinema123!',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials without signing in', function () {
    User::factory()->create([
        'email' => 'viewer@example.test',
        'password' => 'Cinema123!',
    ]);

    $this->from(route('login'))->post(route('login.store'), [
        'email' => 'viewer@example.test',
        'password' => 'wrong-password',
    ])->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs an authenticated user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest();
});

it('protects authenticated pages from guests', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with(['profile.edit', 'reservations.index']);

it('updates the authenticated user profile', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.test',
    ]);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'new@example.test',
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHas('success');

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.test');
});

it('updates the password only when the current password is correct', function () {
    $user = User::factory()->create(['password' => 'CurrentPass123']);

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'wrong-password',
        'password' => 'ReplacementPass123',
        'password_confirmation' => 'ReplacementPass123',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('CurrentPass123', $user->refresh()->password))->toBeTrue();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'CurrentPass123',
        'password' => 'ReplacementPass123',
        'password_confirmation' => 'ReplacementPass123',
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHas('success');

    expect(Hash::check('ReplacementPass123', $user->refresh()->password))->toBeTrue();
});

it('blocks customers from the admin area', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
});

it('allows administrators into the admin area', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertHeader('Cache-Control', 'no-store, private');
});

it('redirects authenticated users away from guest-only screens', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('login'))->assertRedirect();
    $this->actingAs($user)->get(route('register'))->assertRedirect();
});
