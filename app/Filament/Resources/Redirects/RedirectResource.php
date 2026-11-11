<?php

namespace App\Filament\Resources\Redirects;

use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'SEO & tracking';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->description('Slug changes create redirects automatically. Add manual ones here for old URLs.')->schema([
                TextInput::make('from_path')->label('Old path')->required()->maxLength(250)->prefix('/')
                    ->unique(ignoreRecord: true)
                    ->regex('#^/?[^\s?]*$#')->helperText('Path only, e.g. old-page.html')
                    ->dehydrateStateUsing(fn ($state) => Redirect::normalize((string) $state)),
                TextInput::make('to_path')->label('New path or URL')->required()->maxLength(250)
                    ->rule(fn () => function ($attribute, $value, $fail) {
                        if (! str_starts_with($value, '/') && ! preg_match('#^https://#', $value)) {
                            $fail('Use a path starting with / or a full https:// URL.');
                        }
                    }),
                Select::make('status_code')->options([301 => '301 — Permanent', 302 => '302 — Temporary'])->default(301)->required(),
                Toggle::make('is_active')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('from_path')->searchable()->weight('medium'),
                TextColumn::make('to_path')->searchable(),
                TextColumn::make('status_code')->badge()->color('gray'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('hits')->sortable(),
                TextColumn::make('last_hit_at')->since()->placeholder('Never')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
