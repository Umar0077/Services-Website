<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

/** Authors write and edit their own unpublished posts. Editors and above manage and publish everything. */
class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return $user->role->isEditorOrAbove() || $post->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->role->isEditorOrAbove()) {
            return true;
        }

        return $post->author_id === $user->id && $post->status !== PostStatus::Published;
    }

    public function publish(User $user): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->role->isEditorOrAbove()
            || ($post->author_id === $user->id && $post->status === PostStatus::Draft);
    }

    public function deleteAny(User $user): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function restoreAny(User $user): bool
    {
        return $user->role->isEditorOrAbove();
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->role->isAdmin();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->role->isAdmin();
    }
}
