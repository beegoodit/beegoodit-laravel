<?php

namespace BeegoodIT\EloquentReadonlyAttributes;

use RuntimeException;

class ReadonlyAttributeViolation extends RuntimeException
{
    /**
     * @param  list<string>  $attributes
     */
    public function __construct(private readonly array $attributes)
    {
        parent::__construct(sprintf(
            'Readonly attributes cannot be changed: %s',
            implode(', ', $attributes),
        ));
    }

    /**
     * @return list<string>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
