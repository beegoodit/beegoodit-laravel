<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Support\Requirements;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class RequirementsTest extends TestCase
{
    #[Test]
    public function the_minimum_matches_the_composer_constraint(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__).'/composer.json'), true);

        $this->assertSame('^'.Requirements::minimumConstraint(), $composer['require']['laravel/ai']);
    }

    #[Test]
    public function it_accepts_the_installed_version(): void
    {
        Requirements::ensure();

        $this->assertNotNull(Requirements::installedLaravelAi());
    }

    #[Test]
    public function it_accepts_the_minimum_and_newer_versions(): void
    {
        Requirements::check(Requirements::MinimumLaravelAi);
        Requirements::check('v1.4.0');
        Requirements::check('2.0.0');
        Requirements::check('dev-main');
        Requirements::check(null);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_stops_on_an_older_version_with_a_clear_message(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('needs laravel/ai 1.4.0 or newer, but 0.7.2 is installed');

        Requirements::check('0.7.2');
    }
}
