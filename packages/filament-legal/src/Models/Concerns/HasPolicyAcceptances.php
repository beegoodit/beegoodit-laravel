<?php

namespace BeegoodIT\FilamentLegal\Models\Concerns;

use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use BeegoodIT\FilamentLegal\Models\PolicyAcceptance;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPolicyAcceptances
{
    /**
     * Get the policy acceptances for the user.
     */
    public function policyAcceptances(): HasMany
    {
        return $this->hasMany(PolicyAcceptance::class, 'user_id');
    }

    /**
     * Check if the user has accepted the latest version of a policy type.
     */
    public function hasAcceptedLatestPolicy(string $type = 'privacy'): bool
    {
        $activePolicy = LegalPolicy::getActive($type);

        if (!$activePolicy instanceof \BeegoodIT\FilamentLegal\Models\LegalPolicy) {
            return true;
        }

        return $this->policyAcceptances()
            ->where('legal_policy_id', $activePolicy->id)
            ->exists();
    }
}
