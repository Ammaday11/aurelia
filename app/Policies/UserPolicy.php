<?php

namespace App\Policies;

use App\Models\User;

/**
 * Governs the admin "Users" screen, where staff manage each other's admin
 * accounts (see App\Http\Controllers\Admin\UserController). Every
 * registered user is an admin (see User::isAdmin()), so the interesting
 * rule here isn't who can manage accounts — it's protecting the admin
 * from locking themselves, or everyone, out: delete() blocks removing
 * your own account or the last remaining one.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($user->is($target)) {
            return false;
        }

        return User::query()->count() > 1;
    }
}
