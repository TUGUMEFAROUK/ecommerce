<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(3, true));

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    /**
     * Indicate that the category belongs to a parent category.
     */
    public function childOf(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->getKey(),
        ]);
    }
}
