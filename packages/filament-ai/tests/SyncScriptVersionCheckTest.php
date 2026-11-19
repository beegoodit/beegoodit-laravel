<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class SyncScriptVersionCheckTest extends TestCase
{
    private string $pluginRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluginRoot = sys_get_temp_dir().'/filament-ai-sync-'.bin2hex(random_bytes(6));

        mkdir($this->pluginRoot.'/src/Providers', 0777, true);
        file_put_contents($this->pluginRoot.'/src/Providers/AiServiceProvider.php', "<?php\n");
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->pluginRoot);

        parent::tearDown();
    }

    #[Test]
    public function it_refuses_a_plugin_whose_composer_json_allows_an_older_laravel_ai(): void
    {
        $this->writePlugin(composer: '^0.7', plugin: '^1.4');

        [$exitCode, $output] = $this->sync();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Refusing to sync', $output);
        $this->assertStringContainsString('composer.json: require allows laravel/ai ^0.7', $output);
        $this->assertDirectoryDoesNotExist($this->pluginRoot.'/src/FilamentAi');
    }

    #[Test]
    public function it_refuses_a_plugin_whose_plugin_json_allows_an_older_laravel_ai(): void
    {
        $this->writePlugin(composer: '^1.4', plugin: '^0.7|^1.4');

        [$exitCode, $output] = $this->sync();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('plugin.json: requires_packages allows laravel/ai ^0.7|^1.4', $output);
        $this->assertDirectoryDoesNotExist($this->pluginRoot.'/src/FilamentAi');
    }

    #[Test]
    public function it_refuses_a_plugin_that_does_not_state_laravel_ai(): void
    {
        file_put_contents($this->pluginRoot.'/plugin.json', json_encode(['requires_packages' => []]));

        [$exitCode, $output] = $this->sync();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('requires_packages has no laravel/ai entry', $output);
    }

    #[Test]
    public function it_copies_into_a_plugin_that_meets_the_minimum(): void
    {
        $this->writePlugin(composer: '^1.4', plugin: '^1.4');

        [$exitCode, $output] = $this->sync();

        $this->assertSame(0, $exitCode, $output);
        $this->assertFileExists($this->pluginRoot.'/src/FilamentAi/Support/Requirements.php');
        $this->assertFileExists($this->pluginRoot.'/src/FilamentAi/GENERATED.md');
    }

    private function writePlugin(string $composer, string $plugin): void
    {
        file_put_contents($this->pluginRoot.'/composer.json', json_encode([
            'require' => ['laravel/ai' => $composer],
        ]));

        file_put_contents($this->pluginRoot.'/plugin.json', json_encode([
            'requires_packages' => ['laravel/ai' => $plugin],
        ]));
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function sync(): array
    {
        $package = dirname(__DIR__);

        exec(sprintf(
            'bash %s %s %s 2>&1',
            escapeshellarg($package.'/scripts/sync-filament-ai.sh'),
            escapeshellarg($package),
            escapeshellarg($this->pluginRoot),
        ), $lines, $exitCode);

        return [$exitCode, implode("\n", $lines)];
    }
}
