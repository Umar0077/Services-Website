<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\DisclaimerVersion;
use App\Models\User;

/**
 * The single source of the client-approved disclaimer. The footer, /disclaimer page, forms, service, emergency
 * and location pages all read from here, so the text cannot differ between pages.
 *
 * Only admins can change it (Admin → Settings → Disclaimer). Seeders and content imports never write it.
 */
class Disclaimer
{
    public static function text(): string
    {
        return trim(str_replace("\r\n", "\n", (string) app(Settings::class)->get('disclaimer')));
    }

    /** @return array<int, array{label: bool, text: string}> */
    public static function paragraphs(): array
    {
        return collect(preg_split("/\n\s*\n/", self::text()))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->map(fn ($p) => ['label' => mb_strlen($p) <= 40 && str_ends_with($p, ':'), 'text' => $p])
            ->values()
            ->all();
    }

    /** The availability sentence(s) of the disclaimer, used next to any same-day / 24/7 mention. */
    public static function availabilityNote(): string
    {
        foreach (self::paragraphs() as $paragraph) {
            if (! $paragraph['label'] && preg_match('/same-day|24\/7/i', $paragraph['text'])) {
                return $paragraph['text'];
            }
        }

        return '';
    }

    public static function update(string $text, User $user, ?string $note = null): bool
    {
        $text = trim(str_replace("\r\n", "\n", $text));
        if ($text === self::text()) {
            return false;
        }

        if (! DisclaimerVersion::query()->exists()) {
            // Keep the text that was live before the first admin edit.
            DisclaimerVersion::query()->create(['text' => self::text(), 'note' => 'Original approved text', 'created_at' => now()->subSecond()]);
        }

        app(Settings::class)->set(['disclaimer' => $text]);
        DisclaimerVersion::query()->create(['text' => $text, 'note' => $note, 'user_id' => $user->id, 'created_at' => now()]);
        ActivityLog::record('disclaimer_updated', null, 'Disclaimer text changed', ['note' => $note]);

        return true;
    }
}
