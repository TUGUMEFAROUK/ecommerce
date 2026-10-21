<?php

namespace Database\Seeders;

use App\Services\AdminUserService;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Creates the administrator account described by the environment.
 *
 * This seeder is deliberately not called by `DatabaseSeeder`: an
 * administrator is provisioned on purpose, with a password the operator
 * chose, instead of being left behind by a default fixture with a
 * well-known password.
 *
 * Configure it with ADMIN_EMAIL, ADMIN_NAME, and ADMIN_PASSWORD, then run
 * `php artisan db:seed --class=AdminUserSeeder`.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Seed the administrator account.
     */
    public function run(AdminUserService $admins): void
    {
        $email = (string) env('ADMIN_EMAIL', '');
        $name = (string) env('ADMIN_NAME', '');
        $password = (string) env('ADMIN_PASSWORD', '');

        if ($email === '' || $password === '') {
            throw new RuntimeException(
                'The administrator was not created. Set ADMIN_EMAIL and ADMIN_PASSWORD '
                .'in the environment, then run this seeder again.'
            );
        }

        $user = $admins->create($email, $name, $password);

        $this->command?->info(sprintf('Administrator ready: %s', $user->email));
    }
}
