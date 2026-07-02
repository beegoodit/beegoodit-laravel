<?php

namespace BeegoodIT\LaravelPwa\Support;

class PwaThemeTokens
{
    public static function cssVariableName(string $key): string
    {
        $key = str_replace('_', '-', $key);

        if (str_starts_with($key, 'pwa-')) {
            return '--'.$key;
        }

        return '--pwa-'.$key;
    }

    /**
     * @return array<string, string>
     */
    public static function forScope(string $scope): array
    {
        /** @var array<string, string|null> $tokens */
        $tokens = config("pwa.navigation.theme_tokens.{$scope}", []);

        return collect($tokens)
            ->filter(fn ($value): bool => filled($value))
            ->mapWithKeys(fn (string $value, string $key): array => [
                self::cssVariableName($key) => $value,
            ])
            ->all();
    }

    public static function hasAny(): bool
    {
        return self::forScope('light') !== [] || self::forScope('dark') !== [];
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public static function renderBlock(string $selector, array $tokens): string
    {
        if ($tokens === []) {
            return '';
        }

        $declarations = collect($tokens)
            ->map(fn (string $value, string $name): string => "    {$name}: {$value};")
            ->implode("\n");

        return "{$selector} {\n{$declarations}\n}";
    }
}
