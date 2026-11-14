<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

use Illuminate\Support\Str;

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

    /**
     * Select labels carry the full id for the open list and a short name for the closed trigger.
     *
     * @return array<string, string>
     */
    public static function selectOptions(?string $current = null): array
    {
        $options = [];

        foreach (self::options($current) as $id => $label) {
            $options[$id] = self::optionHtml((string) $id, (string) $label);
        }

        return $options;
    }

    public static function optionHtml(string $id, string $label): string
    {
        $listLabel = $id === self::Auto ? $label : $id;

        return '<span class="filament-ai-model-option">'
            .'<span class="filament-ai-model-option-full">'.e($listLabel).'</span>'
            .'<span class="filament-ai-model-option-short">'.e(self::shortLabel($id, $label)).'</span>'
            .'</span>';
    }

    public static function shortLabel(string $id, ?string $configuredLabel = null): string
    {
        if ($id === self::Auto) {
            return (string) __('filament-ai::ai.chat.model_auto');
        }

        if (is_string($configuredLabel) && $configuredLabel !== '' && $configuredLabel !== $id) {
            return $configuredLabel;
        }

        $name = Str::contains($id, '/') ? Str::afterLast($id, '/') : $id;

        if (preg_match('/^claude-(haiku|sonnet|opus)-(\d+)-(\d+)/i', $name, $matches) === 1) {
            return ucfirst(strtolower($matches[1])).' '.$matches[2].'.'.$matches[3];
        }

        if (preg_match('/^gemini-(\d+(?:\.\d+)?)-([a-z]+)/i', $name, $matches) === 1) {
            return 'Gemini '.$matches[1].' '.ucfirst(strtolower($matches[2]));
        }

        if (preg_match('/^gpt-(.+)$/i', $name, $matches) === 1) {
            return 'GPT-'.str_replace('-', ' ', $matches[1]);
        }

        return $name;
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
