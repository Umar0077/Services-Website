<?php

namespace App\Support;

use App\Filament\RichBlocks\CalloutBlock;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Renders CMS rich text safely: expands editor custom blocks, then sanitizes with an allowlist,
 * then adds heading anchors for the table of contents. CMS HTML is never echoed unsanitized.
 */
class RichText
{
    public static function render(string|array|null $html, bool $withAnchors = false): string
    {
        if (is_array($html)) {
            $html = RichContentRenderer::make($html)->customBlocks([CalloutBlock::class])->toHtml();
        }
        if (blank($html)) {
            return '';
        }

        if (str_contains($html, 'data-type="customBlock"')) {
            $html = RichContentRenderer::make($html)->customBlocks([CalloutBlock::class])->toHtml();
        }

        // Editor alignment comes through as inline styles; keep only alignment, as a class. Other CSS is dropped.
        $html = preg_replace_callback('/<(p|h[2-6])([^>]*?)\sstyle="[^"]*text-align:\s*(left|center|right|justify)[^"]*"([^>]*)>/i',
            fn ($m) => "<{$m[1]}{$m[2]} class=\"align-{$m[3]}\"{$m[4]}>", $html);

        $clean = self::sanitizer()->sanitize($html);

        // External links open safely.
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $clean = preg_replace_callback('/<a\s[^>]*href="(https?:\/\/[^"]+)"[^>]*>/i', function ($m) use ($host) {
            if (parse_url($m[1], PHP_URL_HOST) === $host || str_contains($m[0], 'rel=')) {
                return $m[0];
            }

            return str_replace('<a ', '<a rel="noopener noreferrer" ', $m[0]);
        }, $clean);

        // Lazy-load inline images.
        $clean = preg_replace('/<img(?![^>]*loading=)/i', '<img loading="lazy" decoding="async"', $clean);

        return $withAnchors ? self::addHeadingAnchors($clean)['html'] : $clean;
    }

    /** @return array<int, array{id: string, text: string, level: int}> */
    public static function tableOfContents(string|array|null $html): array
    {
        return self::addHeadingAnchors(self::render($html))['toc'];
    }

    /** @return array{html: string, toc: array} */
    private static function addHeadingAnchors(string $html): array
    {
        $toc = [];
        $used = [];
        $html = preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h\1>/is', function ($m) use (&$toc, &$used) {
            $text = trim(html_entity_decode(strip_tags($m[3])));
            $id = Str::slug($text) ?: 'section';
            $base = $id;
            $i = 2;
            while (isset($used[$id])) {
                $id = $base.'-'.$i++;
            }
            $used[$id] = true;
            $toc[] = ['id' => $id, 'text' => $text, 'level' => (int) $m[1]];
            $attrs = preg_replace('/\s*id="[^"]*"/', '', $m[2]);

            return "<h{$m[1]} id=\"{$id}\"{$attrs}>{$m[3]}</h{$m[1]}>";
        }, $html);

        return ['html' => $html, 'toc' => $toc];
    }

    private static function sanitizer(): HtmlSanitizer
    {
        static $sanitizer = null;

        if ($sanitizer) {
            return $sanitizer;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
            ->allowMediaSchemes(['https', 'http'])
            ->allowAttribute('class', ['p', 'h2', 'h3', 'h4', 'h5', 'h6', 'aside', 'span', 'figure', 'table'])
            ->allowAttribute('id', ['h2', 'h3', 'h4'])
            ->allowAttribute('colspan', ['td', 'th'])
            ->allowAttribute('rowspan', ['td', 'th'])
            ->allowElement('aside', ['class', 'role'])
            ->blockElement('div')
            ->dropElement('script')
            ->dropElement('style')
            ->dropElement('iframe')
            ->dropElement('form')
            ->withMaxInputLength(500_000);

        return $sanitizer = new HtmlSanitizer($config);
    }

    /** Plain-text excerpt helper. */
    public static function excerpt(?string $html, int $limit = 160): string
    {
        // Drop the contents of non-text elements (scripts, styles...) before stripping tags.
        $html = preg_replace('#<(script|style|noscript|template|iframe)\b[^>]*>.*?</\1>#is', ' ', (string) $html);

        return Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html)))), $limit);
    }
}
