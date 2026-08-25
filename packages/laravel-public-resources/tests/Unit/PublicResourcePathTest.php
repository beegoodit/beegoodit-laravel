<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources\Tests\Unit;

use BeegoodIT\LaravelPublicResources\PublicResourcePath;
use BeegoodIT\LaravelPublicResources\Tests\TestCase;

class PublicResourcePathTest extends TestCase
{
    public function test_collection_path(): void
    {
        $this->assertSame(
            '/de/programm/veranstaltungen',
            PublicResourcePath::collection('de', 'programm', 'veranstaltungen')
        );
    }

    public function test_member_path(): void
    {
        $this->assertSame(
            '/de/programm/veranstaltungen/summer-night-h3k7m2p9',
            PublicResourcePath::member('de', 'programm', 'veranstaltungen', 'summer-night', 'h3k7m2p9')
        );
    }

    public function test_action_path(): void
    {
        $this->assertSame(
            '/de/programm/veranstaltungen/h3k7m2p9/ics',
            PublicResourcePath::action('de', 'programm', 'veranstaltungen', 'h3k7m2p9', 'ics')
        );
    }
}
