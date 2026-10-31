<?php

namespace Maxkhim\FilamentDeduplerUploader\Providers;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * ServiceProvider для пакета filament-dedupler-uploader.
 *
 * Наследуется от PackageServiceProvider из Laravel Package Tools,
 * обеспечивает настройку, регистрацию ресурсов, миграций, команд и т.д.
 */
class FilamentDeduplerUploaderServiceProvider extends PackageServiceProvider
{
    /**
     * Имя пакета.
     */
    public static string $name = 'filament-dedupler-uploader';

    /**
     * Пространство имен для шаблонов.
     */
    public static string $viewNamespace = 'filament-dedupler-uploader';

    /**
     * Настраивает пакет с помощью Laravel Package Tools.
     *
     * Регистрирует команды, маршруты, миграции, конфигурации, языковые файлы, представления и т.д.
     */
    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace);
    }

    /**
     * Выполняется после регистрации пакета.
     * Пока не содержит реализации.
     */
    public function packageRegistered(): void {}

    /**
     * Выполняется после загрузки пакета.
     *
     * Регистрирует активы, иконки, публикует stubs и обрабатывает тестирование.
     */
    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    /**
     * Возвращает имя пакета npm или пакета, отвечающего за активы.
     *
     * @return string|null
     */
    protected function getAssetPackageName(): ?string
    {
        return 'maxkhim/filament-dedupler-uploader';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
        ];
    }
}