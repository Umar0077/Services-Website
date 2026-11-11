<?php

namespace App\Filament\Resources\Users;

use App\Enums\Role;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    /** Roles the current user may assign: never above their own. */
    public static function assignableRoles(): array
    {
        $me = auth()->user();

        return collect(Role::cases())
            ->filter(fn (Role $r) => $me && $r->rank() <= $me->role->rank())
            ->mapWithKeys(fn (Role $r) => [$r->value => $r->getLabel()])
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(100),
                TextInput::make('email')->email()->required()->maxLength(150)->unique(ignoreRecord: true),
                Select::make('role')->options(fn () => self::assignableRoles())->required()->native(false)
                    ->in(fn () => array_keys(self::assignableRoles()))
                    ->disabled(fn (?User $record) => $record?->is(auth()->user()))
                    ->helperText('Super Admin: everything · Admin: everything except super admins · Editor: all content and leads · Author: own draft posts'),
                Toggle::make('is_active')->label('Can sign in')->default(true)->inline(false)
                    ->disabled(fn (?User $record) => $record?->is(auth()->user())),
                TextInput::make('password')->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->rule(Password::min(12)->mixedCase()->numbers()->uncompromised())
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep the current password.' : 'At least 12 characters with upper and lower case letters and a number.'),
                TextInput::make('job_title')->maxLength(100)->helperText('Shown on blog bylines.'),
                Textarea::make('bio')->rows(3)->maxLength(600)->columnSpanFull()->helperText('Shown under articles. Do not list credentials that cannot be verified.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')->badge(),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('last_login_at')->since()->placeholder('Never')->sortable(),
            ])
            ->filters([SelectFilter::make('role')->options(Role::class)])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
