<?php

namespace App\Filament\Resources\PomodoroResource\Pages;

use App\Filament\Resources\PomodoroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPomodoros extends ListRecords
{
    protected static string $resource = PomodoroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
