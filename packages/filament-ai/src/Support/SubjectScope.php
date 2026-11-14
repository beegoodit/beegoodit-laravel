<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class SubjectScope
{
    /**
     * @param  Builder<*>  $query
     * @return Builder<*>
     */
    public static function apply(Builder $query, ?Model $subject): Builder
    {
        if ($subject === null) {
            return $query->whereNull('subject_type')->whereNull('subject_id');
        }

        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', (string) $subject->getKey());
    }

    /**
     * @return array{subject_type: string|null, subject_id: string|null}
     */
    public static function attributes(?Model $subject): array
    {
        if ($subject === null) {
            return [
                'subject_type' => null,
                'subject_id' => null,
            ];
        }

        return [
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => (string) $subject->getKey(),
        ];
    }

    /**
     * Soft-dep helper: build a subject stub from `filament-ai.subject_model` + key.
     * Does not hit the database — only morph class + key are needed for scoping.
     */
    public static function fromConfiguredKey(int|string|null $id): ?Model
    {
        if ($id === null || $id === '') {
            return null;
        }

        $model = config('filament-ai.subject_model');

        if (! is_string($model) || $model === '' || ! is_a($model, Model::class, true)) {
            throw new InvalidArgumentException(
                'filament-ai.subject_model must be an Eloquent model class before resolving a subject id.',
            );
        }

        /** @var Model $instance */
        $instance = new $model;
        $instance->setAttribute($instance->getKeyName(), $id);

        return $instance;
    }
}
