<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestLeads extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent form submissions';

    public static function canView(): bool
    {
        return auth()->user()->can('viewAny', Lead::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Lead::query()->with('state')->latest()->limit(6))
            ->paginated(false)
            ->recordUrl(fn (Lead $r) => LeadResource::getUrl('edit', ['record' => $r]))
            ->columns([
                TextColumn::make('created_at')->label('Received')->since(),
                TextColumn::make('name'),
                TextColumn::make('service_label')->label('Service')->placeholder('—'),
                TextColumn::make('location')->state(fn (Lead $r) => collect([$r->city_name, $r->state?->code, $r->zip])->filter()->implode(', ')),
                TextColumn::make('status')->badge(),
            ])
            ->emptyStateHeading('No form submissions yet');
    }
}
