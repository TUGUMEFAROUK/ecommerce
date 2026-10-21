<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'admin'])
        ->name('admin.only-example')
        ->get('/admin-only-example', fn () => response('ok'));
});

test('new users default to the customer role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->isAdmin())->toBeFalse();
});

test('the customer role is denied access to admin routes', function () {
    $user = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($user)->get(route('admin.only-example'))->assertForbidden();
});

test('the admin role is granted access to admin routes', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user)->get(route('admin.only-example'))->assertOk();
});

test('the admin dashboard requires authentication', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('the admin dashboard denies customers', function () {
    $user = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

test('the admin dashboard is shown to administrators', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Admin dashboard');
});
