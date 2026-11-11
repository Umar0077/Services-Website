<?php

namespace App\Filament\Resources\TrackingNumbers;

use App\Models\TrackingNumber;
use App\Support\Phone;
use BackedEnum;
use Filament\Actions\EditAction;
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

/**
 * Approved call-tracking numbers. A landing-page link like /call-hvac?pn=google-ads shows that campaign's number.
 * Numbers that are not on this list can never appear on the site, whatever the URL says.
 */
class TrackingNumberResource extends Resource
{
    protected static ?string $model = TrackingNumber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = 'SEO & tracking';

    protected static ?string $navigationLabel = 'Tracking numbers';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->description('Only numbers listed here can replace the default phone number via a campaign link.')->schema([
                TextInput::make('label')->required()->maxLength(100)->placeholder('Google Ads — Summer AC'),
                TextInput::make('code')->label('Link code')->required()->maxLength(40)->unique(ignoreRecord: true)
                    ->regex('/^[a-z0-9-]+$/')->helperText(fn (?string $state) => 'Use links like '.url('/call-hvac').'?pn='.($state ?: 'code')),
                TextInput::make('number')->label('Phone number')->required()->tel()
                    ->rule('regex:/^\D*1?\D*(\d\D*){10}$/')->helperText('10-digit U.S. number from your call-tracking provider.')
                    ->formatStateUsing(fn (?string $state) => $state ? Phone::format($state) : null),
                TextInput::make('source')->maxLength(100)->placeholder('MarketCall campaign name'),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->searchable()->weight('medium'),
                TextColumn::make('code')->badge()->color('gray')->copyable(),
                TextColumn::make('number')->formatStateUsing(fn ($state) => Phone::format($state)),
                TextColumn::make('source')->placeholder('—'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrackingNumbers::route('/'),
            'create' => Pages\CreateTrackingNumber::route('/create'),
            'edit' => Pages\EditTrackingNumber::route('/{record}/edit'),
        ];
    }
}
