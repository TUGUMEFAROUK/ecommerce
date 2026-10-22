<?php

use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;
use App\Support\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

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

/**
 * Build a PNG of the requested dimensions without needing an image extension.
 *
 * The IHDR chunk is patched, so the header reports the wanted size while the
 * file stays a valid PNG for the upload validators.
 */
function fakeImageBytes(int $width, int $height): string
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

    return substr($png, 0, 16).pack('N', $width).pack('N', $height).substr($png, 24);
}

function fakeImage(string $name, int $width, int $height): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, fakeImageBytes($width, $height));
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

test('an administrator can upload a company logo and it appears in the storefront', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'brand_display' => 'logo',
            'store_logo_upload' => fakeImage('logo.png', 400, 120),
        ]))
        ->assertRedirect(route('admin.settings.edit'));

    $path = app(SettingsService::class)->get('store_logo');

    expect($path)->not->toBe('');
    expect(app(SettingsService::class)->brandDisplay())->toBe('logo');

    Storage::disk('public')->assertExists($path);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('home'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($path), false);
});

test('the logo can be removed and the storefront falls back to the store initial', function () {
    Storage::fake('public');

    $settings = app(SettingsService::class);

    $settings->set(['store_name' => 'Gadget Kingdom']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'brand_display' => 'logo',
            'store_logo_upload' => fakeImage('logo.png', 400, 120),
        ]));

    $path = $settings->get('store_logo');

    expect($settings->brandDisplay())->toBe('logo');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['store_logo_remove' => '1']))
        ->assertRedirect(route('admin.settings.edit'));

    expect($settings->get('store_logo'))->toBe('');
    expect($settings->brandDisplay())->toBe('name');

    Storage::disk('public')->assertMissing($path);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Gadget Kingdom')
        ->assertDontSee(Storage::disk('public')->url($path), false);
});

test('the logo and the store name are never shown together', function () {
    Storage::fake('public');

    app(SettingsService::class)->set(['store_name' => 'Gadget Kingdom']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'brand_display' => 'logo',
            'store_logo_upload' => fakeImage('logo.png', 400, 120),
        ]))
        ->assertRedirect(route('admin.settings.edit'));

    $logoUrl = Storage::disk('public')->url(app(SettingsService::class)->get('store_logo'));

    $logoBrand = Blade::render('<x-store-brand />');

    expect($logoBrand)->toContain($logoUrl)
        ->and($logoBrand)->not->toContain('>Gadget Kingdom<');

    app(SettingsService::class)->set(['brand_display' => 'name']);

    $nameBrand = Blade::render('<x-store-brand />');

    expect($nameBrand)->toContain('>Gadget Kingdom<')
        ->and($nameBrand)->not->toContain($logoUrl);
});

test('choosing the logo before a file is uploaded keeps showing the store name', function () {
    app(SettingsService::class)->set([
        'store_name' => 'Gadget Kingdom',
        'brand_display' => 'logo',
    ]);

    expect(app(SettingsService::class)->usesLogo())->toBeFalse();

    expect(Blade::render('<x-store-brand />'))->toContain('Gadget Kingdom');
});

test('the settings form offers both branding choices and stores the selected one', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('name="brand_display"', false)
        ->assertSee('value="logo"', false)
        ->assertSee('value="name"', false);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['brand_display' => 'logo']))
        ->assertRedirect(route('admin.settings.edit'));

    expect(app(SettingsService::class)->brandDisplay())->toBe('logo');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['brand_display' => 'name']))
        ->assertRedirect(route('admin.settings.edit'));

    expect(app(SettingsService::class)->brandDisplay())->toBe('name');
});

test('the logo size can be changed and is applied to the rendered logo', function () {
    Storage::fake('public');

    app(SettingsService::class)->set(['store_name' => 'Gadget Kingdom']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'brand_display' => 'logo',
            'store_logo_height' => '48',
            'store_logo_upload' => fakeImage('logo.png', 400, 120),
        ]))
        ->assertRedirect(route('admin.settings.edit'));

    expect(app(SettingsService::class)->logoHeight())->toBe(48);

    expect(Blade::render('<x-store-brand />'))->toContain('height: 48px');
});

test('the logo size is kept inside the allowed range', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['store_logo_height' => '400']))
        ->assertSessionHasErrors('store_logo_height');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['store_logo_height' => '4']))
        ->assertSessionHasErrors('store_logo_height');

    expect(app(SettingsService::class)->logoHeight())
        ->toBeGreaterThanOrEqual(SettingsService::MIN_LOGO_HEIGHT)
        ->toBeLessThanOrEqual(SettingsService::MAX_LOGO_HEIGHT);
});

test('an oversized logo upload is rejected', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'store_logo_upload' => fakeImage('logo.png', 3000, 2000),
        ]))
        ->assertSessionHasErrors('store_logo_upload');

    expect(app(SettingsService::class)->get('store_logo'))->toBe('');
});

test('the journal image can be uploaded and is shown on the homepage', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'newsletter_image_upload' => fakeImage('journal.jpg', 1200, 900),
        ]))
        ->assertRedirect(route('admin.settings.edit'));

    $path = app(SettingsService::class)->get('newsletter_image');

    expect($path)->not->toBe('');

    Storage::disk('public')->assertExists($path);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('home'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($path), false);
});

test('the journal image can be removed', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'newsletter_image_upload' => fakeImage('journal.jpg', 1200, 900),
        ]));

    $path = app(SettingsService::class)->get('newsletter_image');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['newsletter_image_remove' => '1']))
        ->assertRedirect(route('admin.settings.edit'));

    expect(app(SettingsService::class)->get('newsletter_image'))->toBe('');

    Storage::disk('public')->assertMissing($path);
});

test('journal images outside the allowed size are rejected', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'newsletter_image_upload' => fakeImage('journal.jpg', 100, 80),
        ]))
        ->assertSessionHasErrors('newsletter_image_upload');

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload([
            'newsletter_image_upload' => fakeImage('journal.jpg', 3000, 3000),
        ]))
        ->assertSessionHasErrors('newsletter_image_upload');

    expect(app(SettingsService::class)->get('newsletter_image'))->toBe('');
});

test('the settings form offers the Ugandan shilling and stores it', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('Ugandan Shilling (UGX)', false);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.settings.update'), adminSettingsPayload(['store_currency' => 'ugx']))
        ->assertRedirect(route('admin.settings.edit'));

    expect(app(SettingsService::class)->get('store_currency'))->toBe('UGX');
});

test('prices are shown in shillings without decimals once the store prices in UGX', function () {
    app(SettingsService::class)->set(['store_currency' => 'UGX']);

    Product::factory()->create(['name' => 'Handwoven Throw', 'price' => 12000]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('UGX 12,000', false);

    expect(Money::format(12000))->toBe('UGX 12,000')
        ->and(Money::format(12000.5))->toBe('UGX 12,001')
        ->and(Money::format(12000, 'USD'))->toBe('$12,000.00');
});
