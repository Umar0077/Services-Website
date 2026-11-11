<?php

namespace App\Filament\Support;

use App\Models\Media;
use App\Services\MediaService;
use App\Support\TargetingCheck;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/** Shared admin form building blocks, so every content type edits SEO, media, FAQs and slugs the same way. */
class Fields
{
    /** Title field that fills the slug while creating (never silently changes an existing slug). */
    public static function title(string $name = 'title', string $label = 'Title'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->maxLength(180)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation) {
                // Only while creating, and only if the editor has not typed a custom slug.
                if ($operation === 'create' && in_array($get('slug'), [null, '', Str::slug((string) $old)], true)) {
                    $set('slug', Str::slug((string) $state));
                }
            });
    }

    public static function slug(string $prefix, ?\Closure $uniqueScope = null): TextInput
    {
        return TextInput::make('slug')
            ->required()
            ->maxLength(120)
            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
            ->validationMessages(['regex' => 'Use lowercase letters, numbers and single hyphens only.'])
            ->unique(ignoreRecord: true, modifyRuleUsing: $uniqueScope)
            ->prefix($prefix)
            ->helperText('Changing the slug of a published page automatically adds a 301 redirect from the old URL.')
            ->dehydrateStateUsing(fn (?string $state) => Str::slug((string) $state));
    }

    /** Media picker with inline upload. Only safe raster images are accepted. */
    public static function media(string $name, string $label = 'Image'): Select
    {
        return Select::make($name)
            ->label($label)
            ->relationship(Str::camel(Str::beforeLast($name, '_id')), 'filename')
            ->getOptionLabelFromRecordUsing(fn (Media $m) => $m->filename.($m->alt ? ' — '.Str::limit($m->alt, 50) : ''))
            ->searchable(['filename', 'alt', 'original_name'])
            ->preload(false)
            ->helperText(fn (?string $state) => ($m = $state ? Media::find($state) : null)
                ? new HtmlString('<img src="'.e($m->url()).'" alt="" style="max-height:110px;border-radius:8px;margin-top:6px">')
                : 'Choose from the media library, or upload a new image.')
            ->live()
            ->createOptionForm([
                FileUpload::make('file')
                    ->label('Image')
                    ->image()
                    ->acceptedFileTypes(MediaService::ALLOWED_MIME)
                    ->maxSize(MediaService::MAX_KB)
                    ->disk('public')
                    ->directory('media/tmp')
                    ->required(),
                TextInput::make('alt')->label('Alt text')->required()->maxLength(250)
                    ->helperText('Describe the image for people who cannot see it.'),
            ])
            ->createOptionUsing(function (array $data) {
                return app(MediaService::class)->registerStoredFile($data['file'], basename($data['file']), ['alt' => $data['alt']])->id;
            });
    }

    public static function faqs(string $name = 'faqs'): Repeater
    {
        return Repeater::make($name)
            ->label('FAQs')
            ->schema([
                TextInput::make('question')->required()->maxLength(250),
                Textarea::make('answer')->required()->rows(3)->maxLength(2000),
            ])
            ->itemLabel(fn (array $state) => $state['question'] ?? null)
            ->collapsible()
            ->collapsed()
            ->reorderable()
            ->addActionLabel('Add question')
            ->defaultItems(0);
    }

    public static function rich(string $name, string $label): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'link'],
                ['h2', 'h3', 'h4', 'h5', 'h6'],
                ['alignStart', 'alignCenter', 'alignEnd'],
                ['blockquote', 'bulletList', 'orderedList', 'table', 'attachFiles', 'customBlocks'],
                ['undo', 'redo'],
            ])
            ->customBlocks([\App\Filament\RichBlocks\CalloutBlock::class])
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory('media/content')
            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->fileAttachmentsMaxSize(MediaService::MAX_KB)
            ->extraInputAttributes(['style' => 'min-height: 22rem']);
    }

    /**
     * SEO panel: overrides, robots, Open Graph, and a live search-result preview.
     *
     * @param  string  $urlPrefix  e.g. /services/
     */
    public static function seo(string $urlPrefix, string $titleField = 'title', string $descriptionFallback = 'short_description'): Section
    {
        return Section::make('Search & social')
            ->description('Leave blank to use sensible defaults from the content.')
            ->icon('heroicon-o-magnifying-glass')
            ->collapsible()
            ->schema([
                Text::make(fn (Get $get) => self::serpPreview($urlPrefix.$get('slug'), $get('seo_title') ?: $get($titleField), $get('seo_description') ?: Str::limit(strip_tags((string) $get($descriptionFallback)), 160))),
                TextInput::make('seo_title')->label('SEO title')->maxLength(70)->live(onBlur: true)
                    ->hint(fn (?string $state) => strlen((string) $state).'/60')
                    ->helperText('Used exactly as written. Aim for 50–60 characters and describe a homeowner need.'),
                Textarea::make('seo_description')->label('Meta description')->rows(2)->maxLength(320)->live(onBlur: true)
                    ->hint(fn (?string $state) => strlen((string) $state).'/155')
                    ->helperText('Aim for 120–155 characters.'),
                Grid::make(3)->schema([
                    TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(250)->columnSpan(1)
                        ->helperText('Only set to point at a different page. Pages with a different canonical are left out of the sitemap.'),
                    Toggle::make('robots_index')->label('Allow indexing')->default(true)->inline(false),
                    Toggle::make('robots_follow')->label('Follow links')->default(true)->inline(false),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('og_title')->label('Social share title')->maxLength(120),
                    Textarea::make('og_description')->label('Social share description')->rows(2)->maxLength(320),
                ]),
                self::media('og_media_id', 'Social share image'),
            ]);
    }

    /** Live editorial review: residential targeting and unsupported claims (warnings, not blockers). */
    public static function targetingReview(array $fields): Callout
    {
        return Callout::make('Targeting & claims review')
            ->description(function (Get $get) use ($fields) {
                $warnings = TargetingCheck::run(...array_map(fn ($f) => self::toText($get($f)), $fields));

                return $warnings
                    ? new HtmlString('<ul style="list-style:disc;padding-left:1.1rem">'.collect($warnings)->map(fn ($w) => '<li>'.e($w).'</li>')->implode('').'</ul>')
                    : 'No commercial targeting or unsupported claims detected. Content should address U.S. homeowners.';
            })
            ->color(fn (Get $get) => TargetingCheck::run(...array_map(fn ($f) => self::toText($get($f)), $fields)) ? 'warning' : 'success')
            ->icon('heroicon-o-shield-check');
    }

    /** Form state of a rich editor is TipTap JSON; convert any field value to HTML/text for checks. */
    public static function toText(mixed $value): string
    {
        if (is_array($value)) {
            if (($value['type'] ?? null) === 'doc') {
                try {
                    return \Filament\Forms\Components\RichEditor\RichContentRenderer::make($value)->toHtml();
                } catch (\Throwable) {
                    return '';
                }
            }

            return collect($value)->flatten()->filter(fn ($v) => is_string($v))->implode(' ');
        }

        return (string) $value;
    }

    private static function serpPreview(string $path, ?string $title, ?string $description): HtmlString
    {
        $url = rtrim(config('app.url'), '/').'/'.ltrim($path, '/');
        $title = e(Str::limit((string) $title, 62));
        $description = e(Str::limit((string) $description, 158)) ?: '<em>No description yet.</em>';

        return new HtmlString(<<<HTML
            <div style="background:#fff;border:1px solid rgba(127,127,127,.25);border-radius:10px;padding:14px 16px;font-family:arial,sans-serif;max-width:620px">
                <div style="font-size:12px;color:#5f6368">{$url}</div>
                <div style="font-size:19px;color:#1a0dab;margin:4px 0 3px;line-height:1.3">{$title}</div>
                <div style="font-size:13.5px;color:#4d5156;line-height:1.5">{$description}</div>
            </div>
        HTML);
    }
}
