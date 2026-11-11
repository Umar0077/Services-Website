<?php

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Resources\Cities\CityResource;
use App\Models\City;
use App\Models\State;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalDescription('Columns: state_code, name, intro (optional), local_info (optional). Imported cities are created unpublished and unverified so each one can be reviewed first. Existing cities are skipped.')
                ->schema([
                    FileUpload::make('file')->label('CSV file')->required()->disk('local')->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])->maxSize(2048),
                ])
                ->action(function (array $data) {
                    $path = Storage::disk('local')->path($data['file']);
                    $handle = fopen($path, 'r');
                    $header = array_map(fn ($h) => Str::snake(trim((string) $h)), fgetcsv($handle) ?: []);
                    $states = State::query()->pluck('id', 'code');
                    $created = 0;
                    $skipped = 0;
                    $errors = [];
                    $line = 1;

                    while (($row = fgetcsv($handle)) !== false && $line < 2001) {
                        $line++;
                        if (count($row) !== count($header)) {
                            $errors[] = "Line {$line}: wrong number of columns";

                            continue;
                        }
                        $r = array_combine($header, array_map('trim', $row));
                        $v = Validator::make($r, [
                            'state_code' => ['required', 'size:2'],
                            'name' => ['required', 'string', 'max:100'],
                            'intro' => ['nullable', 'string', 'max:600'],
                            'local_info' => ['nullable', 'string', 'max:5000'],
                        ]);
                        $stateId = $states[strtoupper($r['state_code'] ?? '')] ?? null;
                        if ($v->fails() || ! $stateId) {
                            $errors[] = "Line {$line}: ".($stateId ? $v->errors()->first() : 'unknown state code');

                            continue;
                        }
                        $slug = Str::slug($r['name']);
                        if (City::query()->where('state_id', $stateId)->where('slug', $slug)->exists()) {
                            $skipped++;

                            continue;
                        }
                        City::query()->create([
                            'state_id' => $stateId,
                            'name' => strip_tags($r['name']),
                            'slug' => $slug,
                            'intro' => strip_tags($r['intro'] ?? '') ?: null,
                            'local_info' => filled($r['local_info'] ?? null) ? '<p>'.e($r['local_info']).'</p>' : null,
                            'is_published' => false,
                            'service_verified' => false,
                        ]);
                        $created++;
                    }
                    fclose($handle);
                    Storage::disk('local')->delete($data['file']);

                    Notification::make()
                        ->title("Imported {$created} cities".($skipped ? ", skipped {$skipped} existing" : ''))
                        ->body($errors ? implode("\n", array_slice($errors, 0, 8)).(count($errors) > 8 ? "\n…" : '') : null)
                        ->{$errors ? 'warning' : 'success'}()
                        ->persistent()
                        ->send();
                }),
            CreateAction::make()->label('New city'),
        ];
    }
}
