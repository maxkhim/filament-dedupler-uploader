<?php

declare(strict_types=1);

namespace Maxkhim\FilamentDeduplerUploader\Infolists;

use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Maxkhim\Dedupler\Models\UniqueFile;

class DeduplerFilesRepeatableEntry extends RepeatableEntry
{
    public static function getDefaultName(): ?string
    {
        return 'uniqueFiles';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->contained(false)
            ->table([
                RepeatableEntry\TableColumn::make(''),
                RepeatableEntry\TableColumn::make('')
                    ->width("15%"),
                RepeatableEntry\TableColumn::make(''),
                RepeatableEntry\TableColumn::make('SHA1')
                    ->alignCenter(),
            ])
            ->schema([
                TextEntry::make('original_name')
                    ->hiddenLabel()
                    ->openUrlInNewTab()
                    ->icon(Heroicon::PaperClip),
                TextEntry::make('size')
                    ->hiddenLabel()
                    ->badge()
                    ->state(function ($record) {
                        return Number::fileSize($record->size, 1);
                    }),
                TextEntry::make('dl')
                    ->hiddenLabel()
                    ->beforeContent(
                        [
                            Action::make('download')
                                ->label('Download')
                                ->hiddenLabel()
                                ->icon(Heroicon::ArrowDownTray)
                                ->iconButton()
                                ->color('primary')
                                ->button()
                                ->action(function (UniqueFile $record) {
                                    if (!Storage::disk($record->disk)->exists($record->path)) {
                                        Notification::make()
                                            ->warning()
                                            ->title('Error')
                                            ->body('File not found.')
                                            ->send();
                                        return;
                                    }
                                    return Storage::disk($record->disk)
                                        ->download($record->path, $record->original_name);
                                })
                        ]
                    ),
                TextEntry::make('sha1_hash')
                    ->alignCenter()
                ,
            ]);
    }
}