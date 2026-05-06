<?php

namespace BeegoodIT\EloquentReadonlyAttributes;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait HasReadonlyAttributes
{
    protected static function bootHasReadonlyAttributes(): void
    {
        static::saving(function (Model $model): void {
            if (ReadonlyAttributes::guardsAreBypassed()) {
                return;
            }

            $violations = $model->readonlyAttributeViolations();

            if ($violations !== []) {
                throw new ReadonlyAttributeViolation($violations);
            }
        });
    }

    /**
     * @return array<string, Closure(static): bool>
     */
    protected function readonlyAttributes(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    protected function readonlyAttributeViolations(): array
    {
        $violations = [];
        $readonlyAttributes = $this->readonlyAttributes();

        foreach (array_keys($this->getDirty()) as $attribute) {
            if ($this->isReadonlyHousekeepingAttribute($attribute)) {
                continue;
            }

            if (! array_key_exists($attribute, $readonlyAttributes)) {
                continue;
            }

            if ($readonlyAttributes[$attribute]($this) === true) {
                $violations[] = $attribute;
            }
        }

        return $violations;
    }

    protected function isReadonlyHousekeepingAttribute(string $attribute): bool
    {
        return in_array($attribute, array_filter([
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
            'deleted_at',
        ]), true);
    }
}
