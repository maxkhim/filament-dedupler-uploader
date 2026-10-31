[![Latest Version](https://img.shields.io/packagist/v/maxkhim/filament-dedupler-uploader.svg?style=flat&label=Packagist&logo=packagist&logoColor=ffffff)](https://packagist.org/packages/maxkhim/filament-dedupler-uploader)
[![Laravel](https://img.shields.io/badge/Laravel-12+-007ec6.svg?logo=laravel&logoColor=ffffff)](https://laravel.com)
[![License](https://img.shields.io/packagist/l/maxkhim/filament-dedupler-uploader.svg?color=007ec6&label=License)](LICENSE.md)
[![Filament](https://img.shields.io/badge/Filament-5-007ec6.svg?&logoColor=ffffff)](https://filamentphp.com)

# Filament Dedupler Uploader: DRY for your Storage 
### One file to link them all

> **Deduplicate file storing plugin for Filament**, powered by [`maxkhim/laravel-storage-dedupler`](https://github.com/maxkhim/laravel-storage-dedupler) and [`Filament FileUpload component`](https://filamentphp.com/docs/5.x/forms/file-upload)

## Overview

`filament-dedupler-uploader` is plugin for Filament that prevents duplicate file storage by detecting files with identical content before saving them to storage. 
Instead of storing multiple copies of the same file, it uses content-based hashing (SHA1) to ensure each unique file is stored only once - regardless of how many times or by whom it is uploaded.

This is especially useful for applications where users frequently upload the same documents, images, or other files, helping to:

- **Save disk space** by eliminating redundant copies
- **Maintain data integrity** through content-based identification
- **Reduce storage costs** especially for SSD storage in 2026 ;)

### Key Features

- 🔍 **Automatic deduplication** - Files are compared by SHA1 hash before storage
- 📦 **Seamless integration** - Based on Filament's `FileUpload` component
- 🔗 **Smart relationships** - Attaches `UniqueFile` records to Eloquent models via the `Deduplable` trait
- 📋 **Infolist components** - Display uploaded files with metadata (original name, size, SHA1 hash, download)
- 🗂️ **Multiple file support** - Handles single and multiple file uploads

## Requirements

- PHP 8.1+
- Laravel 12+
- Filament 5.x

## 🚀 Quick Start

### 1. Install the package

```bash
composer require maxkhim/filament-dedupler-uploader
php artisan dedupler:install
php artisan dedupler:check
```

### 2. Add the Plugin

```php
use Maxkhim\FilamentDeduplerUploader\FilamentDeduplerUploaderPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentDeduplerUploaderPlugin::make()
        ]);
}
```

### 3. Prepare Your Model

Your Eloquent model can use the `Deduplable` trait from `laravel-storage-dedupler` for simplicity linking files:

```php
use Illuminate\Database\Eloquent\Model;
use Maxkhim\Dedupler\Traits\Deduplable;

class Document extends Model
{
    use Deduplable;

    // The trait provides the relationship and attachUniqueFile() method
}
```

### 4. Use the DeduplerFile Upload Component

Based on Filament's `FileUpload` component

```php
use Maxkhim\FilamentDeduplerUploader\Forms\Components\DeduplerFileUploader;

DeduplerFileUploader::make('attachment')
    ->multiple()

```

### 5. Display Uploaded Files

Displays files in a table with original name, size, download button, and SHA1 hash, using standard Filament components:

```php
use Maxkhim\FilamentDeduplerUploader\Infolists\DeduplerFilesRepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

public function infolist(Infolist $infolist): Infolist
{
    return $infolist->schema([
        Section::make('Uploaded Files')
            ->schema([
                DeduplerFilesRepeatableEntry::make()
                    ->label('Documents'),
            ]),
    ]);
}
```

## How It Works

### Upload Flow

1. **User uploads a file** in the Filament form and saves record
2. **SHA1 hash is computed** from the file content
3. **Deduplication check** - If a file with the same hash already exists in storage, it is reused instead of being saved again
4. **Relationship attachment** - The polymorphic `uniqueFiles` record attribute is attached to the model via the `Deduplable` trait
5. **Form submission** - The file path and SHA1 hash is stored in the database

### Deletion Behavior

Physical file deletion is managed by the `laravel-storage-dedupler` package. When a file is removed from a model, the relationship is detached.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

This package is built on top of [`maxkhim/laravel-storage-dedupler`](https://github.com/maxkhim/laravel-storage-dedupler).
