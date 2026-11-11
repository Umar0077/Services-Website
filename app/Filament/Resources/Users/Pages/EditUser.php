<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        if (method_exists($this->record, 'publicUrl')) {
            $actions[] = Action::make('view')->label('View page')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                ->visible(fn () => $this->record->isPubliclyVisible())
                ->url(fn () => $this->record->publicUrl())->openUrlInNewTab();
        }
        $actions[] = DeleteAction::make();

        return $actions;
    }
}
