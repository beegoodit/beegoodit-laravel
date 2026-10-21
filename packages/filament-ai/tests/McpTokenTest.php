<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Models\McpToken;
use PHPUnit\Framework\Attributes\Test;

final class McpTokenTest extends TestCase
{
    #[Test]
    public function it_issues_hashed_tokens_and_finds_by_plain(): void
    {
        $user = $this->actingAsUser();

        $issued = McpToken::issue(
            label: 'cursor',
            tenantId: 7,
            createdByUserId: $user->id,
            scope: McpToken::SCOPE_READ,
        );

        $this->assertStringStartsWith('fa_mcp_', $issued['plain']);
        $this->assertSame(7, $issued['token']->tenantKey());
        $this->assertSame($user->id, $issued['token']->created_by_user_id);
        $this->assertTrue($issued['token']->isCurrentlyValid());
        $this->assertFalse($issued['token']->allowsWrite());

        $found = McpToken::findByPlainToken($issued['plain']);
        $this->assertNotNull($found);
        $this->assertTrue($found->is($issued['token']));
    }

    #[Test]
    public function it_revokes_and_rejects_invalid_tokens(): void
    {
        $user = $this->actingAsUser();

        $issued = McpToken::issue(
            label: 'tmp',
            tenantId: null,
            createdByUserId: $user->id,
            scope: McpToken::SCOPE_WRITE,
        );

        $this->assertTrue($issued['token']->allowsWrite());
        $issued['token']->revoke();

        $this->assertFalse($issued['token']->fresh()->isCurrentlyValid());
        $this->assertNull(McpToken::findByPlainToken('fa_mcp_not-a-real-token'));
    }
}
