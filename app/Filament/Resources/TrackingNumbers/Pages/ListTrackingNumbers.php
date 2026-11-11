<?php

namespace App\Filament\Resources\TrackingNumbers\Pages;

use App\Filament\Resources\TrackingNumbers\TrackingNumberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrackingNumbers extends ListRecords
{
    protected static string $resource = TrackingNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New tracking number')];
    }
}
