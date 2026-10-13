<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

enum ChatMode: string
{
    case Ask = 'ask';
    case Agent = 'agent';
    case Plan = 'plan';

    public function allowsWrites(): bool
    {
        return $this === self::Agent;
    }

    public function isReadOnly(): bool
    {
        return ! $this->allowsWrites();
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function tryFromConfig(?string $value = null): self
    {
        $value ??= (string) config('filament-ai.default_mode', self::Ask->value);

        return self::tryFrom($value) ?? self::Ask;
    }
}
