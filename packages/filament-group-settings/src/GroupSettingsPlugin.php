<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings;

use BeegoodIT\FilamentGroupSettings\Pages\GroupSettingsPage;
use BeegoodIT\FilamentGroupSettings\Pages\SettingsLanding;
use Filament\Contracts\Plugin;
use Filament\Pages\Page;
use Filament\Panel;
use InvalidArgumentException;
use ReflectionMethod;

class GroupSettingsPlugin implements Plugin
{
    /** @var list<class-string<GroupSettingsPage>> */
    protected array $pages = [];

    protected bool $hasLanding = false;

    protected ?string $landingNavigationLabel = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament()->getPlugin('filament-group-settings');

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-group-settings';
    }

    /**
     * @param  list<class-string<GroupSettingsPage>>  $pages
     */
    public function pages(array $pages): static
    {
        foreach ($pages as $page) {
            if (! is_subclass_of($page, GroupSettingsPage::class)) {
                throw new InvalidArgumentException(
                    sprintf('%s must extend %s.', $page, GroupSettingsPage::class),
                );
            }

            // Do not call getNavigationGroup() here — it often runs __() and can
            // empty-cache package translations when TallCMS boots Filament plugins
            // before the page package's service provider has loadTranslationsFrom().
            if ($page::getGroupKey() === '') {
                throw new InvalidArgumentException(
                    sprintf('%s must define a non-empty getGroupKey().', $page),
                );
            }
        }

        $this->pages = array_values($pages);

        return $this;
    }

    public function landing(bool $condition = true): static
    {
        $this->hasLanding = $condition;

        return $this;
    }

    public function landingNavigationLabel(?string $label): static
    {
        $this->landingNavigationLabel = $label;

        return $this;
    }

    public function hasLanding(): bool
    {
        return $this->hasLanding;
    }

    public function getLandingNavigationLabel(): ?string
    {
        return $this->landingNavigationLabel;
    }

    /**
     * @return list<class-string<GroupSettingsPage>>
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    public function hasPage(string $page): bool
    {
        return in_array($page, $this->pages, true);
    }

    public function register(Panel $panel): void
    {
        $toRegister = $this->pages;

        if ($this->hasLanding) {
            $toRegister[] = SettingsLanding::class;
        }

        if ($toRegister === []) {
            return;
        }

        $panel->pages($toRegister);
    }

    public function boot(Panel $panel): void
    {
        foreach ($this->pages as $page) {
            $method = new ReflectionMethod($page, 'getNavigationGroup');

            if ($method->getDeclaringClass()->getName() === Page::class) {
                throw new InvalidArgumentException(
                    sprintf('%s must define getNavigationGroup() (string or UnitEnum).', $page),
                );
            }
        }
    }
}
