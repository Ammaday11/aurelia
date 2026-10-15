<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WrappingPaper;

class WrappingPaperPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, WrappingPaper $wrappingPaper): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, WrappingPaper $wrappingPaper): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, WrappingPaper $wrappingPaper): bool
    {
        return $user->isAdmin();
    }
}
