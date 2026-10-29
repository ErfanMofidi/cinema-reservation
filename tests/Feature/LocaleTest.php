<?php

use App\Models\User;
use Illuminate\Support\Facades\Session;

it('switches locale via the locale route', function () {
    $this->get(route('locale.switch', ['locale' => 'en']));
    expect(Session::get('locale'))->toBe('en');

    $this->get(route('locale.switch', ['locale' => 'fa']));
    expect(Session::get('locale'))->toBe('fa');
});

it('rejects invalid locale with 404', function () {
    $this->get(route('locale.switch', ['locale' => 'xx']))
        ->assertNotFound();
});

it('persists locale across requests in session', function () {
    $this->get(route('locale.switch', ['locale' => 'en']));

    $response = $this->get('/');
    $response->assertSee('Now showing');
});

it('renders admin in english by default in tests', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard');
});
