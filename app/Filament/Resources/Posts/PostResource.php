<?php

namespace App\Filament\Resources\Posts;

use App\Enums\PostStatus;
use App\Filament\Support\Fields;
use App\Models\Post;
use App\Support\SeoChecks;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Posts';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        $canPublish = fn () => auth()->user()?->can('publish', Post::class);

        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    Fields::title(),
                    Fields::slug('/blog/'),
                    Textarea::make('excerpt')->rows(3)->maxLength(400)
                        ->helperText('Shown on article cards and used as the meta description when none is set.'),
                    \Filament\Forms\Components\ToggleButtons::make('body_format')
                        ->label('Write the article with')
                        ->options(['rich' => 'Visual editor', 'html' => 'HTML code'])
                        ->icons(['rich' => 'heroicon-o-pencil-square', 'html' => 'heroicon-o-code-bracket'])
                        ->default('rich')
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function (Get $get, \Filament\Schemas\Components\Utilities\Set $set, ?string $state) {
                            // Switching between editor modes carries over the current article so nothing is lost.
                            if ($state === 'html') {
                                $current = Fields::toText($get('body'));
                                if (filled($current) || blank($get('body_html'))) {
                                    $set('body_html', $current);
                                }
                            } elseif ($state === 'rich' && filled($get('body_html'))) {
                                $set('body', (string) $get('body_html'));
                            }
                        })
                        ->helperText(fn (Get $get) => $get('body_format') === 'html'
                            ? 'Paste or write HTML. Headings (h2–h6), paragraphs, lists, links, images, tables, blockquotes and bold/italic are kept. Scripts, styles, iframes, forms and on* attributes are removed automatically for security. Use Preview to check the result.'
                            : 'Switching back to the visual editor keeps the text; markup the editor does not support is simplified.'),
                    Fields::rich('body', 'Article')->visible(fn (Get $get) => $get('body_format') !== 'html'),
                    \Filament\Forms\Components\CodeEditor::make('body_html')
                        ->label('Article HTML')
                        ->language(\Filament\Forms\Components\CodeEditor\Enums\Language::Html)
                        ->visible(fn (Get $get) => $get('body_format') === 'html')
                        ->required(fn (Get $get) => $get('body_format') === 'html')
                        ->live(onBlur: true)
                        ->rule('max:500000'),
                ]),
                Section::make('FAQs')->description('Optional. Shown at the end of the article and marked up as FAQ structured data.')
                    ->collapsible()->schema([Fields::faqs()]),
                Fields::seo('/blog/', 'title', 'excerpt'),
                Section::make('Keywords & editorial checks')->collapsible()->schema([
                    Grid::make(2)->schema([
                        TextInput::make('primary_keyword')->maxLength(120)->live(onBlur: true)
                            ->helperText('The main homeowner question this article answers.'),
                        TextInput::make('secondary_keywords')->maxLength(250)->helperText('Comma separated.'),
                    ]),
                    Text::make(fn (Get $get) => new HtmlString(
                        '<ul style="display:grid;gap:6px">'.collect(SeoChecks::post(self::checkData($get)))->map(fn ($c) => '<li>'.($c['ok'] ? '✅' : '⚠️').' '.e($c['label']).'</li>')->implode('').'</ul>'
                    )),
                    Fields::targetingReview(['title', 'excerpt', 'body', 'body_html', 'seo_title', 'seo_description', 'primary_keyword', 'secondary_keywords']),
                ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Publishing')->schema([
                    Select::make('status')->options(PostStatus::class)->default(PostStatus::Draft)->required()->native(false)
                        ->disabled(fn () => ! $canPublish())
                        ->helperText(fn () => $canPublish() ? 'Scheduled posts go live automatically at the publish time.' : 'Authors save drafts. An editor publishes.'),
                    DateTimePicker::make('published_at')->label('Publish date & time')->seconds(false)
                        ->disabled(fn () => ! $canPublish())
                        ->helperText('For scheduling, choose a future time and set status to Scheduled.'),
                    Toggle::make('is_featured')->label('Feature on the blog page'),
                    Toggle::make('show_toc')->label('Show table of contents')->default(true),
                    Select::make('author_id')->relationship('author', 'name')->searchable()->preload()
                        ->default(fn () => auth()->id())
                        ->disabled(fn () => ! $canPublish()),
                ]),
                Section::make('Featured image')->schema([
                    Fields::media('featured_media_id', 'Image')->hiddenLabel(),
                ]),
                Section::make('Organization')->schema([
                    Select::make('category_id')->relationship('category', 'name')->preload()->searchable(),
                    Select::make('tags')->relationship('tags', 'name')->multiple()->preload()->searchable()
                        ->createOptionForm([
                            TextInput::make('name')->required()->maxLength(60),
                            TextInput::make('slug')->required()->maxLength(60)->unique('tags', 'slug')->regex('/^[a-z0-9-]+$/'),
                        ]),
                    Select::make('services')->label('Related services')->relationship('services', 'title')->multiple()->preload(),
                    Select::make('relatedPosts')->label('Related posts')->relationship('relatedPosts', 'title', fn (Builder $query, ?Post $record) => $query->when($record, fn ($q) => $q->where('posts.id', '!=', $record->id)))
                        ->multiple()->searchable()->maxItems(3)->helperText('Up to 3. Empty = same-category posts.'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('featuredMedia.path')->label('')->disk('public')->imageSize(44)->square(),
                TextColumn::make('title')->searchable()->sortable()->limit(60)->weight('medium')
                    ->description(fn (Post $r) => '/blog/'.$r->slug),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('category.name')->label('Category')->toggleable(),
                TextColumn::make('author.name')->label('Author')->toggleable(),
                TextColumn::make('published_at')->dateTime('M j, Y g:ia')->sortable()->label('Publish date'),
                TextColumn::make('updated_at')->since()->sortable()->label('Updated')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(PostStatus::class),
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('author')->relationship('author', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('preview')->icon(Heroicon::OutlinedEye)->color('gray')
                    ->url(fn (Post $r) => self::previewUrl($r))->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')->label('Publish now')->icon(Heroicon::OutlinedCheckCircle)
                        ->visible(fn () => auth()->user()->can('publish', Post::class))
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Post $p) => $p->update([
                            'status' => PostStatus::Published, 'published_at' => $p->published_at?->isPast() ? $p->published_at : now(),
                        ])))->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')->label('Move to draft')->icon(Heroicon::OutlinedPauseCircle)
                        ->visible(fn () => auth()->user()->can('publish', Post::class))
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => PostStatus::Draft]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Authors only see their own posts. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class])->with(['featuredMedia', 'category', 'author']);
        $user = auth()->user();

        return $user && ! $user->role->isEditorOrAbove() ? $query->where('author_id', $user->id) : $query;
    }

    /** Form state as plain values for the editorial checks, using whichever editor is active. */
    private static function checkData(Get $get): array
    {
        $data = array_map(fn ($v) => Fields::toText($v), $get());
        if ($get('body_format') === 'html') {
            $data['body'] = (string) $get('body_html');
        }

        return $data;
    }

    public static function previewUrl(Post $post): string
    {
        return URL::temporarySignedRoute('blog.preview', now()->addHours(2), ['post' => $post->slug]);
    }

    public static function getRelations(): array
    {
        return [RelationManagers\RevisionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return self::getEloquentQuery();
    }
}
