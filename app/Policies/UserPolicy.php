<?php

namespace App\Policies;

use App\Models\User;

/** Admins manage users, but never anyone ranked above them. Only a Super Admin can manage Super Admins. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->role->isAdmin() || $user->is($model);
    }

    public function create(User $user): bool
    {
        return $user->role->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->role->isAdmin() && $user->role->rank() >= $model->role->rank();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->role->isAdmin()
            && ! $user->is($model)
            && $user->role->rank() >= $model->role->rank();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
