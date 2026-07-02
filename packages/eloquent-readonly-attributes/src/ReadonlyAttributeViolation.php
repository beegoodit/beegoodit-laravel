<?php

namespace BeegoodIT\EloquentReadonlyAttributes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\ValidationException;

class ReadonlyAttributeViolation extends ValidationException
{
    /**
     * @param  list<string>  $attributes
     */
    public function __construct(private readonly array $attributes)
    {
        parent::__construct(
            tap(ValidatorFacade::make([], []), function ($validator) use ($attributes): void {
                foreach ($attributes as $attribute) {
                    foreach (Arr::wrap(self::messageForAttribute($attribute, $attributes)) as $message) {
                        $validator->errors()->add($attribute, $message);
                    }
                }
            }),
        );
    }

    /**
     * @param  list<string>  $attributes
     */
    public static function forAttributes(array $attributes): self
    {
        return new self($attributes);
    }

    /**
     * @return list<string>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, list<string>>
     */
    public function errorsForStatePath(string $statePath): array
    {
        $prefix = rtrim($statePath, '.');

        $messages = [];

        foreach ($this->attributes as $attribute) {
            $messages["{$prefix}.{$attribute}"] = [
                self::messageForAttribute($attribute, $this->attributes),
            ];
        }

        return $messages;
    }

    public function toFormValidationException(string $statePath): ValidationException
    {
        return ValidationException::withMessages($this->errorsForStatePath($statePath));
    }

    /**
     * @param  list<string>  $attributes
     */
    protected static function messageForAttribute(string $attribute, array $attributes): string
    {
        return sprintf(
            'Readonly attributes cannot be changed: %s',
            implode(', ', $attributes),
        );
    }
}
