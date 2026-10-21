<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

function adminCategoryPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Electronics',
        'slug' => 'electronics',
    ], $overrides);
}

test('the category index lists categories with their product and child counts', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);
    Product::factory()->create(['category_id' => $category->getKey()]);
    Category::factory()->childOf($category)->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Electronics', false);
});

test('an administrator can create a category', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.categories.store'), adminCategoryPayload())
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
        'slug' => 'electronics',
        'parent_id' => null,
    ]);
});

test('an administrator can nest a category under an existing parent', function () {
    $parent = Category::factory()->create(['name' => 'Parent', 'slug' => 'parent']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.categories.store'), adminCategoryPayload(['parent_id' => $parent->getKey()]))
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'slug' => 'electronics',
        'parent_id' => $parent->getKey(),
    ]);
});

test('category creation validates required fields, slug format, and duplicates', function () {
    Category::factory()->create(['slug' => 'taken-slug']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [])
        ->assertInvalid(['name', 'slug']);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), adminCategoryPayload(['slug' => 'Not A Slug']))
        ->assertInvalid(['slug' => 'The slug field format is invalid.']);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), adminCategoryPayload(['slug' => 'taken-slug']))
        ->assertInvalid(['slug' => 'The slug has already been taken.']);

    $this->assertDatabaseCount('categories', 1);
});

test('the parent category must exist', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.categories.store'), adminCategoryPayload(['parent_id' => 999]))
        ->assertInvalid(['parent_id' => 'The selected parent id is invalid.']);
});

test('a category cannot be its own parent', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.categories.update', $category), adminCategoryPayload([
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->getKey(),
        ]))
        ->assertInvalid(['parent_id']);

    expect($category->refresh()->parent_id)->toBeNull();
});

test('a category cannot become a descendant of itself through an indirect cycle', function () {
    $root = Category::factory()->create(['name' => 'Root', 'slug' => 'root']);
    $child = Category::factory()->childOf($root)->create();
    $grandchild = Category::factory()->childOf($child)->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.categories.update', $root), adminCategoryPayload([
            'name' => $root->name,
            'slug' => $root->slug,
            'parent_id' => $grandchild->getKey(),
        ]))
        ->assertInvalid([
            'parent_id' => 'A category cannot be placed under one of its own subcategories.',
        ]);

    expect($root->refresh()->parent_id)->toBeNull();
});

test('updating a category changes its fields and parent', function () {
    $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);
    $parent = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.categories.update', $category), adminCategoryPayload([
            'name' => 'New Name',
            'slug' => 'new-name',
            'parent_id' => $parent->getKey(),
        ]))
        ->assertRedirect(route('admin.categories.index'));

    $category->refresh();

    expect($category->name)->toBe('New Name')
        ->and($category->slug)->toBe('new-name')
        ->and($category->parent_id)->toBe($parent->getKey());
});

test('a category with products cannot be deleted', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->getKey()]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->delete(route('admin.categories.destroy', $category))
        ->assertSessionHasErrors([
            'category' => 'This category still has products. Move or delete them first.',
        ]);

    $this->assertDatabaseHas('categories', ['id' => $category->getKey()]);
});

test('an empty category can be deleted', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $category->getKey()]);
});

test('deleting a parent category detaches its child categories', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->childOf($parent)->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->delete(route('admin.categories.destroy', $parent))
        ->assertRedirect(route('admin.categories.index'));

    expect($child->refresh()->parent_id)->toBeNull();
});

test('customers are denied access to every category management route', function (string $method, string $routeName) {
    $category = Category::factory()->create();
    $customer = User::factory()->create(['role' => 'customer']);

    $payload = $method === 'get' ? [] : adminCategoryPayload();
    $parameters = in_array($routeName, ['admin.categories.index', 'admin.categories.create', 'admin.categories.store'], true)
        ? []
        : [$category];

    $this->actingAs($customer)
        ->call($method, route($routeName, $parameters), $payload)
        ->assertForbidden();
})->with([
    ['get', 'admin.categories.index'],
    ['get', 'admin.categories.create'],
    ['post', 'admin.categories.store'],
    ['get', 'admin.categories.edit'],
    ['put', 'admin.categories.update'],
    ['delete', 'admin.categories.destroy'],
]);

test('guests are redirected to login from the category routes', function () {
    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
    $this->post(route('admin.categories.store'), adminCategoryPayload())->assertRedirect(route('login'));

    $this->assertDatabaseCount('categories', 0);
});
