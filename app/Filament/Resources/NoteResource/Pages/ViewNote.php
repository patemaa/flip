<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\Pages\ViewRecord;

class ViewNote extends ViewRecord
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->color('warning')
                ->icon('heroicon-o-pencil-square'),

            Actions\Action::make('download')
                ->label('Download as TXT')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return $this->downloadNoteAsText();
                }),

            Actions\DeleteAction::make()
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Note Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label('Title')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->iconColor('primary')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->copyable()
                                    ->copyMessage('Title Copied!')
                                    ->copyMessageDuration(1500),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->icon('heroicon-o-calendar-days')
                                    ->dateTime('d.m.Y H:i')
                                    ->color('gray'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('tags')
                                    ->label('Tags')
                                    ->icon('heroicon-o-tag')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return implode(', ', $state);
                                        }
                                        return $state ?? '';
                                    })
                                    ->badge()
                                    ->color('primary')
                                    ->visible(fn($record) => !empty($record->tags)),
                            ]),
                    ]),

                Infolists\Components\Section::make('Content')
                    ->icon('heroicon-o-document')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\TextEntry::make('body')
                            ->label('')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->iconColor('primary')
                            ->html()
                            ->prose()
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Content Copied!')
                            ->copyMessageDuration(1500),

                    ]),

                Infolists\Components\Grid::make()
                    ->schema([
                        Infolists\Components\Section::make('Attachment Files')
                            ->icon('heroicon-o-paper-clip')
                            ->collapsible()
                            ->collapsed()
                            ->columnSpan(1)
                            ->visible(fn($record) => !empty($record->attachment))
                            ->schema([
                                Infolists\Components\TextEntry::make('attachment')
                                    ->label('File')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state) return 'File not found';
                                        return pathinfo($state, PATHINFO_BASENAME);
                                    })
                                    ->url(fn($record) => $record->attachment ? asset('storage/' . $record->attachment) : null, true)
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->visible(fn($record) => !empty($record->attachment)),
                            ]),

                        Infolists\Components\Section::make('Statistics')
                            ->icon('heroicon-o-chart-bar')
                            ->collapsible()
                            ->collapsed()
                            ->columnSpan(1)
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('body')
                                            ->label('Character Number')
                                            ->icon('heroicon-o-document-text')
                                            ->formatStateUsing(fn($state) => strlen(strip_tags($state ?? '')) . ' characters')
                                            ->color('info'),

                                        Infolists\Components\TextEntry::make('body')
                                            ->label('Word Number')
                                            ->icon('heroicon-o-document-duplicate')
                                            ->formatStateUsing(fn($state) => str_word_count(strip_tags($state ?? '')) . ' word')
                                            ->color('info'),
                                    ])
                            ]),
                    ]),
            ]);
    }

    private function downloadNoteAsText()
    {
        $record = $this->record;
        $fileName = 'not-' . $record->id . '-' . date('Y-m-d') . '.txt';
        $filePath = storage_path('app/public/temp/' . $fileName);

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $content = $this->prepareNoteContent($record);

        file_put_contents($filePath, $content);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    private function prepareNoteContent($record): string
    {
        $content = "";
        $content .= "===============================================\n";
        $content .= "                 NOTE DETAILS                  \n";
        $content .= "===============================================\n\n";

        $content .= "Title: " . ($record->title ?? 'Başlık Yok') . "\n";
        $content .= "Created At: " . $record->created_at?->format('d.m.Y H:i') . "\n";

        if (!empty($record->tags)) {
            $tags = is_array($record->tags) ? implode(', ', $record->tags) : $record->tags;
            $content .= "Tags: " . $tags . "\n";
        }

        $content .= "\n" . str_repeat("-", 47) . "\n";
        $content .= "Content:\n";
        $content .= str_repeat("-", 47) . "\n\n";

        $content .= strip_tags($record->body ?? '') . "\n\n";

        if (!empty($record->attachment)) {
            $content .= str_repeat("-", 47) . "\n";
            $content .= "Attachment File: " . pathinfo($record->attachment, PATHINFO_BASENAME) . "\n";
            $content .= str_repeat("-", 47) . "\n";
        }

        $content .= "\n\nExport Date: " . now()->format('d.m.Y H:i:s') . "\n";

        return $content;
    }
}
