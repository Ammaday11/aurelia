<?php

namespace App\Policies;

use App\Models\CustomCharge;
use App\Models\User;

class CustomChargePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, CustomCharge $customCharge): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, CustomCharge $customCharge): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, CustomCharge $customCharge): bool
    {
        return $user->isAdmin();
    }
}
