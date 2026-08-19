<?php

namespace BeegoodIT\EloquentRichCrud;

use RuntimeException;

class MustUseDeprovision extends RuntimeException
{
    public static function for(object $model): self
    {
        $basename = class_basename($model);

        return new self("Call {$basename}::deprovision() instead of delete() for {$basename}.");
    }
}
