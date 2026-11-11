<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload images')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => auth()->user()->can('create', Media::class))
                ->modalDescription('JPG, PNG, WebP or GIF up to 10 MB. Files are renamed safely and responsive WebP sizes are generated automatically.')
                ->schema([
                    FileUpload::make('files')->label('Images')->multiple()->maxFiles(20)->required()
                        ->image()->acceptedFileTypes(MediaService::ALLOWED_MIME)->maxSize(MediaService::MAX_KB)
                        ->disk('public')->directory('media/tmp')->storeFileNamesIn('original_names'),
                    TextInput::make('alt')->label('Alt text (applied to all)')->maxLength(250)
                        ->helperText('You can edit alt text for each image afterwards.'),
                ])
                ->action(function (array $data) {
                    $service = app(MediaService::class);
                    $count = 0;
                    foreach ($data['files'] as $path) {
                        try {
                            $service->registerStoredFile($path, $data['original_names'][$path] ?? basename($path), ['alt' => $data['alt'] ?? null]);
                            $count++;
                        } catch (\Throwable $e) {
                            Notification::make()->title('Skipped a file')->body($e->getMessage())->danger()->send();
                        }
                    }
                    Notification::make()->title("Uploaded {$count} image(s)")->success()->send();
                }),
        ];
    }
}
