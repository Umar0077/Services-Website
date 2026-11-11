<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Support\Fields;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $items = fn (string $name, string $label) => Repeater::make($name)->label($label)->schema([
            TextInput::make('title')->required()->maxLength(100),
            Textarea::make('text')->required()->rows(2)->maxLength(400),
        ])->itemLabel(fn (array $state) => $state['title'] ?? null)->collapsible()->maxItems(8)->columnSpanFull();

        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    Fields::title(),
                    Fields::slug('/')
                        ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                            if (in_array($value, Page::RESERVED_SLUGS, true)) {
                                $fail('This address is used by another part of the site.');
                            }
                        }),
                    TextInput::make('heading')->label('Page heading (H1)')->maxLength(180)->helperText('Defaults to the title.'),
                    Textarea::make('intro')->rows(3)->maxLength(500),
                ]),
                Section::make('Page sections')
                    ->description('Controlled building blocks that keep pages consistent with the design system.')
                    ->collapsible()
                    ->schema([
                        Builder::make('sections')->hiddenLabel()->blockNumbers(false)->collapsible()->blocks([
                            Block::make('text')->label('Heading + text')->icon('heroicon-o-bars-3-bottom-left')->schema([
                                Grid::make(2)->schema([TextInput::make('eyebrow')->maxLength(60), TextInput::make('heading')->required()->maxLength(120)]),
                                Fields::rich('body', 'Text'),
                            ]),
                            Block::make('features')->label('Feature cards')->icon('heroicon-o-squares-2x2')->schema([
                                Grid::make(2)->schema([TextInput::make('eyebrow')->maxLength(60), TextInput::make('heading')->required()->maxLength(120)]),
                                Textarea::make('intro')->rows(2)->maxLength(300),
                                $items('items', 'Cards'),
                            ]),
                            Block::make('steps')->label('Numbered steps')->icon('heroicon-o-list-bullet')->schema([
                                Grid::make(2)->schema([TextInput::make('eyebrow')->maxLength(60), TextInput::make('heading')->required()->maxLength(120)]),
                                $items('items', 'Steps'),
                            ]),
                            Block::make('image_text')->label('Image + text')->icon('heroicon-o-photo')->schema([
                                Grid::make(2)->schema([TextInput::make('eyebrow')->maxLength(60), TextInput::make('heading')->required()->maxLength(120)]),
                                Select::make('media_id')->label('Image')->options(fn () => \App\Models\Media::query()->latest()->limit(200)->pluck('filename', 'id'))->searchable(),
                                Select::make('image_side')->options(['left' => 'Image on the left', 'right' => 'Image on the right'])->default('left'),
                                Fields::rich('body', 'Text'),
                            ]),
                        ]),
                    ]),
                Section::make('Body')->description('Main text. Used for legal pages; optional for others.')->collapsible()
                    ->schema([Fields::rich('body', 'Body')->hiddenLabel()]),
                Fields::seo('/', 'title', 'intro'),
                Fields::targetingReview(['title', 'heading', 'intro', 'body', 'sections', 'cta_heading', 'cta_text', 'seo_title', 'seo_description']),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Publishing')->schema([
                    Toggle::make('is_published')->label('Published'),
                    Select::make('template')->options(Page::TEMPLATES)->default('standard')->required()->native(false),
                    Toggle::make('show_in_footer')->label('Link in footer (legal column)'),
                    TextInput::make('system_key')->label('System page')->disabled()->dehydrated(false)
                        ->visible(fn (?Page $record) => $record?->isSystem())
                        ->helperText('Linked from fixed places on the site; it cannot be deleted.'),
                ]),
                Section::make('Hero image')->schema([Fields::media('hero_media_id')->hiddenLabel()]),
                Section::make('Call to action')->description('Bottom banner. Leave blank for the site default.')->schema([
                    TextInput::make('cta_heading')->maxLength(120),
                    Textarea::make('cta_text')->rows(2)->maxLength(300),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->columns([
                TextColumn::make('title')->searchable()->weight('medium')->description(fn (Page $r) => '/'.$r->slug),
                TextColumn::make('template')->badge()->formatStateUsing(fn ($state) => Page::TEMPLATES[$state] ?? $state),
                IconColumn::make('is_published')->boolean()->label('Live'),
                IconColumn::make('system_key')->label('System')->boolean()->getStateUsing(fn (Page $r) => $r->isSystem()),
                TextColumn::make('updated_at')->since()->label('Updated')->sortable(),
            ])
            ->filters([SelectFilter::make('template')->options(Page::TEMPLATES)])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
