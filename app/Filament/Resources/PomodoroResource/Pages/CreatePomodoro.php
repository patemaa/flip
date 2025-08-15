<?php

namespace App\Filament\Resources\PomodoroResource\Pages;

use App\Filament\Resources\PomodoroResource;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreatePomodoro extends CreateRecord
{
    protected static string $resource = PomodoroResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $started = $data['started_at'] ?? null;
        $ended   = $data['ended_at'] ?? null;

        if ($started && $ended) {
            $data['duration_seconds'] = max(
                Carbon::parse($ended)->diffInSeconds(Carbon::parse($started))
            );
        }

        return $data;
    }
}
