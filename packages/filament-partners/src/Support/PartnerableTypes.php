<?php

namespace BeegoodIT\FilamentPartners\Support;

use Filament\Forms\Components\MorphToSelect;
use Illuminate\Database\Eloquent\Model;

final class PartnerableTypes
{
    /**
     * @return list<class-string<Model>>
     */
    public static function models(): array
    {
        $configured = config('filament-partners.partnerable_models', []);
        $models = [];

        foreach ($configured as $key => $value) {
            if (is_int($key) && is_string($value) && $value !== '') {
                $models[] = $value;

                continue;
            }

            if (is_string($key) && $key !== '') {
                $models[] = $key;
            }
        }

        return array_values(array_unique($models));
    }

    /**
     * @param  class-string<Model>  $model
     */
    public static function titleAttribute(string $model): string
    {
        $configured = config('filament-partners.partnerable_models', []);

        if (is_string($configured[$model] ?? null) && $configured[$model] !== '') {
            return $configured[$model];
        }

        $overrides = config('filament-partners.partnerable_title_attributes', []);

        if (is_string($overrides[$model] ?? null) && $overrides[$model] !== '') {
            return $overrides[$model];
        }

        return 'name';
    }

    /**
     * @return array<int, MorphToSelect\Type>
     */
    public static function morphTypes(): array
    {
        return array_map(
            fn (string $model): MorphToSelect\Type => self::type($model),
            self::models(),
        );
    }

    /**
     * @param  class-string<Model>  $model
     */
    public static function type(string $model): MorphToSelect\Type
    {
        $attribute = self::titleAttribute($model);
        $type = MorphToSelect\Type::make($model);

        if (method_exists($model, $attribute)) {
            return $type
                ->titleAttribute(self::searchableAttribute($attribute))
                ->getOptionLabelFromRecordUsing(
                    fn (Model $record): string => self::label($record, $attribute),
                );
        }

        return $type->titleAttribute($attribute);
    }

    public static function label(?Model $owner, ?string $attribute = null): string
    {
        if ($owner === null) {
            return __('filament-partners::partner.partnerable_platform');
        }

        $attribute ??= self::titleAttribute($owner::class);

        if (method_exists($owner, $attribute)) {
            $value = $owner->{$attribute}();

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        $value = $owner->getAttribute($attribute);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $name = $owner->getAttribute('name');

        return is_string($name) && $name !== '' ? $name : (string) $owner->getKey();
    }

    private static function searchableAttribute(string $attribute): string
    {
        return $attribute === 'title' ? 'name' : $attribute;
    }
}
