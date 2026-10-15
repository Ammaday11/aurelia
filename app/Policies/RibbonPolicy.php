<?php

namespace App\Policies;

use App\Models\Ribbon;
use App\Models\User;

class RibbonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Ribbon $ribbon): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Ribbon $ribbon): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Ribbon $ribbon): bool
    {
        return $user->isAdmin();
    }
}
