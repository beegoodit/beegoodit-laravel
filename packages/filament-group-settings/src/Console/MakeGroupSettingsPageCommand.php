<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeGroupSettingsPageCommand extends GeneratorCommand
{
    protected $name = 'make:filament-group-settings-page';

    protected $description = 'Create a Filament group settings page';

    protected $type = 'Group settings page';

    public function handle(): bool|int|null
    {
        if (! filled($this->option('navigation-group'))) {
            $this->error('The --navigation-group option is required.');

            return self::FAILURE;
        }

        return parent::handle();
    }

    protected function getStub(): string
    {
        return dirname(__DIR__, 2).'/stubs/group-settings-page.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Filament\\Pages\\Settings';
    }

    /**
     * @param  string  $stub
     */
    protected function replaceClass($stub, $name): string
    {
        $stub = parent::replaceClass($stub, $name);

        $basename = class_basename($name);
        $groupKey = $this->option('group-key')
            ?: Str::kebab((string) Str::replaceEnd('Settings', '', $basename))
            ?: 'group';

        $navigationGroup = $this->option('navigation-group');

        return str_replace(
            ['{{ groupKey }}', '{{ navigationGroup }}'],
            [$groupKey, "return __('{$navigationGroup}');"],
            $stub,
        );
    }

    /**
     * @return array<int, InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption('group-key', null, InputOption::VALUE_REQUIRED, 'Stable URL key (e.g. billing)'),
            new InputOption('navigation-group', null, InputOption::VALUE_REQUIRED, 'Translation key for getNavigationGroup(), e.g. navigation.groups.billing'),
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite the page if it already exists'),
        ];
    }
}
