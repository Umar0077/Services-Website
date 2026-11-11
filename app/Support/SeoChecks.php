<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Editorial SEO checklist shown while editing. These are guides for editors, not ranking rules —
 * there is deliberately no keyword-density scoring.
 */
class SeoChecks
{
    /** @return array<int, array{ok: bool, label: string}> */
    public static function post(array $d): array
    {
        $body = (string) ($d['body'] ?? '');
        $text = trim(html_entity_decode(strip_tags($body)));
        $words = str_word_count($text);
        $title = (string) ($d['seo_title'] ?: ($d['title'] ?? ''));
        $description = (string) ($d['seo_description'] ?: ($d['excerpt'] ?? ''));
        $keyword = Str::lower(trim((string) ($d['primary_keyword'] ?? '')));
        $firstParagraph = Str::lower(strip_tags(Str::before($body, '</p>')));
        $h2 = preg_match_all('/<h2[\s>]/i', $body);
        $internal = preg_match_all('/href="(\/(?!\/)|'.preg_quote(rtrim(config('app.url'), '/'), '/').')/i', $body);

        $checks = [
            [mb_strlen($title) >= 30 && mb_strlen($title) <= 62, 'Title is 30–62 characters ('.mb_strlen($title).')'],
            [mb_strlen($description) >= 110 && mb_strlen($description) <= 160, 'Meta description or excerpt is 110–160 characters ('.mb_strlen($description).')'],
            [$words >= 600, "Article has enough depth to be useful ({$words} words; 600+ recommended)"],
            [$h2 >= 2, "Uses section headings ({$h2} H2s)"],
            [$internal >= 1, "Links to at least one related page on this site ({$internal})"],
            [filled($d['featured_media_id'] ?? null), 'Has a featured image'],
            [filled($d['category_id'] ?? null), 'Has a category'],
        ];

        if ($keyword !== '') {
            $checks[] = [str_contains(Str::lower($title), $keyword), 'Primary keyword appears in the title'];
            $checks[] = [str_contains($firstParagraph, $keyword) || str_contains(Str::lower($text), $keyword), 'Primary keyword appears in the article text'];
        } else {
            $checks[] = [false, 'Primary keyword set (helps you review focus — not used for scoring)'];
        }

        return array_map(fn ($c) => ['ok' => (bool) $c[0], 'label' => $c[1]], $checks);
    }
}
