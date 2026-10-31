<?php

namespace Maxkhim\FilamentDeduplerUploader;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentDeduplerUploaderPlugin implements Plugin
{
    protected ?string $defaultTheme = null;

    public function getId(): string
    {
        return 'filament-dedupler-uploader';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function defaultTheme(?string $theme): static
    {
        $this->defaultTheme = $theme;

        return $this;
    }

    public function getDefaultTheme(): ?string
    {
        return $this->defaultTheme;
    }
}
