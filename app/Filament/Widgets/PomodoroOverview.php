<?php

namespace App\Filament\Widgets;

use App\Models\Pomodoro;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PomodoroOverview extends BaseWidget
{
    protected static ?string $heading = 'Pomodoro Overview';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
            return Pomodoro::query();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('type')->label('Task'),
            Tables\Columns\TextColumn::make('duration_seconds')->label('Duration (min)'),
            Tables\Columns\TextColumn::make('status')->label('Status')->colors([
                'gray' => 'not_started',
                'warning' => 'in_progress',
                'success' => 'completed',
                'danger' => 'cancelled',
            ]),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
        ];
    }
}
