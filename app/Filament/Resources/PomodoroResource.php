<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PomodoroResource\Pages;
use App\Filament\Resources\PomodoroResource\RelationManagers;
use App\Models\Pomodoro;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class PomodoroResource extends Resource
{
    protected static ?string $model = Pomodoro::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Pomodoro Sessions';

    protected static ?string $modelLabel = 'Pomodoro Session';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hidden user_id field kaldırıldı - şimdilik kullanmayacağız

                Forms\Components\Section::make('Session Details')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Session Type')
                                    ->options([
                                        'work' => 'Work Session (25 min)',
                                        'short_break' => 'Short Break (5 min)',
                                        'long_break' => 'Long Break (15 min)',
                                        'custom' => 'Custom Duration',
                                    ])
                                    ->required()
                                    ->default('work')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        match ($state) {
                                            'work' => $set('duration_seconds', 25 * 60),
                                            'short_break' => $set('duration_seconds', 5 * 60),
                                            'long_break' => $set('duration_seconds', 15 * 60),
                                            default => $set('duration_seconds', null),
                                        };
                                    }),

                                Forms\Components\TextInput::make('duration_seconds')
                                    ->label('Duration (seconds)')
                                    ->numeric()
                                    ->required()
                                    ->default(25 * 60)
                                    ->suffix('seconds')
                                    ->helperText('25 min = 1500 seconds')
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'custom'),
                            ]),
                    ]),

                Forms\Components\Section::make('Session Status')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'not_started' => 'Not Started',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                        'paused' => 'Paused',
                                    ])
                                    ->required()
                                    ->default('not_started')
                                    ->live(),

                                Forms\Components\Select::make('priority')
                                    ->label('Priority Level')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'urgent' => 'Urgent',
                                    ])
                                    ->default('medium'), // Default değer eklendi
                            ]),
                    ]),

                Forms\Components\Section::make('Timing')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('started_at')
                                    ->label('Started At')
                                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['in_progress', 'completed', 'paused'])),

                                Forms\Components\DateTimePicker::make('ended_at')
                                    ->label('Ended At')
                                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                            ]),
                    ]),

                Forms\Components\Section::make('Additional Information')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->placeholder('Add tags (e.g., coding, reading, meeting)')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('project_name')
                            ->label('Project/Task Name')
                            ->placeholder('e.g., Website Development, Study Math'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getEloquentQuery())
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'work',
                        'success' => 'short_break',
                        'info' => 'long_break',
                        'warning' => 'custom',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'work' => 'Work',
                        'short_break' => 'Short Break',
                        'long_break' => 'Long Break',
                        'custom' => 'Custom',
                        default => ucfirst($state),
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->getStateUsing(fn ($record) => sprintf(
                        '%02d:%02d',
                        intdiv($record->duration_seconds ?? 0, 60),
                        ($record->duration_seconds ?? 0) % 60
                    ))
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'not_started',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'info' => 'paused',
                    ])
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priority')
                    ->colors([
                        'gray' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('project_name')
                    ->label('Project Name')
                    ->searchable()
                    ->toggleable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
                    ->limit(2)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('M j, H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Ended')
                    ->dateTime('M j, H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('actual_duration')
                    ->label('Actual Time')
                    ->getStateUsing(function ($record) {
                        if ($record->started_at && $record->ended_at) {
                            $diff = $record->started_at->diffInMinutes($record->ended_at);
                            return sprintf('%d min', $diff);
                        }
                        return '-';
                    })
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'paused' => 'Paused',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'work' => 'Work Session',
                        'short_break' => 'Short Break',
                        'long_break' => 'Long Break',
                        'custom' => 'Custom',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low Priority',
                        'medium' => 'Medium Priority',
                        'high' => 'High Priority',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),

                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),

                Tables\Filters\Filter::make('completed_sessions')
                    ->label('Completed Only')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'completed')),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('start_session')
                        ->label('Start')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'in_progress',
                                'started_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Session Started!')
                                ->body('Pomodoro session has been started.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === 'not_started'),

                    Action::make('pause_session')
                        ->label('Pause')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(function ($record) {
                            $record->update(['status' => 'paused']);

                            Notification::make()
                                ->title('Session Paused')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === 'in_progress'),

                    Action::make('resume_session')
                        ->label('Resume')
                        ->icon('heroicon-o-play')
                        ->color('info')
                        ->action(function ($record) {
                            $record->update(['status' => 'in_progress']);

                            Notification::make()
                                ->title('Session Resumed')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === 'paused'),

                    Action::make('complete_session')
                        ->label('Complete')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'completed',
                                'ended_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Session Completed!')
                                ->body('Great job! Session completed successfully.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => in_array($record->status, ['in_progress', 'paused'])),

                    Action::make('cancel_session')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'cancelled',
                                'ended_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Session Cancelled')
                                ->warning()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Session')
                        ->modalDescription('Are you sure you want to cancel this session?')
                        ->visible(fn ($record) => in_array($record->status, ['in_progress', 'paused', 'not_started'])),

                    Action::make('duplicate')
                        ->label('Duplicate Session')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function ($record) {
                            $newSession = $record->replicate();
                            $newSession->status = 'not_started';
                            $newSession->started_at = null;
                            $newSession->ended_at = null;
                            $newSession->save();

                            Notification::make()
                                ->title('Session Duplicated')
                                ->body('New session created with same settings.')
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),

                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->headerActions([
                Tables\Actions\Action::make('quick_work_session')
                    ->label('Quick Work Session')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function () {
                        $session = Pomodoro::create([
                            'type' => 'work',
                            'duration_seconds' => 25 * 60,
                            'status' => 'in_progress',
                            'started_at' => now(),
                            'priority' => 'medium',
                        ]);

                        Notification::make()
                            ->title('Quick Work Session Started!')
                            ->body('25-minute work session is now running.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('quick_break')
                    ->label('Quick Break')
                    ->icon('heroicon-o-pause')
                    ->color('info')
                    ->action(function () {
                        $session = Pomodoro::create([
                            'type' => 'short_break',
                            'duration_seconds' => 5 * 60,
                            'status' => 'in_progress',
                            'started_at' => now(),
                            'priority' => 'low',
                        ]);

                        Notification::make()
                            ->title('Quick Break Started!')
                            ->body('5-minute break session is now running.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_complete')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'completed',
                                    'ended_at' => now(),
                                ]);
                                $count++;
                            }

                            Notification::make()
                                ->title('Sessions Updated')
                                ->body("{$count} sessions marked as completed.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Mark sessions as completed')
                        ->modalDescription('Are you sure you want to mark the selected sessions as completed?')
                        ->modalSubmitActionLabel('Yes, complete them'),

                    Tables\Actions\BulkAction::make('bulk_cancel')
                        ->label('Cancel Sessions')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'cancelled',
                                    'ended_at' => now(),
                                ]);
                                $count++;
                            }

                            Notification::make()
                                ->title('Sessions Cancelled')
                                ->body("{$count} sessions have been cancelled.")
                                ->warning()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Cancel sessions')
                        ->modalDescription('Are you sure you want to cancel the selected sessions?')
                        ->modalSubmitActionLabel('Yes, cancel them'),

                    Tables\Actions\BulkAction::make('export_sessions')
                        ->label('Export to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            return static::exportSessionsToCSV($records);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    /**
     * User filtering kaldırıldı - şimdilik tüm kayıtları göster
     */
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->where('user_id', auth()->id());
    // }

    /**
     * Export sessions to CSV
     */
    public static function exportSessionsToCSV($records)
    {
        $filename = 'pomodoro-sessions-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Type', 'Duration', 'Status', 'Priority', 'Project', 'Started At',
                'Ended At', 'Actual Duration', 'Tags'
            ]);

            foreach ($records as $record) {
                $actualDuration = '';
                if ($record->started_at && $record->ended_at) {
                    $actualDuration = $record->started_at->diffInMinutes($record->ended_at) . ' min';
                }

                fputcsv($handle, [
                    $record->type,
                    sprintf('%d min', intdiv($record->duration_seconds ?? 0, 60)),
                    $record->status,
                    $record->priority ?? '',
                    $record->project_name ?? '',
                    $record->started_at?->format('Y-m-d H:i:s'),
                    $record->ended_at?->format('Y-m-d H:i:s'),
                    $actualDuration,
                    is_array($record->tags) ? implode(', ', $record->tags) : ($record->tags ?? ''),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
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
