<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Media $media): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Media $media): bool
    {
        return $user->role->isEditorOrAbove() || $media->uploaded_by === $user->id;
    }

    /** Media still referenced by content can never be deleted. */
    public function delete(User $user, Media $media): bool
    {
        return $user->role->isEditorOrAbove() && $media->usageCount() === 0;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role->isEditorOrAbove();
    }
}
