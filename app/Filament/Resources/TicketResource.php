<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Services\MikroTikService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $slug = 'tickets';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user_name')
                    ->required(),

                Select::make('issue_type')
                    ->options([
                    'internet' => 'Internet Issue',
                    'router' => 'Router Issue',
                    'billing' => 'Billing Issue',
                    ])
                    ->required()
                    ->label('Issue Type'),

                TextInput::make('description')
                    ->required(),

                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                    ])
                    ->default('open')
                    ->label('Status'),

                TextInput::make('mikrotik_user'),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Ticket $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?Ticket $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_name')
                    ->searchable(),

                TextColumn::make('issue_type'),

                TextColumn::make('description'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('mikrotik_user'),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                EditAction::make()->button(),
                DeleteAction::make()->button(),
                RestoreAction::make()->button(),
                ForceDeleteAction::make()->button(),
                Action::make('disconnect')
                    ->button()
                    ->label('Disconnect User')
                    ->action(function (Ticket $record) {
                        if ($record->mikrotik_user) {
                            $mikrotik = new MikroTikService();
                            $result = $mikrotik->disconnectUser($record->mikrotik_user);

                            if ($result) {
                                Filament\Notifications\Notification::make()
                                    ->title('User Disconnected')
                                    ->success()
                                    ->send();
                            } else {
                                Filament\Notifications\Notification::make()
                                    ->title('Failed to Disconnect User')
                                    ->danger()
                                    ->send();
                            }
                        }
                    })
                    ->visible(fn ($record) => !empty($record->mikrotik_user)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [

        ];
    }
}
