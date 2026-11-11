<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Admin roles. Capabilities are enforced in the policies (app/Policies), not only by hiding UI.
 *
 * Super Admin: everything, including managing other admins.
 * Admin: everything except managing super admins.
 * Editor: all content (blog, services, locations, pages, media), plus leads.
 * Author: own blog posts (drafts only; cannot publish), media uploads.
 */
enum Role: string implements HasLabel
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case Author = 'author';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Editor => 'Editor',
            self::Author => 'Author',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    public function isEditorOrAbove(): bool
    {
        return $this !== self::Author;
    }

    public function rank(): int
    {
        return match ($this) {
            self::SuperAdmin => 4,
            self::Admin => 3,
            self::Editor => 2,
            self::Author => 1,
        };
    }
}
