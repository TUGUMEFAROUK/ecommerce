<?php

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

test('only the order owner and administrators can view an order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create(['role' => UserRole::Customer]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $order = Order::factory()->for($owner)->create();

    expect($owner->can('view', $order))->toBeTrue()
        ->and($admin->can('view', $order))->toBeTrue()
        ->and($other->can('view', $order))->toBeFalse();
});

test('the policy reports other customers orders as not found to prevent enumeration', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $order = Order::factory()->for($owner)->create();

    expect($other->can('view', $order))->toBeFalse()
        ->and($other->cannot('view', $order))->toBeTrue();
});

test('only administrators can change an order status', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $order = Order::factory()->for($owner)->create();

    expect($admin->can('update', $order))->toBeTrue()
        ->and($owner->can('update', $order))->toBeFalse()
        ->and($other->can('update', $order))->toBeFalse();
});

test('the update denial is an outright refusal while the owner can still view the order', function () {
    $owner = User::factory()->create();

    $order = Order::factory()->for($owner)->create();

    expect($owner->cannot('update', $order))->toBeTrue()
        ->and($owner->can('view', $order))->toBeTrue();
});

test('the view denial for a foreign order carries a not-found status', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $order = Order::factory()->for($owner)->create();

    try {
        Gate::forUser($other)->authorize('view', $order);
        $this->fail('Expected the view authorization to be denied.');
    } catch (AuthorizationException $exception) {
        expect($exception->status())->toBe(404);
    }
});
