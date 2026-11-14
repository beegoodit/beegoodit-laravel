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
    public function it_shortens_known_model_ids_for_the_closed_trigger(): void
    {
        $this->assertSame('Auto', ChatModels::shortLabel(ChatModels::Auto));
        $this->assertSame('GPT-4o mini', ChatModels::shortLabel('gpt-4o-mini'));
        $this->assertSame('GPT-4.1', ChatModels::shortLabel('openai/gpt-4.1'));
        $this->assertSame('Sonnet 4.5', ChatModels::shortLabel('claude-sonnet-4-5'));
        $this->assertSame('Gemini 2.5 Flash', ChatModels::shortLabel('gemini-2.5-flash'));
        $this->assertSame('Fast', ChatModels::shortLabel('gpt-4o', 'Fast'));
        $this->assertSame('custom-model', ChatModels::shortLabel('custom-model'));
    }

    #[Test]
    public function it_puts_the_full_id_and_the_short_name_in_the_select_label(): void
    {
        $html = ChatModels::optionHtml('claude-sonnet-4-5', 'claude-sonnet-4-5');

        $this->assertStringContainsString('filament-ai-model-option-full', $html);
        $this->assertStringContainsString('claude-sonnet-4-5', $html);
        $this->assertStringContainsString('filament-ai-model-option-short', $html);
        $this->assertStringContainsString('Sonnet 4.5', $html);
        $this->assertStringNotContainsString('<b>', ChatModels::optionHtml('a<b>', 'a<b>'));
        $this->assertStringContainsString('a&lt;b&gt;', ChatModels::optionHtml('a<b>', 'a<b>'));
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
