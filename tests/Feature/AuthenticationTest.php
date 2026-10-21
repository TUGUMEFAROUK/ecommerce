<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('the registration page renders', function () {
    $this->get(route('register'))->assertOk();
});

test('registration creates a customer account and logs the user in', function () {
    $response = $this->post(route('register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('account.show'));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->name)->toBe('Jane Doe')
        ->and($user->role)->toBe(UserRole::Customer)
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

test('registration rejects an email that is already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Jane Doe',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertInvalid(['email' => 'The email has already been taken.']);

    $this->assertGuest();

    expect(User::query()->where('email', 'taken@example.com')->count())->toBe(1);
});

test('registration rejects a weak password', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertInvalid('password');

    $this->assertGuest();
});

test('the login page renders', function () {
    $this->get(route('login'))->assertOk();
});

test('valid credentials log the user in', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('account.show'));

    $this->assertAuthenticatedAs($user);
});

test('invalid credentials are rejected', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect();
    $response->assertInvalid(['email' => 'These credentials do not match our records.']);

    $this->assertGuest();
});

test('guests hitting a protected page are redirected to login and then returned after login', function () {
    $user = User::factory()->create();

    $this->get(route('account.show'))->assertRedirect(route('login'));

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/account');
});

test('authenticated users are redirected away from the login page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('login'))->assertRedirect('/account');
});

test('logout invalidates the session and ends authentication', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $this->get(route('account.show'))->assertRedirect(route('login'));
});

test('repeated failed logins are rate limited', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertInvalid();
    }

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

test('the forgot password page renders', function () {
    $this->get(route('password.request'))->assertOk();
});

test('submitting a known email sends a reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('submitting an unknown email reports that no account exists', function () {
    Notification::fake();

    $response = $this->post(route('password.email'), ['email' => 'missing@example.com']);

    $response->assertRedirect();
    $response->assertInvalid(['email' => "We can't find a user with that email address."]);

    Notification::assertNothingSent();
});

test('the reset password page renders', function () {
    $this->get(route('password.reset', ['token' => 'fake-token', 'email' => 'jane@example.com']))
        ->assertOk()
        ->assertSee('jane@example.com', false);
});

test('a valid token resets the password', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertRedirect(route('account.show'));

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

test('an invalid token is rejected', function () {
    $user = User::factory()->create();

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertInvalid(['email' => 'This password reset token is invalid.']);
});
