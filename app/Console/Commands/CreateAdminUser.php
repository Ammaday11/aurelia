<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Creates a new admin/staff account. Public self-registration is disabled
 * (see routes/auth.php), so this is the supported way to add a second
 * admin after the initial seeded one.
 *
 * Usage: php artisan admin:create
 *        php artisan admin:create --name="Jane Doe" --email=jane@example.com --password=secret123
 */
class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--name=} {--email=} {--password=}';

    protected $description = 'Create a new Aurelia Boutonnière admin account';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("Admin account created: {$user->email}");

        return self::SUCCESS;
    }
}
