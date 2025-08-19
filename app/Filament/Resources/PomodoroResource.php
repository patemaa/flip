<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PomodoroResource\Pages;
use App\Filament\Resources\PomodoroResource\RelationManagers;
use App\Models\Pomodoro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PomodoroResource extends Resource
{
    protected static ?string $model = Pomodoro::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Pomodoro Sessions';

    protected static ?string $modelLabel = 'Pomodoro Session';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Session Details')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\Grid::make()
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
                        Forms\Components\Grid::make()
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
                                    ->default('medium'),
                            ]),
                    ]),

                Forms\Components\Section::make('Timing')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\Grid::make()
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
            ->columns([
                Tables\Columns\TextColumn::make('expected_end_at')
                    ->label('Expected End')
                    ->dateTime('H:i:s')
                    ->placeholder('Not started')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('remaining_seconds')
                    ->label('Remaining')
                    ->getStateUsing(function ($record) {
                        if (!$record || !method_exists($record, 'getFormattedRemainingTimeAttribute')) {
                            return '-';
                        }
                        return $record->formatted_remaining_time ?? '-';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record || !method_exists($record, 'getRemainingSecondsAttribute')) {
                            return 'gray';
                        }

                        $remaining = $record->remaining_seconds ?? 0;
                        return match (true) {
                            $remaining <= 60 => 'danger',
                            $remaining <= 300 => 'warning',
                            default => 'success'
                        };
                    })
                    ->visible(fn ($record) => $record && in_array($record->status ?? '', ['in_progress', 'paused'])),

                Tables\Columns\TextColumn::make('accumulated_seconds')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => $record ? gmdate('i:s', $record->accumulated_seconds ?? 0) : '-')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'work',
                        'success' => 'short_break',
                        'info' => 'long_break',
                        'warning' => 'custom',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
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
                        if (!$record || !$record->started_at || !$record->ended_at) {
                            return '-';
                        }
                        $diff = $record->started_at->diffInMinutes($record->ended_at);
                        return sprintf('%d min', $diff);
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
                            if (!$record) {
                                return;
                            }

                            if (method_exists($record, 'startSession')) {
                                $record->startSession();

                                $endTime = ($record->expected_end_at) ? $record->expected_end_at->format('H:i:s') : 'Unknown';

                                Notification::make()
                                    ->title('Session Started!')
                                    ->body("Will complete at: " . $endTime)
                                    ->success()
                                    ->send();
                            } else {
                                $record->update([
                                    'status' => 'in_progress',
                                    'started_at' => now(),
                                ]);

                                Notification::make()
                                    ->title('Session Started!')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => $record && ($record->status ?? '') === 'not_started'),

                    Action::make('pause_session')
                        ->label('Pause')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(function ($record) {
                            if (!$record) {
                                return;
                            }

                            if (method_exists($record, 'pauseSession')) {
                                $record->pauseSession();

                                Notification::make()
                                    ->title('Session Paused')
                                    ->body("Time accumulated: " . gmdate('i:s', $record->accumulated_seconds ?? 0))
                                    ->warning()
                                    ->send();
                            } else {
                                $record->update(['status' => 'paused']);

                                Notification::make()
                                    ->title('Session Paused')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => $record && ($record->status ?? '') === 'in_progress'),

                    Action::make('resume_session')
                        ->label('Resume')
                        ->icon('heroicon-o-play')
                        ->color('info')
                        ->action(function ($record) {
                            if (method_exists($record, 'resumeSession')) {
                                $record->resumeSession();

                                $endTime = $record->expected_end_at ? $record->expected_end_at->format('H:i:s') : 'Unknown';

                                Notification::make()
                                    ->title('Session Resumed')
                                    ->body("Will complete at: " . $endTime)
                                    ->info()
                                    ->send();
                            } else {
                                $record->update(['status' => 'in_progress']);

                                Notification::make()
                                    ->title('Session Resumed')
                                    ->info()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => $record && ($record->status ?? '') === 'paused'),

                    Action::make('complete_session')
                        ->label('Complete')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($record) {
                            if (method_exists($record, 'completeSession')) {
                                $record->completeSession();
                            } else {
                                $record->update([
                                    'status' => 'completed',
                                    'ended_at' => now(),
                                ]);
                            }

                            Notification::make()
                                ->title('Session Completed!')
                                ->body('Great job! Session completed successfully.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => in_array($record->status, ['in_progress', 'paused'])),

                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->headerActions([
                Action::make('quick_work_session')
                    ->label('Quick Work Session')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function () {
                        $session = Pomodoro::create([
                            'type' => 'work',
                            'duration_seconds' => 25 * 60,
                            'status' => 'not_started',
                            'priority' => 'medium',
                        ]);

                        // Eğer model'de method varsa kullan
                        if (method_exists($session, 'startSession')) {
                            $session->startSession();
                            $endTime = $session->expected_end_at ? $session->expected_end_at->format('H:i:s') : 'Unknown';
                            $body = "25-minute session will complete at: " . $endTime;
                        } else {
                            $session->update([
                                'status' => 'in_progress',
                                'started_at' => now(),
                            ]);
                            $body = "25-minute session started!";
                        }

                        Notification::make()
                            ->title('Work Session Started!')
                            ->body($body)
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Action::make('quick_break')
                    ->label('Quick Break')
                    ->icon('heroicon-o-pause')
                    ->color('info')
                    ->action(function () {
                        $session = Pomodoro::create([
                            'type' => 'short_break',
                            'duration_seconds' => 5 * 60,
                            'status' => 'not_started',
                            'priority' => 'low',
                        ]);

                        // Eğer model'de method varsa kullan
                        if (method_exists($session, 'startSession')) {
                            $session->startSession();
                            $endTime = $session->expected_end_at ? $session->expected_end_at->format('H:i:s') : 'Unknown';
                            $body = "5-minute break will complete at: " . $endTime;
                        } else {
                            $session->update([
                                'status' => 'in_progress',
                                'started_at' => now(),
                            ]);
                            $body = "5-minute break started!";
                        }

                        Notification::make()
                            ->title('Break Started!')
                            ->body($body)
                            ->success()
                            ->persistent()
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
                                if (!$record) {
                                    continue;
                                }
                                if (method_exists($record, 'completeSession')) {
                                    $record->completeSession();
                                } else {
                                    $record->update([
                                        'status' => 'completed',
                                        'ended_at' => now(),
                                    ]);
                                }
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
