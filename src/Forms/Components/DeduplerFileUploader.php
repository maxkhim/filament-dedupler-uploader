<?php

namespace Maxkhim\FilamentDeduplerUploader\Forms\Components;

use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maxkhim\Dedupler\Facades\Dedupler;
use Maxkhim\Dedupler\Models\UniqueFile;
use Maxkhim\Dedupler\Traits\Deduplable;

class DeduplerFileUploader extends FileUpload
{
    private static array $originalFileNames = [];
    protected function setUp(): void
    {
        parent::setUp();

        // Указываем диск, который использует dedupler (с фоллбеком на 'deduplicated')
        $this->disk(config('dedupler.disk_default', 'deduplicated'));

        // Переопределяем сохранение файла (используем вынесенный метод)
        $this->saveUploadedFileUsing(function ($file) {
            return $this->processAndStoreFile($file);
        });

        // 2. Привязка файлов к модели (срабатывает ПОСЛЕ создания/обновления модели)
        $this->saveRelationshipsUsing(function (Model $record, $state) {
            if (blank($state)) {
                return;
            }
            // Приводим состояние к массиву путей
            $paths = is_array($state) ? $state : [$state];

            foreach ($paths as $path) {
                // Находим запись UniqueFile по сохранённому пути
                $uniqueFile = UniqueFile::query()
                    ->find(pathinfo($path, PATHINFO_FILENAME));

                if ($uniqueFile && $this->modelUsesDeduplable($record)) {
                    // Используем метод трейта Deduplable для безопасной привязки
                    // (он сам проверит, нет ли уже такой связи, чтобы не плодить дубликаты в pivot-таблице)
                    $originalFilename =
                        (self::$originalFileNames[$uniqueFile->id] ?? [])
                            ? ["original_name" => $uniqueFile->original_name]
                            : [];

                    $record->attachUniqueFile($uniqueFile->id, $originalFilename);
                }
            }
        });

        // Переопределяем удаление файла (не удаляем физически)
        $this->deleteUploadedFileUsing(function (): void {
            // Намеренно пусто — управление дубликатами делает dedupler
        });
    }


    /**
     * Переопределяем метод сохранения файлов для гарантии корректного состояния Livewire.
     */
    private function detach($detachedFile): void
    {
        Dedupler::detach(
            pathinfo($detachedFile, PATHINFO_FILENAME), $this->getRecord()
        );
    }

    private function checkForDetach($newFiles, $oldFiles) {
        $detachedFiles = [];
        foreach ($oldFiles as $oldFile) {
            if (!in_array($oldFile, $newFiles)) {
                $detachedFiles[] = $oldFile;
            }
        }

        foreach ($detachedFiles as $detachedFile) {
            $this->detach($detachedFile);
        }
    }

    public function saveUploadedFiles(): void
    {
        $state = $this->getState();

        if (blank($state)) {
            if ($this->getRecord()) {
                $this->checkForDetach($state, $this->getRecord()?->toArray()["attachments"] ?? []);
            }
            return;
        }

        // Если включена множественная загрузка и состояние — массив
        if ($this->isMultiple() && is_array($state)) {
            $state = collect($state)
                ->map(fn ($file) => $this->processAndStoreFile($file))
                ->filter() // Убираем null значения, если какие-то файлы не обработались
                ->unique()
                ->values() // Сбрасываем ключи массива
                ->all();
        }
        else {
            $state = $this->processAndStoreFile($state);
        }

        // Принудительно устанавливаем очищенное состояние (только строки или массивы строк)
        $this->state($state);

        if ($this->getRecord()) {
            $this->checkForDetach($state, $this->getRecord()?->toArray()["attachments"] ?? []);
        }
    }

    /**
     * Единый метод для обработки и сохранения файла.
     *
     * @param string|TemporaryUploadedFile|UploadedFile|null $file
     * @return string|null
     */
    private function processAndStoreFile(TemporaryUploadedFile|string|UploadedFile|null $file): ?string
    {
        // Если файл уже сохранен (пришел как строка из БД), просто возвращаем его
        if (is_string($file)) {
            return $file;
        }

        // Обрабатываем только реальные загружаемые файлы
        if ($file instanceof UploadedFile) {
            $uniqueFile = Dedupler::storeFromUploadedFile($file);
            self::$originalFileNames[$uniqueFile->sha1_hash] = $file->getClientOriginalName();
            // Привязываем к модели, если она поддерживает трейт Deduplable
            $record = $this->getRecord();
            if ($record instanceof Model && $this->modelUsesDeduplable($record)) {
                $record->attachUniqueFile($uniqueFile->id);
            }
            return $uniqueFile->path;
        }

        return null;
    }

    /**
     * Проверяет, использует ли модель трейт Deduplable
     */
    private function modelUsesDeduplable(Model $model): bool
    {
        return in_array(
            Deduplable::class,
            class_uses_recursive($model)
        );
    }
}