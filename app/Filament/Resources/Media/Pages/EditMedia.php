<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->authorize(fn (Media $record) => auth()->user()->can('delete', $record))
                ->before(function (DeleteAction $action, Media $record) {
                    $usage = $record->usageCount();
                    if ($usage > 0) {
                        Notification::make()
                            ->title('Cannot delete image in use')
                            ->body("“{$record->filename}” is referenced by {$usage} item(s). Remove it from all content before deleting.")
                            ->danger()
                            ->send();
                        $action->halt();
                    }
                }),
        ];
    }
}
