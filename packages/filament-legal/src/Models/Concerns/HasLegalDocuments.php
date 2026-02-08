<?php

namespace BeegoodIT\FilamentLegal\Models\Concerns;

use BeegoodIT\FilamentLegal\Models\LegalIdentity;
use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasLegalDocuments
{
    public function legalPolicies(): MorphMany
    {
        return $this->morphMany(LegalPolicy::class, 'owner');
    }

    public function legalIdentity(): MorphOne
    {
        return $this->morphOne(LegalIdentity::class, 'owner');
    }
}
