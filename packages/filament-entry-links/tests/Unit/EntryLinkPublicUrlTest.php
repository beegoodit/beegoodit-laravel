<?php

namespace BeegoodIT\FilamentEntryLinks\Tests\Unit;

use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use BeegoodIT\FilamentEntryLinks\Tests\TestCase;

class EntryLinkPublicUrlTest extends TestCase
{
    public function test_public_url_uses_token_only_when_slug_blank(): void
    {
        config(['filament-entry-links.route_prefix' => 'link']);

        $link = new EntryLink([
            'token' => 'abc123',
            'slug' => null,
        ]);

        $this->assertStringEndsWith('/link/abc123', $link->publicUrl());
    }

    public function test_public_url_appends_slug_when_present(): void
    {
        config(['filament-entry-links.route_prefix' => 'link']);

        $link = new EntryLink([
            'token' => 'abc123',
            'slug' => 'poster',
        ]);

        $this->assertStringEndsWith('/link/abc123-poster', $link->publicUrl());
    }

    public function test_public_url_falls_back_when_route_prefix_empty(): void
    {
        config(['filament-entry-links.route_prefix' => '']);

        $link = new EntryLink([
            'token' => 'x',
            'slug' => null,
        ]);

        $this->assertStringEndsWith('/link/x', $link->publicUrl());
    }
}
