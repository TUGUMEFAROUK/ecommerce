<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds the local development dataset.
 *
 * Seeding is a no-op outside local development so running the seeder can
 * never add or replace data in production.
 *
 * No administrator account is created here. A default admin with a
 * well-known password is a liability, so an administrator is provisioned
 * deliberately with `AdminUserSeeder` or `php artisan store:admin`, using
 * a password the operator chose.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $this->seedAccount('test@example.com', 'Test User');

        $this->call(DemoDataSeeder::class);
    }

    /**
     * Create a demo account when one with the given address does not exist yet.
     */
    private function seedAccount(string $email, string $name): void
    {
        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);
    }
}
