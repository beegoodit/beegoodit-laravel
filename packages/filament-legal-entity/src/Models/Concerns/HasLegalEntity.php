<?php

namespace BeegoodIT\FilamentLegalEntity\Models\Concerns;

use BeegoodIT\FilamentLegalEntity\Models\LegalEntity;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasLegalEntity
{
    public function legalEntity(): MorphOne
    {
        return $this->morphOne(LegalEntity::class, 'owner');
    }
}
