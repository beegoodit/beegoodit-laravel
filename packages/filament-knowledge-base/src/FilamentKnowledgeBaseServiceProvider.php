<?php

namespace BeeGoodIT\FilamentKnowledgeBase;

use BeeGoodIT\FilamentKnowledgeBase\Commands\SetupKnowledgeBaseCommand;
use BeeGoodIT\FilamentKnowledgeBase\Concerns\ValidatesDocumentationStructure;
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
