<?php

namespace App\Filament\Resources\Cities;

use App\Filament\Support\Fields;
use App\Models\City;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Locations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    Grid::make(2)->schema([
                        Select::make('state_id')->relationship('state', 'name')->required()->searchable()->preload()->live(),
                        Fields::title('name', 'City name'),
                    ]),
                    Fields::slug('/locations/{state}/', fn (Unique $rule, Get $get) => $rule->where('state_id', $get('state_id'))),
                    TextInput::make('heading')->label('Page heading (H1)')->maxLength(150),
                    Textarea::make('intro')->rows(3)->maxLength(600)->live(onBlur: true),
                    Fields::rich('local_info', 'Local HVAC information')->live(onBlur: true)
                        ->helperText('Write about this city specifically — climate, housing, common equipment, local rules. Pages that only swap the city name in are doorway pages and must not be published.'),
                    Fields::rich('body', 'Additional content'),
                ]),
                Section::make('FAQs')->collapsible()->collapsed()->schema([Fields::faqs()]),
                Fields::seo('/locations/', 'heading', 'intro'),
                Fields::targetingReview(['name', 'heading', 'intro', 'local_info', 'body', 'faqs', 'seo_title', 'seo_description']),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Publishing & availability')->schema([
                    Toggle::make('is_published')->label('Published'),
                    Toggle::make('service_verified')->label('Service availability verified')
                        ->helperText('Only after confirming providers actually serve this city.'),
                    Toggle::make('is_featured')->label('Popular city (homepage & menus)'),
                    Callout::make('Unique local content')
                        ->description(function (Get $get) {
                            $words = str_word_count(strip_tags($get('intro').' '.Fields::toText($get('local_info'))));

                            return "{$words} words of city-specific content. At least 120 are needed before the page can be indexed.";
                        })
                        ->color(fn (Get $get) => str_word_count(strip_tags($get('intro').' '.Fields::toText($get('local_info')))) >= 120 ? 'success' : 'warning'),
                ]),
                Section::make('Services')->schema([
                    Select::make('services')->relationship('services', 'title')->multiple()->preload()->hiddenLabel(),
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
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('state.name')->label('State')->sortable()->searchable(),
                IconColumn::make('is_published')->boolean()->label('Live'),
                IconColumn::make('service_verified')->boolean()->label('Verified'),
                IconColumn::make('is_featured')->boolean()->label('Popular'),
                TextColumn::make('updated_at')->since()->label('Updated')->sortable(),
            ])
            ->filters([
                SelectFilter::make('state')->relationship('state', 'name')->searchable()->preload(),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('service_verified')->label('Service verified'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([
                $bulk('publish', 'Publish', ['is_published' => true], Heroicon::OutlinedEye),
                $bulk('unpublish', 'Unpublish', ['is_published' => false], Heroicon::OutlinedEyeSlash),
                $bulk('verify', 'Mark service verified', ['service_verified' => true], Heroicon::OutlinedCheckBadge),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('state');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
