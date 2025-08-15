<?php

namespace App\Filament\Resources\PomodoroResource\Pages;

use App\Filament\Resources\PomodoroResource;
use Filament\Resources\Pages\EditRecord;
use Carbon\Carbon;

class EditPomodoro extends EditRecord
{
    protected static string $resource = PomodoroResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $started = $data['started_at'] ?? null;
        $ended   = $data['ended_at'] ?? null;

        if ($started && $ended) {
            $data['duration_seconds'] = max(0, Carbon::parse($started)->diffInSeconds(Carbon::parse($ended), false)
            );
        }


        return $data;
    }
}
