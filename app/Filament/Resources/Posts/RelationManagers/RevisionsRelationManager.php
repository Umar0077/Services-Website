<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Models\Post;
use App\Models\PostRevision;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

/** Previous versions of a post. Each content save stores the version it replaced. */
class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revision history';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('title'),
            TextEntry::make('excerpt'),
            TextEntry::make('body')->html()->formatStateUsing(fn (?string $state) => new HtmlString(\App\Support\RichText::render($state)))->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Saved')->dateTime('M j, Y g:ia'),
                TextColumn::make('user.name')->label('Replaced by'),
                TextColumn::make('title')->limit(60),
                TextColumn::make('body')->label('Words')->formatStateUsing(fn (?string $state) => number_format(str_word_count(strip_tags((string) $state)))),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('restore')->label('Restore this version')->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn () => $this->getOwnerRecord() instanceof Post && auth()->user()->can('update', $this->getOwnerRecord()))
                    ->requiresConfirmation()
                    ->modalDescription('The current content is saved as a new revision first, so this can be undone.')
                    ->action(function (PostRevision $record) {
                        /** @var Post $post */
                        $post = $this->getOwnerRecord();
                        $post->restoreRevision($record);
                        Notification::make()->title('Revision restored')->success()->send();
                        $this->redirect(\App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $post]));
                    }),
            ]);
    }
}
