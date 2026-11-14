<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
}
