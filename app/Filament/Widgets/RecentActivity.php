<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivity extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent content activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLog::query()->with('user')->where('action', '!=', 'login')->latest('created_at')->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')->label('When')->since(),
                TextColumn::make('user.name')->label('User')->placeholder('System'),
                TextColumn::make('action')->badge(),
                TextColumn::make('subject_type')->label('Type'),
                TextColumn::make('description')->limit(60),
            ])
            ->emptyStateHeading('No changes yet');
    }
}
