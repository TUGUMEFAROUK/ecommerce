<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Creates and promotes administrator accounts.
 *
 * This is the only supported way to grant administrator rights. It is
 * never reachable over HTTP: the seeder that uses it is run from the
 * command line, and the Artisan command that wraps it is CLI-only.
 *
 * Passwords are never accepted from a default, a fixture, or a seeded
 * constant. They must be supplied by the operator and must be strong
 * enough to survive a credential-stuffing attempt.
 */
class AdminUserService
{
    /**
     * The shortest password an administrator may be given.
     */
    public const MINIMUM_PASSWORD_LENGTH = 12;

    /**
     * Passwords that are long enough to pass the length rule but are
     * still the first thing an attacker tries.
     *
     * @var list<string>
     */
    private const COMMON_PASSWORDS = [
        'password',
        'password1',
        'password123',
        'passw0rd',
        'administrator',
        'admin',
        'admin123',
        'admin1234',
        'administrator1',
        'secret',
        'secret123',
        'changeme',
        'letmein',
        'welcome',
        'welcome1',
        'qwertyuiop',
        '12345678',
        '123456789',
        '1234567890',
        'iloveyou',
    ];

    /**
     * Create an administrator, or promote an existing account.
     *
     * An existing account with the same address is promoted rather than
     * duplicated, so an administrator can be made from a customer account
     * that already exists.
     */
    public function create(string $email, string $name, string $password): User
    {
        $email = Str::lower(trim($email));

        $this->assertEmailAcceptable($email);
        $this->assertPasswordAcceptable($password);

        $name = trim($name) !== '' ? trim($name) : 'Administrator';

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            // forceCreate is used because the role is not a fillable
            // attribute: an administrator's role is never taken from
            // user input, only from this service.
            return User::query()->forceCreate([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => UserRole::Admin,
            ]);
        }

        $user->forceFill([
            'name' => $name,
            'password' => $password,
            'role' => UserRole::Admin,
        ])->save();

        return $user;
    }

    /**
     * Reject an email address that cannot be used for an account.
     *
     * @throws ValidationException
     */
    public function assertEmailAcceptable(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => 'A valid email address is required.',
            ]);
        }
    }

    /**
     * Reject a password that is too weak to protect an administrator.
     *
     * @throws ValidationException
     */
    public function assertPasswordAcceptable(string $password): void
    {
        if (strlen($password) < self::MINIMUM_PASSWORD_LENGTH) {
            throw ValidationException::withMessages([
                'password' => sprintf(
                    'The administrator password must be at least %d characters long.',
                    self::MINIMUM_PASSWORD_LENGTH,
                ),
            ]);
        }

        if ($this->isCommonPassword($password)) {
            throw ValidationException::withMessages([
                'password' => 'This password is too common. Choose a unique password.',
            ]);
        }

        if (trim($password) !== $password) {
            throw ValidationException::withMessages([
                'password' => 'The administrator password must not start or end with a space.',
            ]);
        }
    }

    /**
     * Whether the password is one of the well-known weak passwords.
     */
    public function isCommonPassword(string $password): bool
    {
        return in_array(Str::lower($password), self::COMMON_PASSWORDS, true);
    }
}
