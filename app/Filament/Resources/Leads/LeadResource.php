<?php

namespace App\Filament\Resources\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * Website form submissions. Phone-call leads are tracked by the call-tracking provider (MarketCall) and are not
 * shown here, because no provider API integration exists yet.
 */
class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Leads';

    protected static ?string $navigationLabel = 'Form leads';

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::query()->where('status', LeadStatus::New)->count();

        return $count ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make('Request')->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name'),
                        TextEntry::make('phone')->formatStateUsing(fn ($state) => \App\Support\Phone::format($state))
                            ->url(fn (Lead $r) => \App\Support\Phone::tel($r->phone)),
                        TextEntry::make('email')->placeholder('—')->url(fn (Lead $r) => $r->email ? 'mailto:'.$r->email : null),
                        TextEntry::make('service_label')->label('Service')->placeholder('Not specified'),
                        TextEntry::make('location')->state(fn (Lead $r) => collect([$r->city_name, $r->state?->name, $r->zip])->filter()->implode(', ')),
                        TextEntry::make('created_at')->label('Received')->dateTime('M j, Y g:ia'),
                    ]),
                    TextEntry::make('message')->placeholder('No message')->columnSpanFull(),
                ]),
                Section::make('Source attribution')->collapsible()->collapsed()->columns(2)->schema([
                    TextEntry::make('landing_page')->placeholder('—'),
                    TextEntry::make('referrer')->placeholder('—'),
                    TextEntry::make('utm_source')->placeholder('—'),
                    TextEntry::make('utm_medium')->placeholder('—'),
                    TextEntry::make('utm_campaign')->placeholder('—'),
                    TextEntry::make('gclid')->label('Google click ID')->placeholder('—'),
                    TextEntry::make('ip_address')->label('IP address'),
                    TextEntry::make('consent')->formatStateUsing(fn ($state) => $state ? 'Agreed to be contacted' : 'No'),
                ]),
            ]),
            Section::make('Follow-up')->columnSpan(['lg' => 1])->schema([
                Select::make('status')->options(LeadStatus::class)->required()->native(false),
                Textarea::make('internal_notes')->rows(8)->maxLength(5000)->helperText('Visible to staff only.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('M j, g:ia')->sortable(),
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('phone')->searchable()->formatStateUsing(fn ($state) => \App\Support\Phone::format($state)),
                TextColumn::make('service_label')->label('Service')->placeholder('—')->toggleable(),
                TextColumn::make('location')->state(fn (Lead $r) => collect([$r->city_name, $r->state?->code, $r->zip])->filter()->implode(', ')),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('utm_source')->label('Source')->placeholder('Direct')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(LeadStatus::class)->multiple(),
                SelectFilter::make('service')->relationship('service', 'title'),
                SelectFilter::make('state')->relationship('state', 'name')->searchable(),
                Filter::make('received')->schema([DatePicker::make('from'), DatePicker::make('until')])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([EditAction::make()->label('Open')])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('setStatus')->label('Change status')->icon(Heroicon::OutlinedTag)
                    ->schema([Select::make('status')->options(LeadStatus::class)->required()])
                    ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('export')->label('Export CSV')->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (Collection $records) => response()->streamDownload(function () use ($records) {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['Received', 'Name', 'Phone', 'Email', 'Service', 'City', 'State', 'ZIP', 'Status', 'Message', 'UTM source', 'UTM campaign']);
                        foreach ($records->load('state') as $l) {
                            // Prefix cells that spreadsheets would treat as formulas.
                            $safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;
                            fputcsv($out, array_map($safe, [$l->created_at, $l->name, $l->phone, $l->email, $l->service_label, $l->city_name, $l->state?->code, $l->zip, $l->status->value, $l->message, $l->utm_source, $l->utm_campaign]));
                        }
                        fclose($out);
                    }, 'leads-'.now()->format('Y-m-d').'.csv')),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('state');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'edit' => Pages\EditLead::route('/{record}'),
        ];
    }
}
