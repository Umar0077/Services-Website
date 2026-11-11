<?php

namespace App\Policies;

use App\Models\User;

/** The audit log is read-only for everyone. */
class ActivityLogPolicy
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
        return false;
    }

    public function update(User $user, $model): bool
    {
        return false;
    }

    public function delete(User $user, $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
