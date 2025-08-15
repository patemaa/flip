<?php

namespace App\Filament\Widgets;

use App\Models\Pomodoro;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PomodoroOverview2 extends ChartWidget
{
    protected static ?string $heading = 'Daily Pomodoro Duration';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        // Son 7 gün
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('d M');

                $totalSeconds = Pomodoro::whereDate('started_at', $date)
                    ->get()
                    ->sum(function ($pomodoro) {
                        if ($pomodoro->started_at && $pomodoro->ended_at) {
                            return Carbon::parse($pomodoro->started_at)
                                ->diffInSeconds(Carbon::parse($pomodoro->ended_at));
                        }
                        return 0;
                    });

                $totalMinutes = floor($totalSeconds / 60);
                $data[] = $totalMinutes;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Minutes',
                    'data' => $data,
                    'backgroundColor' => 'pink',
                    'borderColor' => 'gray',

                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
