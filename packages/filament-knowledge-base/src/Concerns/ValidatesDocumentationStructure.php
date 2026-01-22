<?php

namespace BeegoodIT\FilamentKnowledgeBase\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

trait ValidatesDocumentationStructure
{
    /**
     * Valid locale codes that the guava/filament-knowledge-base package recognizes.
     * This is not exhaustive but covers common cases.
     */
    protected array $validLocaleCodes = [
        'af',
        'ar',
        'bg',
        'bn',
        'ca',
        'cs',
        'cy',
        'da',
        'de',
        'el',
        'en',
        'es',
        'et',
        'eu',
        'fa',
        'fi',
        'fr',
        'gl',
        'he',
        'hi',
        'hr',
        'hu',
        'id',
        'is',
        'it',
        'ja',
        'ka',
        'km',
        'ko',
        'lt',
        'lv',
        'mk',
        'mn',
        'ms',
        'my',
        'nb',
        'ne',
        'nl',
        'nn',
        'pl',
        'pt',
        'pt_BR',
        'ro',
        'ru',
        'si',
        'sk',
        'sl',
        'sq',
        'sr',
        'sv',
        'sw',
        'ta',
        'te',
        'th',
        'tl',
        'tr',
        'uk',
        'ur',
        'uz',
        'vi',
        'zh',
        'zh_CN',
        'zh_TW',
    ];

    /**
     * Validate that the knowledge base documentation structure is correct.
     *
     * @throws RuntimeException in development if structure is invalid
     */
    protected function validateDocumentationStructure(): void
    {
        $docsPath = base_path('docs/knowledge-base');

        // Skip validation if directory doesn't exist (no docs configured)
        if (!File::isDirectory($docsPath)) {
            return;
        }

        $directories = File::directories($docsPath);

        if (empty($directories)) {
            return;
        }

        $hasValidLocale = false;
        $invalidFolders = [];
        $fallbackLocale = config('app.fallback_locale', 'en');
        $hasFallbackLocale = false;

        foreach ($directories as $directory) {
            $folderName = basename((string) $directory);

            if ($this->isValidLocaleCode($folderName)) {
                $hasValidLocale = true;
                if ($folderName === $fallbackLocale) {
                    $hasFallbackLocale = true;
                }
            } else {
                $invalidFolders[] = $folderName;
            }
        }

        // Check for invalid folder names
        if (!$hasValidLocale && $invalidFolders !== []) {
            $this->handleInvalidStructure($invalidFolders);
        }

        // Check for missing fallback locale folder
        if ($hasValidLocale && !$hasFallbackLocale) {
            $this->handleMissingFallbackLocale($fallbackLocale);
        }
    }

    /**
     * Check if a folder name is a valid locale code.
     */
    protected function isValidLocaleCode(string $name): bool
    {
        return in_array($name, $this->validLocaleCodes, true)
            || preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $name);
    }

    /**
     * Handle invalid documentation structure.
     */
    protected function handleInvalidStructure(array $invalidFolders): void
    {
        $folders = implode(', ', array_map(fn($f): string => "'{$f}'", $invalidFolders));

        $message = <<<MSG
[Knowledge Base] Invalid documentation structure detected!

Found folders: {$folders}

The guava/filament-knowledge-base package requires locale-coded folder names (e.g., 'en', 'de', 'es').
Using arbitrary folder names like 'general' or 'docs' will cause a redirect loop.

Correct structure:
  docs/knowledge-base/
    en/
      your-docs.md

Fix: Rename your folders to use valid locale codes, or run: php artisan kb:setup

MSG;

        if (app()->environment('local', 'development', 'testing')) {
            throw new RuntimeException($message);
        }

        Log::warning($message);
    }

    /**
     * Handle missing fallback locale folder.
     */
    protected function handleMissingFallbackLocale(string $fallbackLocale): void
    {
        $message = <<<MSG
[Knowledge Base] Missing fallback locale folder!

The guava/filament-knowledge-base package requires a fallback locale folder to prevent redirect loops.
Your fallback locale is '{$fallbackLocale}' but no docs/knowledge-base/{$fallbackLocale}/ folder exists.

This WILL cause a redirect loop error (ERR_TOO_MANY_REDIRECTS).

Fix: Run: php artisan kb:setup --locale=YOUR_LOCALE

This will automatically create both your locale and the fallback locale folders.

MSG;

        if (app()->environment('local', 'development', 'testing')) {
            throw new RuntimeException($message);
        }

        Log::warning($message);
    }
}

