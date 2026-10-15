<?php

namespace App\Policies;

use App\Models\Flower;
use App\Models\User;

class FlowerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Flower $flower): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Flower $flower): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Flower $flower): bool
    {
        return $user->isAdmin();
    }
}
