<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')->icon(Heroicon::OutlinedEye)->color('gray')
                ->url(fn (Post $record) => PostResource::previewUrl($record))->openUrlInNewTab(),
            Action::make('view')->label('View live')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                ->visible(fn (Post $record) => $record->isPubliclyVisible())
                ->url(fn (Post $record) => $record->publicUrl())->openUrlInNewTab(),
            Action::make('publish')->label('Publish now')->icon(Heroicon::OutlinedCheckCircle)->color('success')
                ->visible(fn (Post $record) => $record->status !== PostStatus::Published && auth()->user()->can('publish', Post::class))
                ->requiresConfirmation()
                ->action(function (Post $record) {
                    $record->update(['status' => PostStatus::Published, 'published_at' => now()]);
                    $this->refreshFormData(['status', 'published_at']);
                    Notification::make()->title('Published')->success()->send();
                }),
            Action::make('unpublish')->label('Unpublish')->icon(Heroicon::OutlinedPauseCircle)->color('warning')
                ->visible(fn (Post $record) => $record->status !== PostStatus::Draft && auth()->user()->can('publish', Post::class))
                ->requiresConfirmation()
                ->action(function (Post $record) {
                    $record->update(['status' => PostStatus::Draft]);
                    $this->refreshFormData(['status']);
                    Notification::make()->title('Moved to draft')->success()->send();
                }),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
