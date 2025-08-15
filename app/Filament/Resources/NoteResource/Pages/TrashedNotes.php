<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class TrashedNotes extends ListRecords
{
    protected static string $resource = NoteResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->onlyTrashed();
    }

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationLabel = 'Çöp Kutusu';
    protected static ?string $navigationGroup = 'Not Yönetimi';
}
