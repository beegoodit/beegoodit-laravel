<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

use Composer\InstalledVersions;
use RuntimeException;

/**
 * Minimum versions this package needs from its host.
 *
 * Composer enforces them for hosts that require the package. Hosts that
 * receive a pack-copy (TallCMS) never see the package's composer.json, so
 * they call {@see ensure()} at boot.
 */
final class Requirements
{
    /** Lowest laravel/ai version supported. Must match the composer.json constraint. */
    public const MinimumLaravelAi = '1.4.0';

    /**
     * Stop with a clear message when the installed laravel/ai is too old.
     *
     * @throws RuntimeException
     */
    public static function ensure(): void
    {
        self::check(self::installedLaravelAi());
    }

    /**
     * @throws RuntimeException
     */
    public static function check(?string $installedLaravelAi): void
    {
        if ($installedLaravelAi === null || str_starts_with($installedLaravelAi, 'dev-')) {
            return;
        }

        if (version_compare(ltrim($installedLaravelAi, 'vV'), self::MinimumLaravelAi, '>=')) {
            return;
        }

        throw new RuntimeException(sprintf(
            'beegoodit/filament-ai needs laravel/ai %s or newer, but %s is installed. Run: composer require laravel/ai:^%s',
            self::MinimumLaravelAi,
            $installedLaravelAi,
            self::minimumConstraint(),
        ));
    }

    public static function installedLaravelAi(): ?string
    {
        if (! class_exists(InstalledVersions::class) || ! InstalledVersions::isInstalled('laravel/ai')) {
            return null;
        }

        return InstalledVersions::getPrettyVersion('laravel/ai');
    }

    /**
     * The minimum as a caret constraint body, e.g. "1.4".
     */
    public static function minimumConstraint(): string
    {
        return implode('.', array_slice(explode('.', self::MinimumLaravelAi), 0, 2));
    }
}
