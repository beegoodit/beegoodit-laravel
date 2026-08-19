<?php

namespace BeegoodIT\EloquentRichCrud;

use RuntimeException;

class MustUseProvision extends RuntimeException
{
    public static function for(object $model): self
    {
        $basename = class_basename($model);

        return new self("Call {$basename}::provision() instead of create() for {$basename}.");
    }
}
