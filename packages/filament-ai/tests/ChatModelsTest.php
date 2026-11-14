<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests;

use BeegoodIT\FilamentAi\Support\ChatModels;
use PHPUnit\Framework\Attributes\Test;

class ChatModelsTest extends TestCase
{
    #[Test]
    public function it_builds_options_with_auto_first(): void
    {
        config([
            'filament-ai.chat.models' => [
                'gpt-4o-mini',
                'claude-sonnet-4-5',
            ],
        ]);

        $this->assertSame([
            ChatModels::Auto => 'Auto',
            'gpt-4o-mini' => 'gpt-4o-mini',
            'claude-sonnet-4-5' => 'claude-sonnet-4-5',
        ], ChatModels::options());
    }

    #[Test]
    public function it_includes_current_model_when_missing_from_config(): void
    {
        config([
            'filament-ai.chat.models' => [
                'gpt-4o-mini',
            ],
        ]);

        $options = ChatModels::options('custom-model');

        $this->assertArrayHasKey('custom-model', $options);
        $this->assertSame('custom-model', $options['custom-model']);
    }

    #[Test]
    public function it_normalizes_blank_to_auto_and_stores_null(): void
    {
        $this->assertSame(ChatModels::Auto, ChatModels::normalize(null));
        $this->assertSame(ChatModels::Auto, ChatModels::normalize(''));
        $this->assertSame('gpt-4o', ChatModels::normalize('gpt-4o'));
        $this->assertNull(ChatModels::toStored(ChatModels::Auto));
        $this->assertNull(ChatModels::toStored(''));
        $this->assertSame('gpt-4o', ChatModels::toStored('gpt-4o'));
    }
}
