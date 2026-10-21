<?php

namespace App\Console\Commands;

use App\Services\AdminUserService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

/**
 * Creates the first (or an additional) administrator account.
 *
 * The command is CLI-only — Artisan commands are not routed, so there is
 * no HTTP endpoint a visitor could reach. It is the supported way to
 * promote an account to administrator without editing the database by
 * hand.
 */
class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:admin
                            {--email= : The administrator email address}
                            {--name= : The administrator display name}
                            {--password= : The administrator password (prefer the ADMIN_PASSWORD environment variable)}
                            {--force : Run in production without an interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or promote an administrator account';

    /**
     * Execute the console command.
     */
    public function handle(AdminUserService $admins): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->components->error('Refusing to run in production without --force.');

            return self::FAILURE;
        }

        $email = (string) ($this->option('email') ?: env('ADMIN_EMAIL', ''));
        $name = (string) ($this->option('name') ?: env('ADMIN_NAME', ''));
        $password = (string) ($this->option('password') ?: env('ADMIN_PASSWORD', ''));

        if ($password === '' && $this->input->isInteractive()) {
            $password = (string) $this->secret('Administrator password (input is hidden)');
        }

        if ($email === '' || $password === '') {
            $this->components->error('Both an email address and a password are required.');
            $this->components->info('Pass --email and --password, or set ADMIN_EMAIL and ADMIN_PASSWORD.');

            return self::FAILURE;
        }

        try {
            $user = $admins->create($email, $name, $password);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $errors) {
                foreach ($errors as $error) {
                    $this->components->error($error);
                }
            }

            return self::FAILURE;
        }

        $this->components->info(sprintf('Administrator ready: %s', $user->email));

        return self::SUCCESS;
    }
}
