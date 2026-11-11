<?php

namespace App\Filament\RichBlocks;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;

/** Editor block: a highlighted tip / safety note / key takeaway inside an article. */
class CalloutBlock extends RichContentCustomBlock
{
    public const VARIANTS = [
        'tip' => 'Tip',
        'safety' => 'Safety note',
        'key' => 'Key takeaway',
    ];

    public static function getId(): string
    {
        return 'callout';
    }

    public static function getLabel(): string
    {
        return 'Callout';
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalDescription('A highlighted note inside the article.')
            ->schema([
                Select::make('variant')->options(self::VARIANTS)->default('tip')->required(),
                TextInput::make('title')->maxLength(120),
                Textarea::make('text')->required()->rows(4)->maxLength(1000),
            ]);
    }

    public static function getPreviewLabel(array $config): string
    {
        return (self::VARIANTS[$config['variant'] ?? 'tip'] ?? 'Callout').': '.($config['title'] ?? '');
    }

    public static function toPreviewHtml(array $config): string
    {
        return self::toHtml($config, []);
    }

    public static function toHtml(array $config, array $data): string
    {
        $variant = array_key_exists($config['variant'] ?? '', self::VARIANTS) ? $config['variant'] : 'tip';
        $title = e($config['title'] ?? self::VARIANTS[$variant]);
        $text = nl2br(e($config['text'] ?? ''));

        return "<aside class=\"callout callout-{$variant}\"><p class=\"callout-title\">{$title}</p><p>{$text}</p></aside>";
    }
}
