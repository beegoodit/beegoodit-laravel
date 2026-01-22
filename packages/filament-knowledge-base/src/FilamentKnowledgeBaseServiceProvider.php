<?php

namespace BeegoodIT\FilamentKnowledgeBase;

use BeegoodIT\FilamentKnowledgeBase\Commands\SetupKnowledgeBaseCommand;
use BeegoodIT\FilamentKnowledgeBase\Concerns\ValidatesDocumentationStructure;
use Illuminate\Support\ServiceProvider;

class FilamentKnowledgeBaseServiceProvider extends ServiceProvider
{
    use ValidatesDocumentationStructure;

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->validateDocumentationStructure();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupKnowledgeBaseCommand::class,
            ]);
        }
    }
}
