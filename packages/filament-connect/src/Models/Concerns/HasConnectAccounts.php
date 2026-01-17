<?php

namespace Beegoodit\FilamentConnect\Models\Concerns;

use Beegoodit\FilamentConnect\Models\ApiAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasConnectAccounts
{
    public function apiAccounts(): MorphMany
    {
        return $this->morphMany(ApiAccount::class, 'owner');
    }

    public function getConnectAccount(string $service): ?ApiAccount
    {
        return $this->apiAccounts()
            ->where('service', $service)
            ->where('is_active', true)
            ->first();
    }

    public function getConnectCredentials(string $service): ?array
    {
        return $this->getConnectAccount($service)?->credentials;
    }
}
