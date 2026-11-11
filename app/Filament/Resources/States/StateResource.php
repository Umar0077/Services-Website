<?php

namespace App\Filament\Resources\States;

use App\Filament\Support\Fields;
use App\Models\State;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false; // all 50 states are seeded
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')->required()->maxLength(60),
                        TextInput::make('code')->disabled()->dehydrated(false),
                        Fields::slug('/locations/'),
                    ]),
                    TextInput::make('heading')->label('Page heading (H1)')->maxLength(150),
                    Textarea::make('intro')->rows(3)->maxLength(600)->helperText('State-specific introduction. Required before the page can be indexed.'),
                    Fields::rich('climate', 'Climate considerations'),
                    Fields::rich('body', 'Additional local content'),
                ]),
                Section::make('FAQs')->collapsible()->collapsed()->schema([Fields::faqs()]),
                Fields::seo('/locations/', 'heading', 'intro'),
                Fields::targetingReview(['heading', 'intro', 'climate', 'body', 'faqs', 'seo_title', 'seo_description']),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Publishing & coverage')->schema([
                    Toggle::make('is_published')->label('Published'),
                    Toggle::make('coverage_verified')->label('Provider coverage verified')
                        ->helperText('Turn on only after confirming the network actually serves this state. Unverified pages are shown but never indexed or listed in the sitemap.'),
                    Toggle::make('is_featured')->label('Featured'),
                    Callout::make('Indexing status')
                        ->description(fn (?State $record) => $record?->isIndexable() ? 'Indexable and included in the sitemap.' : 'Not indexed: needs to be published, coverage verified, an introduction, and indexing allowed.')
                        ->color(fn (?State $record) => $record?->isIndexable() ? 'success' : 'warning'),
                ]),
                Section::make('Services offered')->schema([
                    Select::make('services')->relationship('services', 'title')->multiple()->preload()->hiddenLabel()
                        ->helperText('Services available through providers in this state.'),
                ]),
                Section::make('Hero image')->schema([Fields::media('hero_media_id')->hiddenLabel()]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $bulk = fn (string $name, string $label, array $values, $icon) => BulkAction::make($name)->label($label)->icon($icon)
            ->requiresConfirmation()->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records) => $records->each->update($values));

        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium')->description(fn (State $r) => '/locations/'.$r->slug),
                TextColumn::make('code')->badge()->color('gray'),
                IconColumn::make('is_published')->boolean()->label('Live'),
                IconColumn::make('coverage_verified')->boolean()->label('Verified'),
                TextColumn::make('cities_count')->counts('cities')->label('Cities')->sortable(),
                TextColumn::make('intro')->label('Content')->formatStateUsing(fn ($state) => str_word_count(strip_tags((string) $state)).' words')->color('gray'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('coverage_verified')->label('Coverage verified'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([
                $bulk('publish', 'Publish', ['is_published' => true], Heroicon::OutlinedEye),
                $bulk('unpublish', 'Unpublish', ['is_published' => false], Heroicon::OutlinedEyeSlash),
                $bulk('verify', 'Mark coverage verified', ['coverage_verified' => true], Heroicon::OutlinedCheckBadge),
                $bulk('unverify', 'Mark coverage unverified', ['coverage_verified' => false], Heroicon::OutlinedXCircle),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStates::route('/'),
            'edit' => Pages\EditState::route('/{record}/edit'),
        ];
    }
}
