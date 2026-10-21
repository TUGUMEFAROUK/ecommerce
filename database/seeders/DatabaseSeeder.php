<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Seeding is a no-op outside local development so running the seeder can
     * never add or replace data in production.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $this->seedAccount('test@example.com', 'Test User');
        $this->seedAccount('admin@example.com', 'Admin User', UserRole::Admin);

        $this->call(DemoDataSeeder::class);
    }

    /**
     * Create a demo account when one with the given address does not exist yet.
     */
    private function seedAccount(string $email, string $name, UserRole $role = UserRole::Customer): void
    {
        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ]);
    }
}
