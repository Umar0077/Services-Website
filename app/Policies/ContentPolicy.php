<?php

namespace App\Policies;

use App\Models\User;

/** Pages, services, states and cities: editors manage content, admins can delete. */
class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function view(User $user, $model): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function create(User $user): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function update(User $user, $model): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function delete(User $user, $model): bool
    {
        // System pages (About, Privacy, Terms...) are linked throughout the site and cannot be deleted.
        if (method_exists($model, 'isSystem') && $model->isSystem()) {
            return false;
        }

        return $user->role->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->role->isAdmin();
    }
}
