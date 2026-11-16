<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Filament\Pages\Assistant;
use BeegoodIT\FilamentAi\Filament\Pages\ChatConversation;
use BeegoodIT\FilamentAi\Filament\Pages\Conversations;
use BeegoodIT\FilamentAi\Filament\Pages\Overview;
use BeegoodIT\FilamentAi\Filament\Pages\Settings;
use PHPUnit\Framework\Attributes\Test;

class PageSlugsTest extends TestCase
{
    #[Test]
    public function it_puts_pages_under_the_ai_prefix(): void
    {
        $this->assertSame('ai/overview', Overview::getDefaultSlug());
        $this->assertSame('ai/assistant', Assistant::getDefaultSlug());
        $this->assertSame('ai/conversations', Conversations::getDefaultSlug());
        $this->assertSame('ai/conversations/{conversation}', ChatConversation::getSlug());
        $this->assertSame('ai/settings', Settings::getDefaultSlug());
    }
}
