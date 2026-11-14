<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

final class ChatModels
{
    /** Sentinel for “use the provider default model”. */
    public const Auto = 'auto';

    /**
     * @return array<string, string>
     */
    public static function options(?string $current = null): array
    {
        $configured = config('filament-ai.chat.models', []);
        $models = is_array($configured) ? $configured : [];

        $options = [
            self::Auto => __('filament-ai::ai.chat.model_auto'),
        ];

        foreach ($models as $key => $value) {
            if (is_string($key) && is_string($value) && $key !== self::Auto) {
                $options[$key] = $value;

                continue;
            }

            if (is_string($value) && $value !== '' && $value !== self::Auto) {
                $options[$value] = $value;
            }
        }

        if (
            filled($current)
            && $current !== self::Auto
            && ! array_key_exists($current, $options)
        ) {
            $options[$current] = $current;
        }

        return $options;
    }

    public static function normalize(?string $model): string
    {
        $model = trim((string) $model);

        return $model === '' ? self::Auto : $model;
    }

    public static function toStored(?string $model): ?string
    {
        $model = trim((string) $model);

        if ($model === '' || $model === self::Auto) {
            return null;
        }

        return $model;
    }
}
