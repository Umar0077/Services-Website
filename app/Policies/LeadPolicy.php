<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

/** Leads come only from the public form; staff can review and annotate, admins can delete. */
class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->role->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->role->isAdmin();
    }
}
