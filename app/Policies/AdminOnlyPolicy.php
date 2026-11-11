<?php

namespace App\Policies;

use App\Models\User;

/** Redirects and call-tracking numbers: admins only. */
class AdminOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isAdmin();
    }

    public function view(User $user, $model): bool
    {
        return $user->role->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->role->isAdmin();
    }

    public function update(User $user, $model): bool
    {
        return $user->role->isAdmin();
    }

    public function delete(User $user, $model): bool
    {
        return $user->role->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->role->isAdmin();
    }
}
