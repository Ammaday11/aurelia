<?php

namespace App\Policies;

use App\Models\Extra;
use App\Models\User;

class ExtraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Extra $extra): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Extra $extra): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Extra $extra): bool
    {
        return $user->isAdmin();
    }
}
