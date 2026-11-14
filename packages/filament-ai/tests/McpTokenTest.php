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

        $owner = $this->subject('7');

        $issued = McpToken::issue(
            label: 'cursor',
            subject: $owner,
            createdByUserId: $user->id,
            scope: McpToken::SCOPE_READ,
        );

        $second = McpToken::issue(
            label: 'cursor-2',
            subject: $owner,
            createdByUserId: $user->id,
        );

        $this->assertNotSame($issued['token']->id, $second['token']->id);
        $this->assertStringStartsWith('fa_mcp_', $issued['plain']);
        $this->assertSame('7', $issued['token']->subjectKey());
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
            subject: null,
            createdByUserId: $user->id,
            scope: McpToken::SCOPE_WRITE,
        );

        $this->assertTrue($issued['token']->allowsWrite());
        $issued['token']->revoke();

        $this->assertFalse($issued['token']->fresh()->isCurrentlyValid());
        $this->assertNull(McpToken::findByPlainToken('fa_mcp_not-a-real-token'));
    }

    #[Test]
    public function for_site_scopes_via_configured_subject_model(): void
    {
        $user = $this->actingAsUser();
        $owner = $this->subject('7');
        $other = $this->subject('99');

        McpToken::issue(label: 'mine', subject: $owner, createdByUserId: $user->id);
        McpToken::issue(label: 'theirs', subject: $other, createdByUserId: $user->id);
        McpToken::issue(label: 'global', subject: null, createdByUserId: $user->id);

        $scoped = McpToken::query()->forSite('7')->pluck('label')->all();

        $this->assertSame(['mine'], $scoped);
    }

    #[Test]
    public function issue_accepts_site_id_soft_dep_via_subject_model(): void
    {
        $user = $this->actingAsUser();

        $issued = McpToken::issue(
            label: 'soft-dep',
            createdByUserId: $user->id,
            siteId: '42',
        );

        $this->assertSame('42', $issued['token']->subjectKey());
        $this->assertSame((new \BeegoodIT\FilamentAi\Tests\Fixtures\SubjectOwner)->getMorphClass(), $issued['token']->subject_type);
    }
}
