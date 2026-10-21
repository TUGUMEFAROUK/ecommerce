<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function productImagePngBytes(): string
{
    return base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    );
}

test('the first uploaded image becomes the primary image', function () {
    Storage::fake('public');

    $product = Product::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.products.images.store', $product), [
            'image' => UploadedFile::fake()->createWithContent('photo.png', productImagePngBytes()),
        ])
        ->assertRedirect();

    $image = ProductImage::sole();

    expect($image->product_id)->toBe($product->getKey())
        ->and($image->is_primary)->toBeTrue()
        ->and($image->position)->toBe(0)
        ->and(str_starts_with($image->path, 'products/'))->toBeTrue();

    Storage::disk('public')->assertExists($image->path);
});

test('subsequent images are appended with the next position and never primary', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $upload = fn () => $this->actingAs($admin)
        ->post(route('admin.products.images.store', $product), [
            'image' => UploadedFile::fake()->createWithContent('photo.png', productImagePngBytes()),
        ])
        ->assertRedirect();

    $upload();
    $upload();

    $images = $product->images()->orderBy('position')->get();

    expect($images)->toHaveCount(2)
        ->and($images[0]->is_primary)->toBeTrue()
        ->and($images[0]->position)->toBe(0)
        ->and($images[1]->is_primary)->toBeFalse()
        ->and($images[1]->position)->toBe(1);
});

test('an upload whose real content is not an allowed image type is rejected', function () {
    Storage::fake('public');

    $product = Product::factory()->create();

    $path = tempnam(sys_get_temp_dir(), 'upload');
    file_put_contents($path, 'this is not an image');

    $file = new UploadedFile($path, 'photo.png', 'image/png', null, true);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.products.images.store', $product), ['image' => $file])
        ->assertInvalid(['image']);

    $this->assertDatabaseCount('product_images', 0);
});

test('an oversized upload is rejected', function () {
    Storage::fake('public');

    $product = Product::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.products.images.store', $product), [
            'image' => UploadedFile::fake()->create('photo.jpg', 3000),
        ])
        ->assertInvalid(['image']);

    $this->assertDatabaseCount('product_images', 0);
});

test('replacing an image keeps its record and position and deletes the old file', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    $oldPath = UploadedFile::fake()->createWithContent('old.png', productImagePngBytes())
        ->store('products', 'public');

    $image = $product->images()->create([
        'path' => $oldPath,
        'position' => 3,
        'is_primary' => true,
    ]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.images.replace', $image), [
            'image' => UploadedFile::fake()->createWithContent('new.png', productImagePngBytes()),
        ])
        ->assertRedirect();

    $image->refresh();

    expect($image->position)->toBe(3)
        ->and($image->is_primary)->toBeTrue()
        ->and($image->path)->not->toBe($oldPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($image->path);
    $this->assertDatabaseCount('product_images', 1);
});

test('destroying an image deletes its file and promotes the next image to primary', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $primaryPath = UploadedFile::fake()->createWithContent('primary.png', productImagePngBytes())
        ->store('products', 'public');
    $nextPath = UploadedFile::fake()->createWithContent('next.png', productImagePngBytes())
        ->store('products', 'public');

    $primary = $product->images()->create(['path' => $primaryPath, 'position' => 0, 'is_primary' => true]);
    $next = $product->images()->create(['path' => $nextPath, 'position' => 1, 'is_primary' => false]);

    $this->actingAs($admin)
        ->delete(route('admin.images.destroy', $primary))
        ->assertRedirect();

    $this->assertDatabaseMissing('product_images', ['id' => $primary->getKey()]);
    Storage::disk('public')->assertMissing($primaryPath);
    Storage::disk('public')->assertExists($nextPath);

    expect($next->refresh()->is_primary)->toBeTrue();
});

test('customers are denied access to the product image routes', function (string $method, string $routeName) {
    Storage::fake('public');

    $product = Product::factory()->create();
    $path = UploadedFile::fake()->createWithContent('photo.png', productImagePngBytes())
        ->store('products', 'public');
    $image = $product->images()->create(['path' => $path, 'position' => 0, 'is_primary' => true]);
    $customer = User::factory()->create(['role' => 'customer']);

    $payload = $method === 'delete'
        ? []
        : ['image' => UploadedFile::fake()->createWithContent('photo.png', productImagePngBytes())];

    $this->actingAs($customer)
        ->call($method, route($routeName, $routeName === 'admin.products.images.store' ? [$product] : [$image]), $payload)
        ->assertForbidden();

    $this->assertDatabaseCount('product_images', 1);
    Storage::disk('public')->assertExists($path);
})->with([
    ['post', 'admin.products.images.store'],
    ['put', 'admin.images.replace'],
    ['delete', 'admin.images.destroy'],
]);

test('guests are redirected to login from the product image routes', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    $path = UploadedFile::fake()->createWithContent('photo.png', productImagePngBytes())
        ->store('products', 'public');
    $image = $product->images()->create(['path' => $path, 'position' => 0, 'is_primary' => true]);

    $this->post(route('admin.products.images.store', $product), [
        'image' => UploadedFile::fake()->createWithContent('photo.png', productImagePngBytes()),
    ])->assertRedirect(route('login'));

    $this->delete(route('admin.images.destroy', $image))->assertRedirect(route('login'));

    $this->assertDatabaseCount('product_images', 1);
    Storage::disk('public')->assertExists($path);
});
