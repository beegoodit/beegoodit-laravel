<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources\Tests\Unit;

use BeegoodIT\LaravelPublicResources\PublicResourceKey;
use BeegoodIT\LaravelPublicResources\Tests\TestCase;

class PublicResourceKeyTest extends TestCase
{
    public function test_parse_slug_and_id(): void
    {
        $key = PublicResourceKey::parse('summer-night-h3k7m2p9');

        $this->assertNotNull($key);
        $this->assertSame('h3k7m2p9', $key->publicId);
        $this->assertSame('summer-night', $key->slug);
    }

    public function test_parse_id_only(): void
    {
        $key = PublicResourceKey::parse('h3k7m2p9');

        $this->assertNotNull($key);
        $this->assertSame('h3k7m2p9', $key->publicId);
        $this->assertNull($key->slug);
    }

    public function test_parse_normalizes_case_on_id(): void
    {
        $key = PublicResourceKey::parse('summer-night-H3K7M2P9');

        $this->assertNotNull($key);
        $this->assertSame('h3k7m2p9', $key->publicId);
        $this->assertSame('summer-night', $key->slug);
    }

    public function test_parse_rejects_trailing_hyphen_before_id(): void
    {
        $this->assertNull(PublicResourceKey::parse('-h3k7m2p9'));
        $this->assertNull(PublicResourceKey::parse('summer--h3k7m2p9'));
    }

    public function test_parse_rejects_invalid_suffix(): void
    {
        $this->assertNull(PublicResourceKey::parse('summer-night-notvalid'));
        $this->assertNull(PublicResourceKey::parse('summer-night'));
    }

    public function test_format_joins_slug_and_id(): void
    {
        $this->assertSame(
            'summer-night-h3k7m2p9',
            PublicResourceKey::format('summer-night', 'h3k7m2p9')
        );
    }

    public function test_format_id_only_when_slug_empty(): void
    {
        $this->assertSame('h3k7m2p9', PublicResourceKey::format(null, 'h3k7m2p9'));
        $this->assertSame('h3k7m2p9', PublicResourceKey::format('', 'h3k7m2p9'));
    }
}
