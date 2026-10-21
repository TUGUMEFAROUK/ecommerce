<?php

use App\Models\User;
use App\Services\SettingsService;

function adminSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'store_name' => 'Gadget Kingdom',
        'store_currency' => 'USD',
        'contact_email' => 'hello@example.com',
        'contact_phone' => '555-0100',
        'shipping_flat_fee' => '4.99',
        'shipping_free_threshold' => '50.00',
        'low_stock_threshold' => '8',
    ], $overrides);
}

test('the settings page renders the current values', function () {
    app(SettingsService::class)->set(['store_name' => 'Current Store Name']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('Current Store Name', false)
        ->assertSee('USD', false);
});

test('an administrator can update the store settings and the cache is refreshed', function () {
    $settings = app(SettingsService::class);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'store_name' => 'Renamed Store',
            'store_currency' => 'eur',
            'low_stock_threshold' => '15',
        ]))
        ->assertRedirect(route('admin.settings.edit'));

    expect($settings->get('store_name'))->toBe('Renamed Store')
        ->and($settings->get('store_currency'))->toBe('EUR')
        ->and($settings->get('shipping_flat_fee'))->toBe('4.99')
        ->and($settings->get('shipping_free_threshold'))->toBe('50.00')
        ->and($settings->lowStockThreshold())->toBe(15);
});

test('settings validation rejects malformed input', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), adminSettingsPayload(['contact_email' => 'not-an-email']))
        ->assertInvalid(['contact_email']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), adminSettingsPayload(['store_currency' => 'XYZ']))
        ->assertInvalid(['store_currency']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), adminSettingsPayload(['shipping_flat_fee' => '-1.00']))
        ->assertInvalid(['shipping_flat_fee']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), adminSettingsPayload(['low_stock_threshold' => '-3']))
        ->assertInvalid(['low_stock_threshold']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [])
        ->assertInvalid([
            'store_name',
            'store_currency',
            'shipping_flat_fee',
            'shipping_free_threshold',
            'low_stock_threshold',
        ]);
});

test('an unknown currency code outside the iso allowlist is rejected', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['store_currency' => 'US1']))
        ->assertInvalid(['store_currency']);

    expect(app(SettingsService::class)->get('store_currency'))->toBe('USD');
});

test('customers are denied access to the settings routes', function (string $method, string $routeName) {
    $customer = User::factory()->create(['role' => 'customer']);

    $payload = $method === 'put' ? adminSettingsPayload() : [];

    $this->actingAs($customer)
        ->call($method, route($routeName), $payload)
        ->assertForbidden();
})->with([
    ['get', 'admin.settings.edit'],
    ['put', 'admin.settings.update'],
]);

test('guests are redirected to login from the settings routes', function () {
    $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
    $this->put(route('admin.settings.update'), adminSettingsPayload())
        ->assertRedirect(route('login'));

    expect(app(SettingsService::class)->get('store_name'))->not->toBe('Gadget Kingdom');
});
