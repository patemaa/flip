<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PomodoroResource\Pages;
use App\Filament\Resources\PomodoroResource\RelationManagers;
use App\Models\Pomodoro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PomodoroResource extends Resource
{
    protected static ?string $model = Pomodoro::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('duration_seconds'),
                Forms\Components\Select::make('status')
                    ->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('not_started'),
                Forms\Components\DateTimePicker::make('started_at'),
                Forms\Components\DateTimePicker::make('ended_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->getStateUsing(fn ($record) => sprintf(
                        '%02d:%02d:%02d',
                        intdiv($record->duration_seconds, 3600),
                        intdiv($record->duration_seconds % 3600, 60),
                        $record->duration_seconds % 60
                    ))
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'not_started',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'not_started' => 'Not Started',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->color('success')
                    ->action(function ($record) {
                        $nextStatus = match ($record->status) {
                            'not_started' => 'in_progress',
                            'in_progress' => 'completed',
                            'cancelled' => 'not_started',
                            default => $record->status,
                        };

                        if ($record->status !== $nextStatus) {
                            $record->update(['status' => $nextStatus]);
                        }
                    })
                    ->icon('heroicon-o-forward')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'completed'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPomodoros::route('/'),
            'create' => Pages\CreatePomodoro::route('/create'),
            'edit' => Pages\EditPomodoro::route('/{record}/edit'),
        ];
    }
}
