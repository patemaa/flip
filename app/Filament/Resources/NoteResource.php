<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoteResource\Pages;
use App\Filament\Resources\NoteResource\RelationManagers;
use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('attachment'),
                Forms\Components\TagsInput::make('tags')
                    ->label('Tags')
                    ->placeholder('Add a tag')
                    ->separator(',')
                    ->columnSpanFull()
                    ->hint('Separate them with a comma'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('body')
                    ->searchable()
                    ->limit(50)
                    ->getStateUsing(fn($record) => strip_tags($record->body)),
                Tables\Columns\TextColumn::make('tags')
                    ->label('Tags')
                    ->getStateUsing(fn($record) => implode(', ', $record->tags ?? [])),
                Tables\Columns\BadgeColumn::make('attachment')
                    ->label('Attachment')
                    ->getStateUsing(fn($record) => $record->attachment ? 'Attached' : 'No File')
                    ->colors([
                        'gray' => fn($state) => $state === 'Attached',
                        'gray' => fn($state) => $state === 'No File',
                    ])
                    ->url(fn($record) => $record->attachment ? asset('storage/' . $record->attachment) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->action(function ($record) {
                        $zipName = 'note-' . $record->id . '.zip';
                        $zipPath = storage_path('app/public/' . $zipName);

                        $zip = new ZipArchive();
                        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

                            $body = strip_tags($record->body);
                            $zip->addFromString('note.txt', "Title: {$record->title}\n\nBody:\n{$body}");

                            if ($record->attachment && Storage::disk('public')->exists($record->attachment)) {
                                $zip->addFile(storage_path('app/public/' . $record->attachment), basename($record->attachment));
                            }


                            $zip->close();
                        }

                        return response()->download($zipPath)->deleteFileAfterSend(true);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
            'trashed' => Pages\TrashedNotes::route('/trash'),
        ];
    }
}
