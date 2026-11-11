<?php

namespace App\Filament\Resources\Services;

use App\Filament\Support\Fields;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    public const ICONS = [
        'snowflake' => 'Snowflake (cooling)', 'wind' => 'Airflow', 'refresh' => 'Replacement', 'flame' => 'Flame (heating)',
        'thermometer' => 'Thermometer', 'layers' => 'Ductless / layers', 'air-vent' => 'Air vent', 'leaf' => 'Leaf (air quality)',
        'wrench' => 'Wrench (maintenance)', 'siren' => 'Siren (emergency)', 'home' => 'Home',
    ];

    public static function form(Schema $schema): Schema
    {
        $pairs = fn (string $name, string $label, string $add) => Repeater::make($name)->label($label)->schema([
            TextInput::make('title')->required()->maxLength(100),
            Textarea::make('text')->required()->rows(2)->maxLength(400),
        ])->itemLabel(fn (array $state) => $state['title'] ?? null)->collapsible()->collapsed()->addActionLabel($add)->defaultItems(0);

        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    Grid::make(2)->schema([
                        Fields::title(),
                        TextInput::make('nav_label')->label('Short name (menus)')->maxLength(40),
                    ]),
                    Fields::slug('/services/'),
                    Textarea::make('short_description')->required()->rows(2)->maxLength(300)
                        ->helperText('One or two sentences for cards and listings. Describe the homeowner problem it solves.'),
                ]),
                Section::make('Hero')->collapsible()->schema([
                    TextInput::make('hero_heading')->label('Page heading (H1)')->maxLength(150),
                    Textarea::make('hero_text')->rows(2)->maxLength(400),
                ]),
                Section::make('Page content')->collapsible()->schema([
                    Fields::rich('overview', 'Service overview'),
                    $pairs('problems', 'Common customer problems', 'Add problem'),
                    $pairs('options', 'Service options', 'Add option'),
                    Repeater::make('signs')->label('Signs professional help may be needed')->simple(TextInput::make('text')->required()->maxLength(200))
                        ->addActionLabel('Add sign')->defaultItems(0),
                    Fields::rich('details', 'Additional information'),
                ]),
                Section::make('FAQs')->collapsible()->collapsed()->schema([Fields::faqs()]),
                Fields::seo('/services/'),
                Fields::targetingReview(['title', 'short_description', 'hero_heading', 'hero_text', 'overview', 'problems', 'options', 'signs', 'details', 'faqs', 'seo_title', 'seo_description']),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Publishing')->schema([
                    Toggle::make('is_published')->label('Published'),
                    Toggle::make('is_featured')->label('Featured on the homepage'),
                    Select::make('group')->options(Service::GROUPS)->required()->native(false),
                    Select::make('icon')->options(self::ICONS)->required()->native(false),
                    TextInput::make('sort_order')->numeric()->default(0),
                ]),
                Section::make('Hero image')->schema([Fields::media('hero_media_id')->hiddenLabel()]),
                Section::make('Related')->schema([
                    Select::make('relatedServices')->relationship('relatedServices', 'title', fn ($query, ?Service $record) => $query->when($record, fn ($q) => $q->where('services.id', '!=', $record->id)))
                        ->multiple()->preload()->maxItems(3)->label('Related services'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('heroMedia.path')->label('')->disk('public')->imageSize(44)->square(),
                TextColumn::make('title')->searchable()->weight('medium')->description(fn (Service $r) => '/services/'.$r->slug),
                TextColumn::make('group')->badge()->formatStateUsing(fn ($state) => Service::GROUPS[$state] ?? $state),
                IconColumn::make('is_published')->boolean()->label('Live'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
                TextColumn::make('updated_at')->since()->label('Updated')->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')->options(Service::GROUPS),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('publish')->icon(Heroicon::OutlinedEye)->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['is_published' => true])),
                BulkAction::make('unpublish')->icon(Heroicon::OutlinedEyeSlash)->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['is_published' => false])),
            ])]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('heroMedia');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
