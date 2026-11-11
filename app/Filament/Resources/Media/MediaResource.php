<?php

namespace App\Filament\Resources\Media;

use App\Models\Media;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Media library';

    protected static ?string $modelLabel = 'image';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make()->columnSpan(['lg' => 2])->schema([
                ImageEntry::make('path')->hiddenLabel()->disk('public')->imageHeight(320)->extraImgAttributes(['style' => 'object-fit:contain;border-radius:12px']),
                TextInput::make('alt')->label('Alt text')->required()->maxLength(250)
                    ->helperText('Describe what the image shows, for screen readers and search engines. Do not stuff keywords.'),
                TextInput::make('caption')->maxLength(250),
                Textarea::make('description')->rows(3)->maxLength(1000),
            ]),
            Section::make('File details')->columnSpan(['lg' => 1])->schema([
                Grid::make(1)->schema([
                    TextEntry::make('filename'),
                    TextEntry::make('original_name')->placeholder('—'),
                    TextEntry::make('mime_type')->label('Type'),
                    TextEntry::make('dimensions')->state(fn (Media $r) => $r->width ? "{$r->width} × {$r->height}px" : '—'),
                    TextEntry::make('size')->formatStateUsing(fn ($state, Media $r) => $r->humanSize()),
                    TextEntry::make('variants')->label('Responsive sizes')->state(fn (Media $r) => collect($r->variants ?? [])->keys()->map(fn ($w) => $w.'w')->implode(', ') ?: 'Original only'),
                    TextEntry::make('usage')->label('Used on')->state(fn (Media $r) => $r->usageCount().' item(s)')
                        ->helperText('Images in use cannot be deleted.'),
                    TextEntry::make('url')->label('Public URL')->state(fn (Media $r) => $r->url())->copyable()->limit(40),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['md' => 3, 'xl' => 5])
            ->paginationPageOptions([20, 40, 80])
            ->defaultPaginationPageOption(20)
            ->columns([
                Stack::make([
                    // Small WebP thumbnail instead of the full-size original keeps each grid page light.
                    ImageColumn::make('path')->disk('public')->imageHeight(150)->width('100%')
                        ->state(fn (Media $record) => $record->variants[480] ?? $record->path)
                        ->extraImgAttributes(['style' => 'object-fit:cover;width:100%;border-radius:10px', 'loading' => 'lazy']),
                    TextColumn::make('filename')->searchable(['filename', 'original_name', 'alt'])->weight('medium')->limit(28),
                    TextColumn::make('alt')->color('gray')->limit(48)->placeholder('Missing alt text'),
                ])->space(2),
            ])
            ->filters([
                TernaryFilter::make('alt')->label('Alt text')->placeholder('All')->trueLabel('Has alt text')->falseLabel('Missing alt text')
                    ->queries(true: fn (Builder $q) => $q->whereNotNull('alt')->where('alt', '!=', ''), false: fn (Builder $q) => $q->where(fn ($q) => $q->whereNull('alt')->orWhere('alt', ''))),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->authorize(fn (Media $record) => auth()->user()->can('delete', $record))
                    ->modalDescription('The file and all its generated sizes will be removed.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
