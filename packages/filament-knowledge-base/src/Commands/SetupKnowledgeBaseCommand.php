<?php

namespace BeeGoodIT\FilamentKnowledgeBase\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupKnowledgeBaseCommand extends Command
{
    protected $signature = 'kb:setup
        {--locale=en : The locale code for the documentation folder (e.g., en, de, es)}
        {--force : Overwrite existing files}';

    protected $description = 'Set up the knowledge base documentation structure';

    public function handle(): int
    {
        $locale = $this->option('locale');
        $force = $this->option('force');
        $fallbackLocale = config('app.fallback_locale', 'en');

        // Validate locale format
        if (!preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $locale)) {
            $this->error("Invalid locale code: {$locale}");
            $this->line('Use format: en, de, es, pt_BR, zh_CN, etc.');
            return self::FAILURE;
        }

        // Create primary locale folder
        $this->createLocaleFolder($locale, $force);

        // Create fallback locale folder if different from primary
        if ($locale !== $fallbackLocale) {
            $this->newLine();
            $this->info("Also creating fallback locale folder ({$fallbackLocale}) to prevent redirect loops...");
            $this->createLocaleFolder($fallbackLocale, $force);
        }

        $this->newLine();
        $this->info('✓ Knowledge base setup complete!');
        $this->line("Access your documentation at: /kb");

        if ($locale !== $fallbackLocale) {
            $this->newLine();
            $this->comment("Note: Both '{$locale}' and '{$fallbackLocale}' folders were created.");
            $this->comment("The fallback locale folder is required by guava/filament-knowledge-base.");
        }

        return self::SUCCESS;
    }

    /**
     * Create a locale folder with welcome.md file.
     */
    protected function createLocaleFolder(string $locale, bool $force): void
    {
        $basePath = base_path('docs/knowledge-base');
        $localePath = "{$basePath}/{$locale}";
        $welcomePath = "{$localePath}/welcome.md";

        // Create directories
        if (!File::isDirectory($localePath)) {
            File::makeDirectory($localePath, 0755, true);
            $this->info("Created: docs/knowledge-base/{$locale}/");
        } else {
            $this->line("Directory already exists: docs/knowledge-base/{$locale}/");
        }

        // Create welcome.md template
        if (!File::exists($welcomePath) || $force) {
            $content = $this->getWelcomeTemplate($locale);
            File::put($welcomePath, $content);
            $this->info("Created: docs/knowledge-base/{$locale}/welcome.md");
        } else {
            $this->line("welcome.md already exists in {$locale}/. Use --force to overwrite.");
        }
    }

    protected function getWelcomeTemplate(string $locale = 'en'): string
    {
        $title = $locale === 'de' ? 'Willkommen' : 'Welcome';
        $heading = $locale === 'de' ? 'Willkommen in der Wissensdatenbank' : 'Welcome to the Knowledge Base';
        $intro = $locale === 'de'
            ? 'Dies ist Ihre Dokumentations-Startseite. Fügen Sie Markdown-Dateien hinzu, um Ihre Hilfe-Inhalte zu erstellen.'
            : 'This is your documentation home. Start adding markdown files to build out your help content.';

        return <<<MD
---
title: {$title}
icon: heroicon-o-home
---

# {$heading}

{$intro}

## Getting Started

Create new `.md` files in this directory to add documentation pages. The folder structure determines the navigation hierarchy.

## Example Structure

```
docs/knowledge-base/{$locale}/
├── welcome.md          (this file)
├── getting-started/
│   ├── installation.md
│   └── configuration.md
└── features/
    └── feature-name.md
```

## Markdown Features

You can use all standard markdown features including:

- **Bold** and *italic* text
- [Links](https://example.com)
- Code blocks
- Tables
- And more!
MD;
    }
}

