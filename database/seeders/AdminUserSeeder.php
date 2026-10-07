<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeds the first admin account. Credentials are overridable via
     * .env (ADMIN_EMAIL / ADMIN_PASSWORD) so a deployment never has to
     * ship with the documented default password — see README "Default
     * Admin Credentials" for the values used when they're left unset.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@aureliaboutonniere.test');
        $password = env('ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Aurelia Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Admin account ready: {$email} / {$password}");
    }
}
