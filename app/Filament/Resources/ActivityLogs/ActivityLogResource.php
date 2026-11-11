<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Models\ActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/** Read-only audit trail of sign-ins and content changes. */
class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Activity log';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            // The log grows without limit: simple "Previous / Next" paging avoids counting every row on each page.
            ->paginationMode(\Filament\Tables\Enums\PaginationMode::Simple)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime('M j, Y g:ia')->sortable(),
                TextColumn::make('user.name')->label('User')->placeholder('System / visitor'),
                TextColumn::make('action')->badge()->color(fn (string $state) => match ($state) {
                    'deleted' => 'danger', 'created' => 'success', 'login' => 'gray', default => 'info',
                }),
                TextColumn::make('subject_type')->label('Type'),
                TextColumn::make('description')->limit(50)->searchable(),
                TextColumn::make('properties')->label('Changed fields')->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state['fields'] ?? []) : '')->limit(60)->toggleable(),
                TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')->options(fn () => ActivityLog::query()->distinct()->pluck('action', 'action')->all()),
                SelectFilter::make('subject_type')->label('Type')->options(fn () => ActivityLog::query()->whereNotNull('subject_type')->distinct()->pluck('subject_type', 'subject_type')->all()),
                SelectFilter::make('user')->relationship('user', 'name'),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListActivityLogs::route('/')];
    }
}
