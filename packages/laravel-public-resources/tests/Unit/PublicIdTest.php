<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelPublicResources\Tests\Unit;

use BeegoodIT\LaravelPublicResources\PublicId;
use BeegoodIT\LaravelPublicResources\Tests\TestCase;
use InvalidArgumentException;

class PublicIdTest extends TestCase
{
    public function test_generate_returns_valid_length_and_alphabet(): void
    {
        $id = PublicId::generate();

        $this->assertSame(PublicId::LENGTH, strlen($id));
        $this->assertTrue(PublicId::isValid($id));
    }

    public function test_normalize_lowercases(): void
    {
        $this->assertSame('h3k7m2p9', PublicId::normalize('H3K7M2P9'));
    }

    public function test_rejects_forbidden_crockford_letters(): void
    {
        $this->assertFalse(PublicId::isValid('h3k7m2pi')); // i
        $this->assertFalse(PublicId::isValid('h3k7m2pl')); // l
        $this->assertFalse(PublicId::isValid('h3k7m2po')); // o
        $this->assertFalse(PublicId::isValid('h3k7m2pu')); // u
    }

    public function test_rejects_wrong_length(): void
    {
        $this->assertFalse(PublicId::isValid('h3k7m2p'));
        $this->assertFalse(PublicId::isValid('h3k7m2p99'));
    }

    public function test_assert_valid_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PublicId::assertValid('not-valid');
    }
}
