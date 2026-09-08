<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/** Shared Chromium launch helpers for Docker/php-fpm (writable HOME + Crashpad). */
/** @SuppressWarnings(PHPMD.ErrorControlOperator) */
final class ChromiumRuntime
{
    /**
     * @return array<string, string>
     */
    public static function processEnvironment(string $userDataDir): array
    {
        return [
            'HOME' => $userDataDir,
            'XDG_CONFIG_HOME' => $userDataDir.'/config',
            'XDG_CACHE_HOME' => $userDataDir.'/cache',
        ];
    }

    public static function ensureCrashpadDirectory(string $userDataDir): string
    {
        $crashDumpsDir = $userDataDir.DIRECTORY_SEPARATOR.'Crashpad';

        if (! is_dir($crashDumpsDir) && ! mkdir($crashDumpsDir, 0755, true) && ! is_dir($crashDumpsDir)) {
            throw new RuntimeException('Unable to allocate Chromium crash dumps directory.');
        }

        return $crashDumpsDir;
    }

    /**
     * @return list<string>
     */
    public static function stabilityFlags(string $userDataDir): array
    {
        $crashDumpsDir = self::ensureCrashpadDirectory($userDataDir);

        return [
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--disable-crash-reporter',
            '--disable-extensions',
            '--crash-dumps-dir='.$crashDumpsDir,
            '--user-data-dir='.$userDataDir,
        ];
    }

    public static function allocateProfileDirectory(string $prefix = 'chromium-profile-'): string
    {
        $directory = self::workingDirectory().DIRECTORY_SEPARATOR.$prefix.bin2hex(random_bytes(6));

        if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to allocate Chromium profile directory.');
        }

        return $directory;
    }

    public static function workingDirectory(): string
    {
        if (function_exists('storage_path')) {
            try {
                $directory = storage_path('app/tmp');
                if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                    throw new RuntimeException('Unable to allocate temp files for PDF generation.');
                }

                return $directory;
            } catch (\Throwable) {
                // Fall through to system temp.
            }
        }

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-pdf-renderer';
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to allocate temp files for PDF generation.');
        }

        return $directory;
    }

    public static function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            $file->isDir() ? @rmdir($path) : @unlink($path);
        }
        @rmdir($directory);
    }
}
