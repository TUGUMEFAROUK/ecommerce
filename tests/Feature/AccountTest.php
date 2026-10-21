<?php

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('the account dashboard requires authentication', function () {
    $this->get(route('account.show'))->assertRedirect(route('login'));
});

test('the dashboard shows the customer profile and their own orders only', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    $other = User::factory()->create();

    $myOrder = Order::factory()->for($user)->create();
    $theirOrder = Order::factory()->for($other)->create();

    $this->actingAs($user)
        ->get(route('account.show'))
        ->assertOk()
        ->assertSee('Jane Doe')
        ->assertSee($user->email)
        ->assertSee(route('account.orders.show', $myOrder), false)
        ->assertDontSee(route('account.orders.show', $theirOrder), false);
});

test('a customer can view their own order details', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    OrderItem::factory()->for($order)->create(['product_name' => 'Aurora X2 Smartphone']);
    Payment::factory()->succeeded()->for($order)->create();

    $this->actingAs($user)
        ->get(route('account.orders.show', $order))
        ->assertOk()
        ->assertSee('Aurora X2 Smartphone')
        ->assertSee($order->payments->first()->provider_reference);
});

test('another customer cannot view someone elses order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();

    $this->actingAs($user)
        ->get(route('account.orders.show', $order))
        ->assertNotFound();
});

test('an admin can view any customers order details', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = Order::factory()->create();

    $this->actingAs($admin)
        ->get(route('account.orders.show', $order))
        ->assertOk();
});

test('a customer can update their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => 'Jane Updated',
            'email' => 'jane.updated@example.com',
        ])
        ->assertRedirect(route('account.show'));

    expect($user->fresh()->name)->toBe('Jane Updated')
        ->and($user->fresh()->email)->toBe('jane.updated@example.com');
});

test('profile update rejects an email used by another account', function () {
    $user = User::factory()->create();
    User::factory()->create(['email' => 'other@example.com']);

    $response = $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => 'Jane Doe',
            'email' => 'other@example.com',
        ]);

    $response->assertInvalid(['email' => 'The email has already been taken.']);

    expect($user->fresh()->email)->not->toBe('other@example.com');
});

test('a customer can keep their own email during a profile update', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => 'Jane Doe',
            'email' => $user->email,
        ])
        ->assertRedirect(route('account.show'));

    expect($user->fresh()->email)->toBe($user->email);
});

test('changing the password requires the current password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('account.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ]);

    $response->assertInvalid(['current_password' => 'The password is incorrect.']);

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeFalse();
});

test('a customer can change their password with the correct current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
        ->assertRedirect(route('account.show'));

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

test('the account update routes require authentication', function () {
    $this->patch(route('account.update'), ['name' => 'X', 'email' => 'x@example.com'])
        ->assertRedirect(route('login'));

    $this->patch(route('account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('login'));
});

test('order details require authentication', function () {
    $order = Order::factory()->create();

    $this->get(route('account.orders.show', $order))->assertRedirect(route('login'));
});
